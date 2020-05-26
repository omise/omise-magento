<p align="center"><a href='https://www.omise.co'><img src='https://cloud.githubusercontent.com/assets/2154669/26388730/437207e4-4080-11e7-9955-2cd36bb3120f.png' height='160'></a></p>

:warning: Magento 1 end-of-life (EOL) is [30 June 2020](https://magento.com/blog/magento-news/supporting-magento-1-through-june-2020).
We urge all merchants still using Magento 1 to upgrade to Magento 2 and install our [Magento 2 plugin](https://www.omise.co/magento-2).

---

**Omise Magento** is the official payment extension which provides support for Omise payment gateway for store builders working on the Magento platform.

## Supported Versions

Our aim is to support as many versions of Magento as we can.  

**Here's the list of versions we tested on:**
- Magento (CE) 1.9.3.9 on PHP 5.6.33.

To report problems for the version you're using, feel free to submit the issue through [GitHub's issue channel](https://github.com/omise/omise-magento/issues) by following the [Reporting the issue Guideline](https://guides.github.com/activities/contributing-to-open-source/#contributing).

**Can't find the version you're looking for?**  
Submit your requirement as an issue to [https://github.com/omise/omise-magento/issues](https://github.com/omise/omise-magento/issues)

## Getting Started

### Installation Instructions

#### Manually

The steps below shows how to install the module manually.

1. Download and extract the zip file from [Omise-Magento](https://github.com/omise/omise-magento/archive/v1.22.zip) to your `local machine` (or directly to your server).
    <p align="center"><a alt="omise-magento-install-manual-01" href='https://cloud.githubusercontent.com/assets/2154669/23201743/8ecb09da-f90d-11e6-836f-1fc935f6ea5e.png'><img title="omise-magento-install-manual-01" src='https://cloud.githubusercontent.com/assets/2154669/23201743/8ecb09da-f90d-11e6-836f-1fc935f6ea5e.png' /></a></p>
2. locate to `/src` directory and copy **all files** into your **Magento Project**, at a root directory.
3. Your installation is now completed. To check the installation result, open your **Magento admin page**.
    If everything goes well, You must see `Omise` menu on the top of your admin page.  
    <p align="center"><a alt="omise-magento-install-manual-02" href='https://cloud.githubusercontent.com/assets/2154669/23206424/303e5382-f920-11e6-89a0-b4265371a2c3.png'><img title="omise-magento-install-manual-02" src='https://cloud.githubusercontent.com/assets/2154669/23206424/303e5382-f920-11e6-89a0-b4265371a2c3.png' /></a></p>
4. If you cannot see the `Omise` menu, try clear the system caches by going to `System > Cache Management`. Then, `Flush Magento Cache`.
    <p align="center"><a alt="omise-magento-install-manual-03" href='https://cloud.githubusercontent.com/assets/2154669/23206201/57f1c34c-f91f-11e6-83a5-a2de34c873b0.png'><img title="omise-magento-install-manual-03" src='https://cloud.githubusercontent.com/assets/2154669/23206201/57f1c34c-f91f-11e6-83a5-a2de34c873b0.png' /></a></p>
    <p align="center"><a alt="omise-magento-install-manual-04" href='https://cloud.githubusercontent.com/assets/2154669/23206320/c539ac9e-f91f-11e6-803d-7eb7a29bf8e2.png'><img title="omise-magento-install-manual-04" src='https://cloud.githubusercontent.com/assets/2154669/23206320/c539ac9e-f91f-11e6-803d-7eb7a29bf8e2.png' /></a></p>
5. Next, check the [First Time Setup](https://github.com/omise/omise-magento#first-time-setup) to continue setting up your Omise account to Magento store.

### First Time Setup

#### Omise Keys Setup

In order to make a charge with Omise, you have to link the store to your Omise account by using your [credentials](https://www.omise.co/api-authentication) (public and secret keys):

1. At the admin top bar menu. Click `Omise > Keys Setting`. You will be redirected to the Omise module setting page.
2. In this page you can set your `Omise Keys`. Anyway if you want to test Omise service integration, you can enable *test mode* by clicking `Enable test mode`. Your Magento will then process orders with your test keys.
    <p align="center"><a alt="omise-magento-keys-setup-01" href='https://cloud.githubusercontent.com/assets/2154669/23207427/d9a3da98-f923-11e6-9e4a-3b588be9b0d8.png'><img title="omise-magento-install-manual-04" src='https://cloud.githubusercontent.com/assets/2154669/23207427/d9a3da98-f923-11e6-9e4a-3b588be9b0d8.png' /></a></p>

#### Enabling Omise Module

In order to enable **Omise Payment Gateway** in the checkout page, you have to enable the module from Magento's configuration page, at the payment method section.  

1. At the admin top bar menu. Click `Omise > Module Setting`. You will be redirected to the Magento's payment method configuration page.
2. At the bottommost of the page, you will see `Omise Payment Gateway` section with 2 payment methods, Credit Card and [Internet Banking](https://www.omise.co/offsite-payment).  
    To enable the Omise Payment Gateway, choose one (or both) and then, set the option `Enabled` to `Yes` to activate the payment method and save the config. 
    <p align="center"><a alt="omise-magento-enable-module-01" href='https://cloud.githubusercontent.com/assets/2154669/26388922/50f6cdcc-4081-11e7-8ee8-f525e5d3a1ad.png'><img title="omise-magento-enable-module-01" src='https://cloud.githubusercontent.com/assets/2154669/26388922/50f6cdcc-4081-11e7-8ee8-f525e5d3a1ad.png' /></a></p>

In some business models you may need to capture the charge at a later time.  
For example, when you can capture the charge only when you have the product in stock or before shipping. In that case, during checkout Omise will only authorize and hold the charge.  

> _You can hold a charge for as long as permitted by the issuing bank. This delay may vary between cards from 1 to 30 days depending on each card._

The 2 available options for **Payment Action** are `Authorize only` or `Authorize and Capture`.

And, other option is **New order status**, which can be set to `Pending` or `Processing` status. Your order status after checkout process will be set depends on the option here.

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
