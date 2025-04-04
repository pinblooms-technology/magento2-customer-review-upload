# Magento 2 - PinBlooms Customer Reviews Upload

## Overview
PinBlooms_CustomerReviewsUpload is a Magento 2 custom module that allows admins to bulk upload product reviews from the Magento admin panel. The module supports importing reviews using a CSV file, using product SKU and customer email for mapping. Additionally, admins can manually add new reviews through a custom grid in the admin panel.

## Features
- Bulk upload reviews via CSV file
- Manual review entry in the admin grid
- Uses product SKU and customer email for mapping
- Provides a sample CSV file for reference
- Supports ratings from 1 to 5

## Installation
### 1. Download and Extract
Clone or download the module into your Magento 2 installation:
```sh
cd <magento_root>/app/code/PinBlooms/CustomerReviewsUpload
```

### 2. Enable the Module
Run the following commands to enable and set up the module:
```sh
php bin/magento module:enable PinBlooms_CustomerReviewsUpload
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

After a successful installation, you can find this module under:

**Magento Admin > Marketing > User Content > Upload Customer Reviews by CSV**

## How to Use
### 1. Import Reviews via CSV
- Navigate to **Magento Admin > Customer Reviews Upload**

![image (27)](https://github.com/user-attachments/assets/a74c525a-cb1c-4e28-9491-e31998d07618)

  
- Download the sample CSV from `PinBlooms/CustomerReviewsUpload/review-sample.csv`
- Fill in the required fields:
  - **SKU**: Product SKU
  - **Customer Email**: Registered customer email
  - **Title**: Review title
  - **Detail**: Review content
  - **Rating**: Values between 1 and 5 (see note below)
- Upload the completed CSV file
![image (25)](https://github.com/user-attachments/assets/f074e381-e232-40a7-8df6-8aebc072f2a9)

  
- Click **Import Reviews**

  ![image (24)](https://github.com/user-attachments/assets/66212a38-0c63-47a9-9212-cfbf2dd35991)


### 2. Add Reviews Manually
- Navigate to **Magento Admin > Customer Reviews Upload**
- Click **Add New Review**
- Fill in the required details
- Save the review

## Rating System
- The rating column should contain values between 1 and 5, where:
  - `1 = *`
  - `2 = **`
  - `3 = ***`
  - `4 = ****`
  - `5 = *****`

## Important Notes
- Ensure that ratings are activated in **Magento Admin > Store > Rating** before importing reviews.

  ![image (26)](https://github.com/user-attachments/assets/0be12b45-2bda-447c-a81d-2043b1146ad6)

- Customer emails used in the CSV must belong to registered customers.

## Support
For issues or feature requests, please create a GitHub issue or contact us at https://pinblooms.com/contact-us/.

## License
This module is licensed under the MIT License. See `LICENSE` for details.

