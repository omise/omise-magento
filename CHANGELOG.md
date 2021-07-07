# CHANGELOG


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
