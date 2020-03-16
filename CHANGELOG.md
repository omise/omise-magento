# CHANGELOG
### [v1.21 _(Mar 16, 2020)_](https://github.com/omise/omise-magento/releases/tag/v1.21)

#### 🚀 Enhancements

- Implementing new payment method - Paynow QR payment (Available in Singapore only) (PR [#243](https://github.com/omise/omise-magento/pull/243))

---
# CHANGELOG
### [v1.20 _(Mar 5, 2020)_](https://github.com/omise/omise-magento/releases/tag/v1.20)

#### 🚀 Enhancements

- Adding True money payment method (PR [#236](https://github.com/omise/omise-magento/pull/236))
- Implementing new payment method - Tesco Bill Payment (PR [#239](https://github.com/omise/omise-magento/pull/239))

---
### [v1.19 _(Jan 7, 2020)_](https://github.com/omise/omise-magento/releases/tag/v1.19)

#### 🚀 Enhancements

- Adding citipoints method for payment (PR [#233](https://github.com/omise/omise-magento/pull/233))
- Refactoring duplicated code in Model and Callback controller classes (PR [#234](https://github.com/omise/omise-magento/pull/234))

---

### [v1.18 _(Sep 9, 2019)_](https://github.com/omise/omise-magento/releases/tag/v1.18)

#### 🚀 Enhancements

- Adds support for more currencies (PR [#220](https://github.com/omise/omise-magento/pull/220))

---

### [v1.17 _(May 10, 2019)_](https://github.com/omise/omise-magento/releases/tag/v1.17)

#### 🚀 Enhancements

- Update Omise-PHP lib v2.11.2 (according to the change of Installment minimum amount) (PR [#192](https://github.com/omise/omise-magento/pull/192))
- Show instalment amounts, interest rates, and enforce monthly and overall minimum payments (PR [#190](https://github.com/omise/omise-magento/pull/190))
- Deal with unknown instalment providers (merge after #169) (PR [#184](https://github.com/omise/omise-magento/pull/184))
- Instalment payments for Magento 1 (PR [#169](https://github.com/omise/omise-magento/pull/169))

---

### [v1.16 _(Apr 5, 2019)_](https://github.com/omise/omise-magento/releases/tag/v1.16)

#### 🚀 Enhancements

- Adds a more detailed info block on checkout screen for 'Internet Banking' payments. The selected bank is now shown as well, instead of just saying 'Internet Banking'


#### 👾 Bug Fixes

- Fixes an issue with orders not being updated after order cancellation in some situations
- Fixes an issue where order emails were sometimes not being sent

### [v1.15 _(Sep 11, 2018)_](https://github.com/omise/omise-magento/releases/tag/v1.15)

#### 🚀 Enhancements

- Upgrade Omise-PHP library to v2.9.1. (PR [#147](https://github.com/omise/omise-magento/pull/147))
- Improved handling of 'in progress' payments, giving the user a message to say what is happening (PR [#142](https://github.com/omise/omise-magento/pull/142))
- Updated internet banking fee message to account for changing bank fees (PR [#144](https://github.com/omise/omise-magento/pull/144))
- Improved English grammar across the plugin (PR [#143](https://github.com/omise/omise-magento/pull/143))
- Applied consistent naming conventions in code for `protected` and `private` class members (PR [#151](https://github.com/omise/omise-magento/pull/151))

#### 👾 Bug Fixes

- Fixed a CSS issue that was causing internet banking bank logos to not appear with some themes (PR [#144](https://github.com/omise/omise-magento/pull/144))
- Fixed an edge case issue where payments were sometimes recorded against an invoice multiple times (PR [#150](https://github.com/omise/omise-magento/pull/150))

---

### [v1.14 _(Mar 5, 2018)_](https://github.com/omise/omise-magento/releases/tag/v1.14)

#### ✨ Highlights

- Add credit card refund capability (PR [#109](https://github.com/omise/omise-magento/pull/109))
- Add multi-currency support (PR [#102](https://github.com/omise/omise-magento/pull/102))

#### 🚀 Enhancements

- Handle "sales_model_service_quote_submit_failure" event (PR [#108](https://github.com/omise/omise-magento/pull/108))

---

### [v1.13 _(Nov 17, 2017)_](https://github.com/omise/omise-magento/releases/tag/v1.13)

#### ✨ Highlights

- Support Alipay payment (PR [#98](https://github.com/omise/omise-magento/pull/98))
- Correct order state/status and payment flow behavior (PR [#97](https://github.com/omise/omise-magento/pull/97))
- Introduce WebHooks feature (PR [#93](https://github.com/omise/omise-magento/pull/93))

#### 🚀 Enhancements

- Proper set Magento payment object's transaction id after create a new charge / capture an authorized charge (PR [#96](https://github.com/omise/omise-magento/pull/96))
- Refactoring payment processor classes (PR [#92](https://github.com/omise/omise-magento/pull/92), [#100](https://github.com/omise/omise-magento/pull/100))
- Clean code (to remove unused close, add PHPDocBlock, etc) (PR [#91](https://github.com/omise/omise-magento/pull/91))
- Use 'order increment id' as a reference in charge description instead of 'id' (PR [#90](https://github.com/omise/omise-magento/pull/90))

---

### [v1.12 _(May 24, 2017)_](https://github.com/omise/omise-magento/releases/tag/v1.12)

#### ✨ Highlights

- Provides a solution for One Step Checkout extensions. (PR [#86](https://github.com/omise/omise-magento/pull/86))

#### 🚀 Enhancements

- Refactor and clean up code in Block/Form/Cc.php class and payment/form/omisecc.phtml form (PR [#85](https://github.com/omise/omise-magento/pull/85))

---

### [v1.11 _(April 3, 2017)_](https://github.com/omise/omise-magento/releases/tag/v1.11)

#### 🚀 Enhancements

- Upgrade Omise-PHP library to v2.7.1. (PR [#68](https://github.com/omise/omise-magento/pull/68))

#### 👾 Bug Fixes

- Rename files and classes to avoid case-sensitive issue from Magento. (PR [#67](https://github.com/omise/omise-magento/pull/67))

---

### [v1.10 _(March 9, 2017)_](https://github.com/omise/omise-magento/releases/tag/v1.10)

#### ✨ Highlights

- Add option to allows merchant config a card type support by themselves. (PR [#59](https://github.com/omise/omise-magento/pull/59))

---

### [v1.8 _(March 6, 2017)_](https://github.com/omise/omise-magento/releases/tag/v1.8)

#### ✨ Highlights

- Introduce 3-D Secure feature! (PR [#41](https://github.com/omise/omise-magento/pull/41), [#43](https://github.com/omise/omise-magento/pull/43), [#47](https://github.com/omise/omise-magento/pull/47), [#53](https://github.com/omise/omise-magento/pull/53))
- Introduce Internet Banking feature! (PR [#48](https://github.com/omise/omise-magento/pull/48), [#49](https://github.com/omise/omise-magento/pull/49), [#51](https://github.com/omise/omise-magento/pull/51), [#56](https://github.com/omise/omise-magento/pull/56), [#57](https://github.com/omise/omise-magento/pull/57))
- Support JPY, IDR and SGD currencies. (PR [#44](https://github.com/omise/omise-magento/pull/44))

#### 🚀 Enhancements

- Refactor the PaymentMethod class for the extensibility, polymorphism and reduce code complexity that usually, will keep maintain in only one file (Omise_Gateway_Model_PaymentMethod). (PR [#46](https://github.com/omise/omise-magento/pull/46), [#52](https://github.com/omise/omise-magento/pull/52))
- Import `squizlabs/php_codesniffer` package to the project. (PR [#45](https://github.com/omise/omise-magento/pull/45))
- Code styling. (PR [#40](https://github.com/omise/omise-magento/pull/40))

---

### [v1.9.0.6 _(August 10, 2016)_](https://github.com/omise/omise-magento/releases/tag/v1.9.0.6)

#### 🚀 Enhancements

- Remove jQuery library and update code according to new JS checkout form function.

---

### [v1.9.0.5 _(January 5, 2016)_](https://github.com/omise/omise-magento/releases/tag/v1.9.0.5)

#### 🚀 Enhancements

- Extends `Mage_Payment_Model_Method_Abstract` class instead of `Mage_Payment_Model_Method_Cc` in the payment method class.

#### 👾 Bug Fixes

- Check authorized and captured status before continue the charge action.
- Disable submit button action when user clicks submit.

---

### [v1.9.0.4 _(November 16, 2015)_](https://github.com/omise/omise-magento/releases/tag/v1.9.0.4)

#### 🚀 Enhancements

- Add `OmiseMagento/[OmiseMagentoVersion]`, `Magento/[MagentoVersion]` into `OMISE_USER_AGENT_SUFFIX`.
- Update **omise-php** library from 2.3.1 to 2.4.0.

#### 👾 Bug Fixes

- Supported php version 5.2 - 5.3.
- Added autocomplete attribute into card number and security code fields of the checkout form.

---

### [v1.9.0.3 _(August 9, 2015)_](https://github.com/omise/omise-magento/releases/tag/v1.9.0.3)

#### 🚀 Enhancements

- Update **omise-php** library from 2.2.0 to 2.3.1.

#### 👾 Bug Fixes

- Fix `CamelCase` class name issue (CamelCaseName of class was not load in some host environment).

---

### [v1.9.0.2 _(July 7, 2015)_](https://github.com/omise/omise-magento/releases/tag/v1.9.0.2)

#### 🚀 Enhancements

- Update README.md file.
- Change version number in module's xml files from `0.0.0.1` to `1.9.0.2` to match Magento releases.
- Dashboard Page: jQuery from http to https.

---

### [v1.0.1 _(June 23, 2015)_](https://github.com/omise/omise-magento/releases/tag/v1.0.1)

#### 🚀 Enhancements

- At the dashboard page, add link to Omise Dashboard [https://dashboard.omise.co](https://dashboard.omise.co).

#### 👾 Bug Fixes

- Fix Omise Email Account was not shown in dashboard page.
- Fix transfer box was not shown when that account doesn't have any transfer history.

---

### [v1.0.0 _(June 15, 2015)_](https://github.com/omise/omise-magento/releases/tag/v1.0.0)

#### ✨ Highlights

- Implement **Omise Dashboard** into Magento's admin page. The features are as follows:
    - Show current account status (live or test) depends on that you configured in *Omise Keys Setting page*.
    - Show total account balance, transferable balance.
    - Show history of transfers.
    - Admin was able to transfer their *Omise Balance* to their *Bank account*.
- Implement **Omise Keys Setting page** into Magento's admin page.
- Add **Omise menu** into top bar menu of Magento's admin page.
- Add **Omise Payment Gateway Module Configuration** into Margento's admin page in payment method section.
- Implement **Omise Charge API** with `Authorize` with/without `Capture` options.
- Add **Omise Checkout Form** into Magento's checkout page.
- Add [omise-php](https://github.com/omise/omise-php) library *(v2.2.0)* into this extension.
- Update **README.md**.

---

### v0.0.1 _(June 15, 2015)_

- Initial version.
