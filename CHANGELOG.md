Change Log
==========

An [unreleased] version is not available on `master` branch and is subject to changes and must not be considered final. Elements of unreleased list may be edited or removed at any time.
[unreleased]
------------
- *`Fixed`* Support php version 5.2 - 5.3.
- *`Fixed`* Add autocomplete attribute into card number and security code fields of the checkout form.


[1.8.0.0] 2015-08-09
--------------------
- *`Updated`* Updated **omise-php** library from 2.2.0 to 2.3.1.
- *`Fixed`* Fix 'CamelCase' class name issue (CamelCaseName of class was not load in some host environment).

#### Dashboard Page
- *`Fixed`* jQuery from http to https.

#### Checkout process
- *`Fixed`* Plugin not working in Magento 1.8 and below version.

[1.0.1] 2015-06-23
------------------
#### Dashboard Page
- *`Added`* Added link to Omise Dashboard [https://dashboard.omise.co](https://dashboard.omise.co) into Magento Omise Dashboard Page.
- *`Fixed`* Omise Email Account was not shown in dashboard page.

##### Dashboard Page: Transfer section
- *`Fixed`* Transfer box was not shown when that account doesn't have any transfer history.

[1.0.0] 2015-06-15
------------------
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

[0.0.1] 2015-06-15
------------------
- Initial version.