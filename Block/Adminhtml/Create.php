<?php

namespace PinBlooms\CustomerReviewsUpload\Block\Adminhtml\Create;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\FormKey;

/**
 * Class Upload
 *
 * This class handles the upload functionality for the Customer Reviews module.
 */
class Upload extends Template
{
    /**
     * @var FormKey
     */
    protected $_formKey;

    /**
     * Upload constructor.
     *
     * @param Template\Context $context
     * @param FormKey $formKey
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        FormKey $formKey,
        array $data = []
    ) {
        $this->_formKey = $formKey;
        parent::__construct($context, $data);
    }

    /**
     * Get the form key for the upload form.
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->_formKey->getFormKey();
    }
}
