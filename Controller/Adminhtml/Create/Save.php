<?php

namespace PinBlooms\CustomerReviewsUpload\Controller\Adminhtml\Create;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\File\Csv;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\Redirect;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Rating\Option\VoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\CustomerFactory;

/**
 * Class Save
 *
 * Controller responsible for saving reviews through CSV upload.
 */
class Save extends Action
{
    /**
     * @var Csv
     */
    protected $csvProcessor;

    /**
     * @var ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var RatingFactory
     */
    protected $ratingFactory;

    /**
     * @var VoteFactory
     */
    protected $voteFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param Csv $csvProcessor
     * @param ReviewFactory $reviewFactory
     * @param RatingFactory $ratingFactory
     * @param VoteFactory $voteFactory
     * @param StoreManagerInterface $storeManager
     * @param ProductFactory $productFactory
     * @param CustomerFactory $customerFactory
     * @param UploaderFactory $uploaderFactory
     */
    public function __construct(
        Context $context,
        Csv $csvProcessor,
        ReviewFactory $reviewFactory,
        RatingFactory $ratingFactory,
        VoteFactory $voteFactory,
        StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        CustomerFactory $customerFactory,
        UploaderFactory $uploaderFactory
    ) {
        parent::__construct($context);
        $this->csvProcessor = $csvProcessor;
        $this->reviewFactory = $reviewFactory;
        $this->ratingFactory = $ratingFactory;
        $this->voteFactory = $voteFactory;
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->customerFactory = $customerFactory;
        $this->uploaderFactory = $uploaderFactory;
    }

    /**
     * Execute the review import process.
     *
     * @return Redirect
     */
    public function execute()
    {
        try {
            $uploadedFile = $this->getRequest()->getFiles('review_csv');
            if (!$uploadedFile || empty($uploadedFile['tmp_name'])) {
                throw new LocalizedException(__('No file uploaded.'));
            }

            $uploader = $this->uploaderFactory->create(['fileId' => 'review_csv']);
            $uploader->setAllowedExtensions(['csv']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            $destinationPath = BP . '/var/tmp/';
            $result = $uploader->save($destinationPath);
            $filePath = $result['file'];

            $csvData = $this->csvProcessor->getData($destinationPath . $filePath);

            foreach ($csvData as $index => $row) {
                if ($index == 0) {
                    continue;
                }

                $productSku = $row[0];
                $customerEmail = $row[1];
                $reviewDetail = $row[2];
                $reviewSummary = $row[3];
                $summaryRating = (int)$row[4];

                $product = $this->productFactory->create()->loadByAttribute('sku', $productSku);
                if (!$product || !$product->getId()) {
                    $this->messageManager->addWarningMessage(__('Product with SKU "%1" not found, skipping row.', $productSku));
                    continue;
                }

                $customer = $this->customerFactory->create()->getCollection()
                    ->addFieldToFilter('email', $customerEmail)
                    ->getFirstItem();
                if (!$customer || !$customer->getId()) {
                    $this->messageManager->addWarningMessage(__('Customer with email "%1" not found, skipping row.', $customerEmail));
                    continue;
                }

                $existingReview = $this->reviewFactory->create()->getCollection()
                    ->addFieldToFilter('entity_pk_value', $product->getId())
                    ->addFieldToFilter('customer_id', $customer->getId())
                    ->getFirstItem();

                if ($existingReview && $existingReview->getId()) {
                    $review = $existingReview;
                    $review->setTitle($reviewSummary)
                        ->setDetail($reviewDetail)
                        ->setStatusId(1)
                        ->save();

                    $this->voteFactory->create()->getCollection()
                        ->addFieldToFilter('review_id', $review->getId())
                        ->walk('delete');
                } else {
                    $review = $this->reviewFactory->create();
                    $review->setEntityId(1)
                        ->setEntityPkValue($product->getId())
                        ->setStatusId(1)
                        ->setTitle($reviewSummary)
                        ->setDetail($reviewDetail)
                        ->setNickname($customer->getFirstname() . ' ' . $customer->getLastname())
                        ->setCustomerId($customer->getId())
                        ->setStoreId($this->storeManager->getStore()->getId())
                        ->setStores([$this->storeManager->getStore()->getId()])
                        ->save();
                }

                $storeId = $this->storeManager->getStore()->getId();
                $ratingCollection = $this->ratingFactory->create()->getCollection()
                    ->addEntityFilter('product')
                    ->addFieldToFilter('is_active', 1)
                    ->setStoreFilter($storeId);

                $rating = $ratingCollection->getFirstItem();
                if (!$rating || !$rating->getId()) {
                    throw new LocalizedException(__('No active rating found for products.'));
                }

                $ratingOptionCollection = $rating->getOptions();
                $optionId = null;
                foreach ($ratingOptionCollection as $option) {
                    if ((int)$option->getValue() === $summaryRating) {
                        $optionId = $option->getId();
                        break;
                    }
                }

                if (!$optionId) {
                    throw new LocalizedException(__('No matching rating option found for rating "%1".', $summaryRating));
                }

                $ratingOptionVote = $this->voteFactory->create();
                $ratingOptionVote->setRatingId($rating->getId())
                    ->setReviewId($review->getId())
                    ->setPercent($summaryRating * 20)
                    ->setEntityPkValue($product->getId())
                    ->setOptionId($optionId)
                    ->save();

                $review->aggregate();
            }

            $this->messageManager->addSuccessMessage(__('Reviews imported successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error while importing reviews: ' . $e->getMessage()));
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('createmenubackend/create/index');
    }
}
