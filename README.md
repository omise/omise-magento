<p align="center"><a href='https://www.omise.co'><img src='https://cloud.githubusercontent.com/assets/2154669/26388730/437207e4-4080-11e7-9955-2cd36bb3120f.png' height='160'></a></p>

**Omise Magento** is the official payment extension which provides support for Omise payment gateway for store builders working on the Magento platform.

## Supported Versions

Our aim is to support as many versions of Magento as we can.  

**Here's the list of versions we tested on:**
- Magento (CE) 2.2.4, tested on PHP 7.0.29.

To report problems for the version you're using, feel free to submit the issue through [GitHub's issue channel](https://github.com/omise/omise-magento/issues) by following the [Reporting the issue Guideline](https://guides.github.com/activities/contributing-to-open-source/#contributing).

### Legacy Support

For merchants looking for support for Magento (CE) 1.x series, please check [1-stable](https://github.com/omise/omise-magento/tree/1-stable) branch.

**Can't find the version you're looking for?**  
Submit your requirement as an issue to [https://github.com/omise/omise-magento/issues](https://github.com/omise/omise-magento/issues)

## Getting Started

### Installation Instructions

#### Via Composer

Installing the extension is done via [composer](https://getcomposer.org/). Simply run the following command in the Magento root folder:

<pre>
composer require "omise/omise-magento:@stable"
</pre>

Once this is done, run the following commands:

<pre>
php bin/magento module:enable Omise_Payment --clear-static-content
php bin/magento setup:upgrade
php bin/magento setup:di:compile
</pre>

### First Time Setup

After installing, you can configure the module by:

1. Log in to your Magento back-end with the administrator account.
2. Go to `Stores` > `Configuration` > `Sales` > `Payment Methods`.

Settings is displayed under the `Omise` section.

<p align="center"><a alt="omise-magento-install-manual-04" href='https://user-images.githubusercontent.com/10651523/51095402-15ac2c00-17e7-11e9-8ee2-b2122ccf5401.png'><img src='https://user-images.githubusercontent.com/10651523/51095402-15ac2c00-17e7-11e9-8ee2-b2122ccf5401.png'></a></p>

The table below is the settings for the module and the description for each setting.

| Setting             | Description                                                                                                             |
| ------------------- | ----------------------------------------------------------------------------------------------------------------------- |                                                                            
| Sandbox             | If selected, all transactions will be performed in TEST mode and TEST keys will be used                                 |
| Public key for test | Your TEST public key can be found in your Dashboard.                                                                    |
| Secret key for test | Your TEST secret key can be found in your Dashboard.                                                                    |
| Public key for live | Your LIVE public key can be found in your Dashboard.                                                                    |
| Secret key for live | Your LIVE secret key can be found in your Dashboard.                                                                    |
| Webhook for endpoint | Read-only url to enable post order payment notifications. See [Webhooks](https://www.omise.co/api-webhooks) article.      |


- To enable `sandbox` mode, select the setting for `Sandbox` to `Yes`.

**Note:**

If the setting for `Sandbox` is set to `Yes`, the keys for TEST will be used. If the setting for `Sandbox` is set to `No`, the keys for LIVE will be used.

**Credit Card Solution**  
Available options:

| Setting             | Description     |
| ---------- | ---------------------------------- |                                                                            
| Enable/Disable | Enables or disables 'Credit Card Payment method' | 
| Payment Action | Set `Authorize Only` to only authorize a payment or `Authorize and Capture` to automatically capture payment after authorising. |
| Title | Payment Title displayed during checkout |
| 3-D Secure Support | Enable or disable support for additional Credit Card payment authorization |

**Internet Banking Solution**  
Available options:

| Setting             | Description   |
| --------- | --------------------------- |                                                                            
| Enable/Disable | Enables or disables 'Internet Banking Payment method'|
| Title | Payment Title displayed during checkout |


**Alipay Payment Solution**  
Available options:

| Setting             | Description  |
| ------------ | --------------------------------- |                                                                            
| Enable/Disable | Enables or disables 'Alipay Payment method'|
| Title |Payment Title displayed during checkout |

**Tesco Bill Payment Solution**  
Available options:

| Setting             | Description  |
| ------------ | --------------------------------- |                                                                            
| Enable/Disable | Enables or disables 'Tesco Bill Payment method'|
| Title |Payment Title displayed during checkout |

**Installment Payment Solution**  
Available options:

| Setting             | Description  |
| ------------ | --------------------------------- |                                                                            
| Enable/Disable | Enables or disables 'Installment Payment method'|
| Title |Payment Title displayed during checkout |

## Contributing

Thanks for your interest in contributing to Omise Magento. We're looking forward to hearing your thoughts and willing to review your changes.

The following subjects are instructions for contributors who consider to submit changes and/or issues.

### Submit the changes

You're all welcome to submit a pull request.
Please consider the [pull request template](https://github.com/omise/omise-magento/blob/master/.github/PULL_REQUEST_TEMPLATE.md) and fill the form when you submit a new pull request.

Learn more about submitting pull request here: [https://help.github.com/articles/about-pull-requests](https://help.github.com/articles/about-pull-requests)

### Submit the issue

Submit the issue through [GitHub's issue channel](https://github.com/omise/omise-magento/issues).

Learn more about submitting an issue here: [https://guides.github.com/features/issues](https://guides.github.com/features/issues)

## License
Omise-Magento is open-sourced software released under the [MIT License](https://opensource.org/licenses/MIT).
