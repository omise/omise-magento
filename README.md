<p align="center"><a href='https://www.omise.co'><img src='https://assets.omise.co/assets/omise-logo-ed530feda8c7bf8b0c990d5e4cf8080a0f23d406fa4049a523ae715252d0dc54.svg' height='60'></a></p>

**Omise Magento** is the official payment extension which provides support for Omise payment gateway for store builders working on the Magento platform.

## Supported Versions

Our aim is to support as many versions of Magento as we can.  

**Here's the list of versions we tested on:**
- Magento (CE) 2.1.3, tested on PHP 7.0.10.
- Magento (CE) 2.1.2, tested on PHP 7.0.10.

To report problems for the version you're using, feel free to submit the issue through [GitHub's issue channel](https://github.com/omise/omise-magento/issues) by following the [Reporting the issue Guideline](https://guides.github.com/activities/contributing-to-open-source/#contributing).

### Legacy Support

For merchants looking for support for Magento (CE) 1.x series, please check [1-stable](https://github.com/omise/omise-magento/tree/1-stable) branch.

**Can't find the version you're looking for?**  
Submit your requirement as an issue to [https://github.com/omise/omise-magento/issues](https://github.com/omise/omise-magento/issues)

## Getting Started

### Installation Instructions

#### Manually

The steps below shows how to install the module manually.
This requires a user account with permission to write your Magento file system, execute the command line and [composer](https://getcomposer.org).

1. Download and extract the zip file from [Omise-Magento](https://github.com/omise/omise-magento/archive/master.zip).
2. On your Magento server, create a new folder `Omise/Payment` under the directory `app/code`.
3. Copy all files to the directory `app/code/Omise/Payment` that you created in the step 2.
  <p align="center"><a alt="omise-magento-install-manual-01" href='https://cloud.githubusercontent.com/assets/2154669/21450750/247ec3b6-c92c-11e6-92d5-3c036568f20b.png'><img src='https://cloud.githubusercontent.com/assets/2154669/21450750/247ec3b6-c92c-11e6-92d5-3c036568f20b.png'></a></p>
4. Log in to your Magento server by using Terminal program, then change the current directory to your Magento root directory.
5. Execute command `composer require omise/omise-php:2.6.0` to install Omise-PHP library.
6. Execute `php bin/magento module:enable Omise_Payment --clear-static-content` to enable the module.
  <p align="center"><a alt="omise-magento-install-manual-03" href='https://cloud.githubusercontent.com/assets/2154669/21455545/a2c51e10-c953-11e6-88b6-3eff010860df.png'><img src='https://cloud.githubusercontent.com/assets/2154669/21455545/a2c51e10-c953-11e6-88b6-3eff010860df.png'></a></p>
7. At the current directory, execute the command, `php bin/magento setup:upgrade` to upgrade your Magento system.
8. Your installation is now completed. Check the [First Time Setup](https://github.com/omise/omise-magento#first-time-setup) to continue setting up your Omise account to Magento store.

### First Time Setup

After installing, you can configure the module by:

1. Log in to your Magento back-end with the administrator account.
2. Go to `Stores` > `Configuration` > `Sales` > `Payment Methods`.

Settings is displayed under the `Omise` section.

<p align="center"><a alt="omise-magento-install-manual-04" href='https://cloud.githubusercontent.com/assets/2154669/21477670/9918e2b4-cb76-11e6-8b8d-74ec746b7812.png'><img src='https://cloud.githubusercontent.com/assets/2154669/21477670/9918e2b4-cb76-11e6-8b8d-74ec746b7812.png'></a></p>

The table below is the settings for the module and the description for each setting.

| Setting             | Description                                                                                                             |
| ------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| Enable/Disable      | Enables or disables 'Omise Payment Module'                                                                              |
| Sandbox             | If selected, all transactions will be performed in TEST mode and TEST keys will be used                                 |
| Public key for test | Your TEST public key can be found in your Dashboard.                                                                    |
| Secret key for test | Your TEST secret key can be found in your Dashboard.                                                                    |
| Public key for live | Your LIVE public key can be found in your Dashboard.                                                                    |
| Secret key for live | Your LIVE secret key can be found in your Dashboard.                                                                    |
| Payment Action      | Set `Authorize Only` to only authorize a payment or `Authorize and Capture` to automatically capture after authroizing. |
| Title               | Title of Omise Payment gateway shown at checkout.                                                                       |

- To enable the module, select the setting for `Enable/Disable` to `Yes`.
- To enable `sandbox` mode, select the setting for `Sandbox` to `Yes`.

**Note:**

If the setting for `Sandbox` is set to `Yes`, the keys for TEST will be used. If the setting for `Sandbox` is set to `No`, the keys for LIVE will be used.

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
