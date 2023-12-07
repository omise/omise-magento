# CHANGELOG

## [v3.5.2 _(Dec, 7, 2023)_](https://github.com/omise/omise-magento/releases/tag/v3.5.2)
- Updated installment interest rates. (PR: [#460](https://github.com/omise/omise-magento/pull/460))

## [v3.5.1 _(Nov, 24, 2023)_](https://github.com/omise/omise-magento/releases/tag/v3.5.1)
- Updated OCBC digital logo. (PR: [#457](https://github.com/omise/omise-magento/pull/457))
- Remove zero_interest_installments option from installment banks other than Maybank. (PR: [#458](https://github.com/omise/omise-magento/pull/458))

## [v3.5.0 _(Oct, 24, 2023)_](https://github.com/omise/omise-magento/releases/tag/v3.5.0)
- Added QR expires count down for promptpay. (PR: [#453](https://github.com/omise/omise-magento/pull/453))

## [v3.4.0 _(Oct, 11, 2023)_](https://github.com/omise/omise-magento/releases/tag/v3.4.0)
- Added dynamic webhooks with feature flag. (PR: [#450](https://github.com/omise/omise-magento/pull/450))

## [v3.3.1 _(Oct, 03, 2023)_](https://github.com/omise/omise-magento/releases/tag/v3.3.1)
- Added Promptpay QR payment instructions. (PR: [#447](https://github.com/omise/omise-magento/pull/447))
- Bug fixed on Alipay. (PR: [#446](https://github.com/omise/omise-magento/pull/446))

## [v3.3.0 _(Sep, 21, 2023)_](https://github.com/omise/omise-magento/releases/tag/v3.3.0)
- Added OCBC Digital Payment. (PR: [#440](https://github.com/omise/omise-magento/pull/443))

## [v3.2.0 _(Aug, 22, 2023)_](https://github.com/omise/omise-magento/releases/tag/v3.2.0)
- Enabled AlipayHK, Kakaopay and Touch 'N Go for Thailand PSP. (PR: [#440](https://github.com/omise/omise-magento/pull/440))

## [v3.1.2 _(Jun, 23, 2023)_](https://github.com/omise/omise-magento/releases/tag/v3.1.2)
- Fetch the same number of orders as the value of refresh counter in the cron job. (PR: [#437](https://github.com/omise/omise-magento/pull/437))

## [v3.1.1 _(Jun, 12, 2023)_](https://github.com/omise/omise-magento/releases/tag/v3.1.1)
- Bug fixed: Atome failed to create charge with bundle product. (PR: [#433](https://github.com/omise/omise-magento/pull/433))

## [v3.1.0 _(May 15, 2023)_](https://github.com/omise/omise-magento/releases/tag/v3.1.0)
- Added PayPay payment method. (PR: [#428](https://github.com/omise/omise-magento/pull/428))
- Get installment minimum from capability API. (PR: [#427](https://github.com/omise/omise-magento/pull/427))
- Added Google Pay logo in the checkout page. (PR: [#426](https://github.com/omise/omise-magento/pull/426))
- Added Atome payment method. (PR: [#422](https://github.com/omise/omise-magento/pull/422))
- Refactored functions to reduce cognitive complexity. (PR: [#421](https://github.com/omise/omise-magento/pull/421))

## [v3.0.0 _(Apr 3, 2023)_](https://github.com/omise/omise-magento/releases/tag/v3.0.0)
- Replace credit card form with secure form with a feature flag options to switch between credit card forms. (PR: [#410](https://github.com/omise/omise-magento/pull/410))
- Added an admin message to notify the user to update the current credit card form to embedded form. (PR: [#419](https://github.com/omise/omise-magento/pull/419))
- Track the adoption of the secure form. (PR: [#420](https://github.com/omise/omise-magento/pull/420))
- Removed deprecated ktb and scb internet banking.  (PR: [#417](https://github.com/omise/omise-magento/pull/417))
- Bug fix: Hanlde ShopeePay cancel payment. (PR: [#415](https://github.com/omise/omise-magento/pull/415))

## [v2.32.0 _(Mar 8, 2023)_](https://github.com/omise/omise-magento/releases/tag/v2.32.0)
- Added Krungthai NEXT mobile banking (PR: [#409](https://github.com/omise/omise-magento/pull/409))

## [v2.31.0 _(Jan 24, 2023)_](https://github.com/omise/omise-magento/releases/tag/v2.31.0)
- Added ShopeePay under Thailand and Singpaore. (PR: [#404](https://github.com/omise/omise-magento/pull/404))
- Added installment TTB. (PR: [#405](https://github.com/omise/omise-magento/pull/405))
- Added a logic to reverse the cancelled items before changing order status from cancelled to processing. (PR: [#406](https://github.com/omise/omise-magento/pull/406))
- Rebrand Lotus's Bill Payment. (PR: [#407](https://github.com/omise/omise-magento/pull/407))

## [v2.30.0 _(Dec 14, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.30.0)
- Rebranded Omise to Opn Payments. (PR: [#395](https://github.com/omise/omise-magento/pull/395))
- Set order status from closed to processing on refund so that merchants can create credit memo. (PR: [#398](https://github.com/omise/omise-magento/pull/398))
- Fixed duitnowobw bank list not showing. (PR: [#393](https://github.com/omise/omise-magento/pull/393))
- Call omise refund after offline refund event triggered. (PR: [#392](https://github.com/omise/omise-magento/pull/392))
- Use existing online refund function for all payment methods. (PR: [#397](https://github.com/omise/omise-magento/pull/397))
- Bug fixed on partial refund. (PR: [#399](https://github.com/omise/omise-magento/pull/399))

## [v2.29.2 _(Oct 25, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.29.2)
- Issue of two invoices of the same order has been fixed. (PR: [#387](https://github.com/omise/omise-magento/pull/387))
- Improve the implementation to prevent the customer entering redirect URL directly in the address bar of the browser. (PR: [#388](https://github.com/omise/omise-magento/pull/388))
- Fixed the issue of FPX redirecting to cart page with invalid order status. Also added a delay in redirect process before fetching charge status. (PR: [#389](https://github.com/omise/omise-magento/pull/389))
- Fixed conflict between COD with the plugin. (PR: [#390](https://github.com/omise/omise-magento/pull/390))

## [v2.29.1 _(Oct 03, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.29.1)
- Fixed an issue of syncing order status from Magento after reversing a charge not updating the product’s quantity. (PR: [#385](https://github.com/omise/omise-magento/pull/385))
- Fixed the issue of duplicate cancelled event. (PR [#384](https://github.com/omise/omise-magento/pull/384))
- Fixed blocker and serious issues alerted by SonarCloud. (PR [#383](https://github.com/omise/omise-magento/pull/383))

## [v2.29.0 _(Sept 19, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.29.0)
- Upgraded Omise PHP to version 2.16.0 (PR [#375](https://github.com/omise/omise-magento/pull/375))
- Fix the issue of not redirecting to Thank you page. (PR [#376](https://github.com/omise/omise-magento/pull/376))
- Added pipeline to deploy on staging. (PR [#380](https://github.com/omise/omise-magento/pull/380))
- Added pipeline for code coverage with sonarcloud. (PR [#379](https://github.com/omise/omise-magento/pull/379))

## [v2.28.0 _(Aug 30, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.28.0)
- Order status will be updated via webhook when charge status is updated manually by Operations Team. (PR [#359](https://github.com/omise/omise-magento/pull/359))
- Enhanced manual sync button to manually update order status to reflect refunds. (PR [#362](https://github.com/omise/omise-magento/pull/362))
- Enhance webhook flow to capture refund.create event. (PR [#363](https://github.com/omise/omise-magento/pull/363))
- Fix trim do not accept null value in php 8. (PR [#365](https://github.com/omise/omise-magento/pull/365))
- Added unit tests for OmiseHelper.php and Github workflow to run unit tests. (PR [#364](https://github.com/omise/omise-magento/pull/364))
- Fix the issue of an order getting canceled citing Payment expired when the charge was completed successfully. (PR [#368](https://github.com/omise/omise-magento/pull/368))
- Enhancements display payment options. (PR [#361](https://github.com/omise/omise-magento/pull/361))
- Add support RMS wallet (PR [#366](https://github.com/omise/omise-magento/pull/366))
- Fix the issue of Sync Order Status button not working. (PR [#374](https://github.com/omise/omise-magento/pull/374))

## [v2.27.0 _(Jun 30, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.27.0)
- Added missing translation for Thai language. (PR [#356](https://github.com/omise/omise-magento/pull/356))
- Items restored in the cart if the return URI is not triggered by Omise server. (PR [#357](https://github.com/omise/omise-magento/pull/357))
- Allow GrabPay to have payment in THB. (PR [#358](https://github.com/omise/omise-magento/pull/358))

## [v2.26.0 _(Jun 6, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.26.0)
- Retain the cart items when payment fails (PR [#350](https://github.com/omise/omise-magento/pull/350))
- Added support for Google Pay payment method (PR [#351](https://github.com/omise/omise-magento/pull/351))
- Added bank of China logo for FPX payment method (PR [#352](https://github.com/omise/omise-magento/pull/352))
- Payment instructions will be sent to the customer's email for convenience store payment method (PR [#353](https://github.com/omise/omise-magento/pull/353))

## [v2.25.0 _(May 25, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.25.0)
- Add Mobile banking BBL for TH (PR [#347](https://github.com/omise/omise-magento/pull/347))
- Checks validity of the keys when adding them. (PR [#346](https://github.com/omise/omise-magento/pull/346))

## [v2.24.0 _(May 17, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.24.0)
- Add GrabPay payment method (PR [#344](https://github.com/omise/omise-magento/pull/344))

## [v2.23.3 _(May 12, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.23.3)
- Fetch capabilities. (PR [#337](https://github.com/omise/omise-magento/pull/337))
- Preventing Omise plugin to initialize when public and secret keys are not present. (PR [#339](https://github.com/omise/omise-magento/pull/339))
- Incorrect webhook endpoint being displayed has been fixed. (PR [#340](https://github.com/omise/omise-magento/pull/340))
- Move OCBC Pao as standalone payment. (PR [#341](https://github.com/omise/omise-magento/pull/341))
- Issue of new order email not being sent when order is placed with non omise payment method has been fixed. (PR [#342](https://github.com/omise/omise-magento/pull/342))

## [v2.23.2 _(Apr 20, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.23.2)
- The issue of capturing a 3DS payment from sub-stores has been fixed. (PR [#335](https://github.com/omise/omise-magento/pull/335))
- Fixed the issue with the Sync Order Status button in the orders page of the admin panel. (PR [#336](https://github.com/omise/omise-magento/pull/336))

## [v2.23.1 _(Apr 8, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.23.1)
### 👾 Bug Fixes
- Fix bug where redirects didnt handle redirects correctly (PR [#334](https://github.com/omise/omise-magento/pull/334))

## [v2.23.0 _(Apr 1, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.23.0)

### 🚀 Enhancements
- Add setting to enable webhook (PR [#333](https://github.com/omise/omise-magento/pull/333))

## [v2.22.0 _(Mar 17, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.22.0)

### 🚀 Enhancements
- Add UOB Installment for TH (PR [#330](https://github.com/omise/omise-magento/pull/330))
- Add Rabbit LINE Pay for TH (PR [#330](https://github.com/omise/omise-magento/pull/330))

## [v2.21.0 _(Mar 09, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.21.0)

### 🚀 Enhancements
- Add Mobile banking OCBC PAO for SG (PR [#327](https://github.com/omise/omise-magento/pull/327))
- Add Mobile banking SCB for TH (PR [#327](https://github.com/omise/omise-magento/pull/327))

## [v2.20.4 _(Feb 14, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.20.4)

### 👾 Bug Fixes
- Fix duplicate invoices being sent via email (PR [#320](https://github.com/omise/omise-magento/pull/325))

## [v2.20.3 _(Jan 19, 2022)_](https://github.com/omise/omise-magento/releases/tag/v2.20.3)

### 👾 Bug Fixes
- Fix invoice generated before charge is captured (PR [#320](https://github.com/omise/omise-magento/pull/320))

## [v2.20.2 _(Dec 03, 2021)_](https://github.com/omise/omise-magento/releases/tag/v2.20.2)

### 👾 Bug Fixes
- Fix FPX logo never display on checkout screen (PR [#318](https://github.com/omise/omise-magento/pull/318))
- Add FPX term and condition link on checkout screen (PR [#318](https://github.com/omise/omise-magento/pull/318))

# CHANGELOG

## [v2.20.1 _(Nov 30, 2021)_](https://github.com/omise/omise-magento/releases/tag/v2.20.1)

### 👾 Bug Fixes
- Fix expiry cron worker incorrectly expiring orders sporadically (PR [#316](https://github.com/omise/omise-magento/pull/316))
- Fix internet banking charges not being marked as expired (PR [#315](https://github.com/omise/omise-magento/pull/315))

## [v2.20.0 _(Nov 09, 2021)_](https://github.com/omise/omise-magento/releases/tag/v2.20.0)

### 🚀 Enhancements
- Added option to generate invoice after success (PR [#313](https://github.com/omise/omise-magento/pull/313))

### 👾 Bug Fixes
- Ensure order confirmation emails are sent after payment is made for all payment methods (PR [#313](https://github.com/omise/omise-magento/pull/313))

## [v2.19.0 _(Sep 21, 2021)_](https://github.com/omise/omise-magento/releases/tag/v2.19.0)

### 🚀 Enhancements
- Add Alipay plus wallets for SG (PR [#309](https://github.com/omise/omise-magento/pull/309))

## [v2.18.6 _(Sep 16, 2021)_](https://github.com/omise/omise-magento/releases/tag/v2.18.6)

### 🚀 Enhancements
- Update omise-php version from 2.11.1 to 2.13.0 (PR [#307](https://github.com/omise/omise-magento/pull/307))
- Installments - change minimum order from 3000 to 2000 THB (PR [#307](https://github.com/omise/omise-magento/pull/307))


## [v2.18.5 _(Jul 29, 2021)_](https://github.com/omise/omise-magento/releases/tag/v2.18.5)

### 👾 Bug Fixes
- FPX banks list default to empty (PR [#303](https://github.com/omise/omise-magento/pull/303))

## [v2.18.4 _(Jul 29, 2021)_](https://github.com/omise/omise-magento/releases/tag/v2.18.4)

### 👾 Bug Fixes
- FPX banks list dropdown fix and rename default title  (PR [#300](https://github.com/omise/omise-magento/pull/300))
- Apply hotfixes for invoice generation for FPX payment (PR [#301](https://github.com/omise/omise-magento/pull/301))

## [v2.18.3 _(Jul 15, 2021)_](https://github.com/omise/omise-magento/releases/tag/v2.18.3)

### 👾 Bug Fixes
- Fix FPX payment emails are sent before successful charge (PR [#297](https://github.com/omise/omise-magento/pull/297))


## [v2.18.2 _(Jul 06, 2021)_](https://github.com/omise/omise-magento/releases/tag/v2.18.2)

### 👾 Bug Fixes
- Fix FPX Magento plugin does not indicate the banks with offline status (PR [#295](https://github.com/omise/omise-magento/pull/295))


## [v2.18.1 _(Jun 15, 2021)_](https://github.com/omise/omise-magento/releases/tag/v2.18.1)

### 🚀 Enhancements
- Add support for magento-composer-installer package >= 0.2.1 (PR [#292](https://github.com/omise/omise-magento/pull/292))

### 👾 Bug Fixes
- Add back dependency injection code for charge validation (PR [#291](https://github.com/omise/omise-magento/pull/291))


## [v2.18 _(Jun 10, 2021)_](https://github.com/omise/omise-magento/releases/tag/v2.18)

### 🚀 Enhancements
- Add support for Magento 2.4.2. (PR [#289](https://github.com/omise/omise-magento/pull/289))


## [v2.17 _(Jun 08, 2021)_](https://github.com/omise/omise-magento/releases/tag/v2.17)

### 🚀 Enhancements
- Enhance Magento plugin to support FPX. (PR [#287](https://github.com/omise/omise-magento/pull/287))


## [v2.16 _(Dec 23, 2020)_](https://github.com/omise/omise-magento/releases/tag/v2.16)

### 🚀 Enhancements
- Implementing feature to create refund and applying it on PayNow QR payment. (PR [#284](https://github.com/omise/omise-magento/pull/284))
- Applying standards for Magento and fixing it throughout the code. (PR [#283](https://github.com/omise/omise-magento/pull/283))

## [v2.15 _(Dec 03, 2020)_](https://github.com/omise/omise-magento/releases/tag/v2.15)

### 🚀 Enhancements
- Removing 3DS configuration from backend. (PR [#279](https://github.com/omise/omise-magento/pull/279))
- Implementing cron job to update order status of expired omise charge. (PR [#281](https://github.com/omise/omise-magento/pull/281))


## [v2.14 _(Nov 10, 2020)_](https://github.com/omise/omise-magento/releases/tag/v2.14)

### 🚀 Enhancements
- Adding support for SCB Installments. (PR [#273](https://github.com/omise/omise-magento/pull/273))
- Adding configuration to restrict payment method from specific countries. (PR [#274](https://github.com/omise/omise-magento/pull/274))
- Implementing manual sync for omise payment methods. (PR [#275](https://github.com/omise/omise-magento/pull/275))
- Adding billing address information to card payment charge token. (PR [#277](https://github.com/omise/omise-magento/pull/277))


## [v2.13 _(Sep 15, 2020)_](https://github.com/omise/omise-magento/releases/tag/v2.13)

### 👾 Bug Fixes
- Fixing issue for header and footer information in email containing payment QR, Barcodes for multi-store setup. (PR [#269](https://github.com/omise/omise-magento/pull/269))
- Card payment throws error while checkout for PHP 7.4 version. (PR [#268](https://github.com/omise/omise-magento/pull/268))


## [v2.12 _(Aug 25, 2020)_](https://github.com/omise/omise-magento/releases/tag/v2.12)

### ✨ Highlights
- Implemented new payment method - PromptPay, hiding payment methods which are not supported by currency, hardcode charge expiry date. (PR [#264](https://github.com/omise/omise-magento/pull/264))

### 🚀 Enhancements
- Updated content on PayNow order success page and adding support to send Paynow QR code via email. (PR [#260](https://github.com/omise/omise-magento/pull/260))
- Hiding input card details form on selecting payment with saved card. (PR [#265](https://github.com/omise/omise-magento/pull/265))


## [v2.11 _(May 11, 2020)_](https://github.com/omise/omise-magento/releases/tag/v2.11)

### 👾 Bug Fixes

- Fixes wrong calculation of installment amount per month (PR [#250](https://github.com/omise/omise-magento/pull/250))
- Fixes webhook to support HTTP POST requests (PR [#254](https://github.com/omise/omise-magento/pull/254))
- Fixes broken order success page after checkout (PR [#255](https://github.com/omise/omise-magento/pull/255))


## [v2.10 _(Mar 20, 2020)_](https://github.com/omise/omise-magento/releases/tag/v2.10)

### ✨ Highlights

- Convenience Store Payment Support (available only for Singapre customers) (PR [#224](https://github.com/omise/omise-magento/pull/224))
- Plugin Japaniese translation (PR [#226](https://github.com/omise/omise-magento/pull/226))
- Citi Points Payment Implementation (PR [#231](https://github.com/omise/omise-magento/pull/231))
- PayNow QR Payment Implementation (PR [#240](https://github.com/omise/omise-magento/pull/240))

### 🚀 Enhancements

- Refactors JS Payment method renderers (PR [#225](https://github.com/omise/omise-magento/pull/225))


## [v2.9 _(Sep 06, 2019)_](https://github.com/omise/omise-magento/releases/tag/v2.9)

### ✨ Highlights

- Adding Support for new currencies (PR [#218](https://github.com/omise/omise-magento/pull/218))

### 🚀 Enhancements

- Translation of error messages to Thai Language (PR [#210](https://github.com/omise/omise-magento/pull/210))
⠀⠀⠀⠀⠀⠀⠀⠀⠀
### 👾 Bug Fixes

- Handle exception when getting capabilities with invalid Omise Keys (PR [#216](https://github.com/omise/omise-magento/pull/216))
- Fix error when change Omise Account (PR [#215](https://github.com/omise/omise-magento/pull/215))
- Fix: Too many parameters are sent to parent class in DeleteAction (PR [#213](https://github.com/omise/omise-magento/pull/213))

## [v2.8 _(Jul 17, 2019)_](https://github.com/omise/omise-magento/releases/tag/v2.8)

### ✨ Highlights

- Introducing support for True Money Payments (PR [#201](https://github.com/omise/omise-magento/pull/201))
- Manual Capture Functionality - you can capture Credit Card payments directly in Admin Panel (PR [#182](https://github.com/omise/omise-magento/pull/182))
- Installments - change minimum order from 5000 to 3000 THB (PR [#188](https://github.com/omise/omise-magento/pull/188))
- Delete Saved Credit Card Information (PR [#186](https://github.com/omise/omise-magento/pull/186))

### 🚀 Enhancements

- Edit phone number on checkout-page when making True Money payment (PR [#207](https://github.com/omise/omise-magento/pull/207))
- Change to display an error message using ErrorMessageMapper (PR [#205](https://github.com/omise/omise-magento/pull/205))
- Better format of installment minimum amount message (PR [#203](https://github.com/omise/omise-magento/pull/203))
- Display info on checkout if a plugin is in the Sandbox mode (PR [#198](https://github.com/omise/omise-magento/pull/198))
- Manage cards link on the checkout page (PR [#197](https://github.com/omise/omise-magento/pull/197))
- Display Installment terms (PR [#196](https://github.com/omise/omise-magento/pull/196))
- Refactoring - removed unnecessary redirections (PR [#191](https://github.com/omise/omise-magento/pull/191))
- Enable 'Order' button only if installment terms are chosen (PR [#183](https://github.com/omise/omise-magento/pull/183))
⠀⠀⠀⠀⠀⠀⠀⠀⠀
### 👾 Bug Fixes

- Fix for unable to select some installment terms (PR [#195](https://github.com/omise/omise-magento/pull/195))

## [v2.7 _(Jan 21, 2019)_](https://github.com/omise/omise-magento/releases/tag/v2.7)

### 🚀 Enhancements

- True Money Payment Method Implementation (PR [#211](https://github.com/omise/omise-magento/pull/211))
- Changed in composer omise-php requirement to version 2.11.1 (PR [#177](https://github.com/omise/omise-magento/pull/177))

### 👾 Bug Fixes
- Fix: Display correctly barcode from tesco lotus on checkout success page (PR [#180](https://github.com/omise/omise-magento/pull/180))
- Fix: Order can't be completed when choosing other payment methods than Omise (PR [#179](https://github.com/omise/omise-magento/pull/179))

## [v2.6 _(Jan 15, 2019)_](https://github.com/omise/omise-magento/releases/tag/v2.6)

### 👾 Bug Fixes
- Model\Ui\CapabilitiesConfigProvider: returns an empty array instaed of null when Installment payment method is disabled. (PR [#174](https://github.com/omise/omise-magento/pull/174))

## [v2.5 _(Jan 14, 2019)_](https://github.com/omise/omise-magento/releases/tag/v2.5)

### ✨ Highlights
- Introducing support Installment Payments (PR [#148](https://github.com/omise/omise-magento/pull/148))

### 🚀 Enhancements
- Enable Place Order button on Internet Banking checkout option only if bank is selected. (PR [#166](https://github.com/omise/omise-magento/pull/166))
- Restrict Tesco Bill Payment to THB transactions only (PR [#165](https://github.com/omise/omise-magento/pull/165))
- Tesco Bill Payment added PRINT button (PR [#164](https://github.com/omise/omise-magento/pull/164))
- Restrict internet banking payment method to THB orders only(PR [#161](https://github.com/omise/omise-magento/pull/161))
- Send Tesco barcode to customer's email. (PR [#158](https://github.com/omise/omise-magento/pull/158))
- Make plugin compatible with PHP 7.2 (PR [#168](https://github.com/omise/omise-magento/pull/168))

### 👾 Bug Fixes
- Fix for showing Tesco Bill Payment related information on checkout success page when using other payment methods (PR [#163](https://github.com/omise/omise-magento/pull/163))
- Fix for missing "Terms and Conditions" in Alipay and Tesco payment method. (PR [#162](https://github.com/omise/omise-magento/pull/162))
- Fixed problem that translation to 'Select a card you want to proceed (PR [#160](https://github.com/omise/omise-magento/pull/160))
- Fix compilation error in Capabilities API (PR [#171](https://github.com/omise/omise-magento/pull/171))

## [v2.4 _(Oct 2, 2018)_](https://github.com/omise/omise-magento/releases/tag/2.4)

### ✨ Highlights
- Save Credit Card (PR [#123](https://github.com/omise/omise-magento/pull/123))
- Introducing support of Tesco Lotus Payment (PR[#140](https://github.com/omise/omise-magento/pull/140))

### 🚀 Enhancements
- Force plugin to use newest Omise Api v2017-11-02 (PR [#133](https://github.com/omise/omise-magento/pull/133))
- Code Refactoring, prepare plugin for future payment methods (PR [#130](https://github.com/omise/omise-magento/pull/130), PR [#131](https://github.com/omise/omise-magento/pull/131), PR [#134](https://github.com/omise/omise-magento/pull/134), PR [#137](https://github.com/omise/omise-magento/pull/137), PR [#138](https://github.com/omise/omise-magento/pull/138), PR [#149](https://github.com/omise/omise-magento/pull/149), PR [#153](https://github.com/omise/omise-magento/pull/153))
- Move plugin information in admin panel to 'recommended' section (PR [#129](https://github.com/omise/omise-magento/pull/129))
- Restrict Alipay for THB transactions only (PR [#127](https://github.com/omise/omise-magento/pull/127))
- Changed `composer.json` to fulfil requirements from Magento Market Store (PR [#132](https://github.com/omise/omise-magento/pull/132))

### 👾 Bug Fixes
- Fix for not working webhooks due to script compilation error (PR [#141](https://github.com/omise/omise-magento/pull/141)).
- Fix typo on a classname and filename (PR [#135](https://github.com/omise/omise-magento/pull/135))
- Fix for wrong spelling in Admin Panel (PR [#136](https://github.com/omise/omise-magento/pull/136))

## [v2.3 _(Jun 20, 2018)_](https://github.com/omise/omise-magento/releases/tag/v2.3)

### ✨ Highlights

- Introducing support of Alipay payments (PR [#113](https://github.com/omise/omise-magento/pull/113))
- Support for Multistore Magento 2 configuration (PR [#117](https://github.com/omise/omise-magento/pull/117))
- Simplify plugin installation process by using composer (PR [#112](https://github.com/omise/omise-magento/pull/112))
- Introduce Webhook feature  (PR [#107](https://github.com/omise/omise-magento/pull/107))

### 🚀 Enhancements

- Removed IDR currency support (PR [#122](https://github.com/omise/omise-magento/pull/122))
- Updates config to apply Magento docs recommendations (PR [#120](https://github.com/omise/omise-magento/pull/120))
- Removes link/dependency with Authorize.net module (PR [#118](https://github.com/omise/omise-magento/pull/118))
- Removes specific fee warnings for Internet Banking + improve i18n (PR [#114](https://github.com/omise/omise-magento/pull/114))
- Introduce API Object model structure and refactoring code (PR [#103](https://github.com/omise/omise-magento/pull/103))

### 👾 Bug Fixes
- Internet Banking: Proper set the order status if the returned charge status is set to 'pending' (PR [#105](https://github.com/omise/omise-magento/pull/105))
- Removed unused files (PR [#106](https://github.com/omise/omise-magento/pull/106))

## [v2.2 _(Nov 02, 2017)_](https://github.com/omise/omise-magento/releases/tag/v2.2)

### 🚀 Enhancements

-  Force plugin to use a specific API version (2015-11-17) when make a request to Omise API (PR [#94](https://github.com/omise/omise-magento/pull/94))

---

## [v2.1 _(April 12, 2017)_](https://github.com/omise/omise-magento/releases/tag/v2.1)

### ✨ Highlights

- Introduce 3-D Secure feature (PR [#75](https://github.com/omise/omise-magento/pull/75), [#76](https://github.com/omise/omise-magento/pull/76), [#77](https://github.com/omise/omise-magento/pull/77), [#78](https://github.com/omise/omise-magento/pull/78), [#80](https://github.com/omise/omise-magento/pull/80), [#82](https://github.com/omise/omise-magento/pull/82))
- Introduce Internet Banking feature (PR [#62](https://github.com/omise/omise-magento/pull/62), [#63](https://github.com/omise/omise-magento/pull/63), [#64](https://github.com/omise/omise-magento/pull/64), [#65](https://github.com/omise/omise-magento/pull/65), [#66](https://github.com/omise/omise-magento/pull/66), [#70](https://github.com/omise/omise-magento/pull/70), [#71](https://github.com/omise/omise-magento/pull/71), [#80](https://github.com/omise/omise-magento/pull/80))
- Support IDR & SGD Currencies (PR [#72](https://github.com/omise/omise-magento/pull/72))

### 🚀 Enhancements

- Refactor code structure to support multiple payment methods (PR [#61](https://github.com/omise/omise-magento/pull/61), [#73](https://github.com/omise/omise-magento/pull/73), [#74](https://github.com/omise/omise-magento/pull/74), [#83](https://github.com/omise/omise-magento/pull/83))

---

## [2.0] 2016-12-26
#### FIRST RELEASE FOR MAGENTO COMMUNITY EDITION 2.1.2 & 2.1.3
The first release contains a feature to create charge with auto capture and manual capture.
The merchant can select a setting, sandbox, to switch between test mode and live mode.

The list of charge can be found at Omise dashboard.

## [1.9.0.6] 2016-08-10
- *`Improved`* Remove jQuery library and update code according to new JS checkout form function

## [1.9.0.5] 2016-01-05
- *`Fixed`* Check authorized and captured status before continue the charge action.
- *`Fixed`* Disable submit button action when user clicks submit.
- *`Updated`* Extends `Mage_Payment_Model_Method_Abstract` class instead of `Mage_Payment_Model_Method_Cc` in the payment method class.

## [1.9.0.4] 2015-11-16
- *`Added`* Added `OmiseMagento/[OmiseMagentoVersion]`, `Magento/[MagentoVersion]` into `OMISE_USER_AGENT_SUFFIX`.
- *`Updated`* Updated **omise-php** library from 2.3.1 to 2.4.0.
- *`Fixed`* Supported php version 5.2 - 5.3.
- *`Fixed`* Added autocomplete attribute into card number and security code fields of the checkout form.

## [1.9.0.3] 2015-08-09
- *`Updated`* Updated **omise-php** library from 2.2.0 to 2.3.1.
- *`Fixed`* Fix 'CamelCase' class name issue (CamelCaseName of class was not load in some host environment).

## [1.9.0.2] 2015-07-07
#### Versioning & Document
- *`Updated`* Updated README.md file
- *`Updated`* Changed version number in module's xml files from `0.0.0.1` to `1.9.0.2` to match Magento releases

#### Dashboard Page
- *`Improved`* jQuery from http to https

## [1.0.1] 2015-06-23
#### Dashboard Page
- *`Added`* Added link to Omise Dashboard [https://dashboard.omise.co](https://dashboard.omise.co) into Magento Omise Dashboard Page.
- *`Fixed`* Omise Email Account was not shown in dashboard page.

##### Dashboard Page: Transfer section
- *`Fixed`* Transfer box was not shown when that account doesn't have any transfer history.

## [1.0.0] 2015-06-15
- *`Added`* Implemented **Omise Dashboard** into Magento's admin page. The features are as follows:
  - Show current account status (live or test) depends on that you configured in *Omise Keys Setting page*.
  - Show total account balance, transferable balance.
  - Show history of transfers.
  - Admin was able to transfer their *Omise Balance* to their *Bank account*.
- *`Added`* Implemented **Omise Keys Setting page** into Magento's admin page.
- *`Added`* Added **Omise menu** into top bar menu of Magento's admin page.
- *`Added`* Added **Omise Payment Gateway Module Configuration** into Margento's admin page in payment method section.
- *`Added`* Implemented **Omise Charge API** with `Authorize` with/without `Capture` options.
- *`Added`* Added **Omise Checkout Form** into Magento's checkout page.
- *`Added`* Added [omise-php](https://github.com/omise/omise-php) library *(v2.2.0)* into this extension.
- *`Updated`* Updated **README.md**.

## [0.0.1] 2015-06-15
- Initial version.
