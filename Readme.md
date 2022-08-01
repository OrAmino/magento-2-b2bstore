# How to install:
1. Create a new folder in `app/code/` called `Orienteed`
2. Place all the folders in this repository inside that new folder.
3. Enable the modules running:
  - `php bin/magento module:enable Orienteed_All Orienteed_CategoryAttribute Orienteed_CustomerAgent Orienteed_CustomerAttribute Orienteed_CustomerDiscountGraphql Orienteed_CustomerLogin Orienteed_GraphQl Orienteed_MoodleId Orienteed_MoodleToken Orienteed_OrderAttribute Orienteed_SendEmailsFromRegistrationFormsOrienteed_RequiredLogin Orienteed_OrderIncidences`
  
  - `php bin/magento setup:upgrade`
  - `php bin/magento setup:di:compile`