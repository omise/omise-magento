# CHANGELOG

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
