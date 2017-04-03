<p align="center"><a href='https://www.omise.co'><img src='https://assets.omise.co/assets/omise-logo-ed530feda8c7bf8b0c990d5e4cf8080a0f23d406fa4049a523ae715252d0dc54.svg' height='60'></a></p>

**Omise Magento** is the official payment extension which provides support for Omise payment gateway for store builders working on the Magento platform.

## Supported Versions

Our aim is to support as many versions of Magento as we can.  

**Here's the list of versions we tested on:**
- Magento (CE) 1.9.3.2 on PHP 5.6.28.
- Magento (CE) 1.9.3.1 on PHP 5.6.28.

To report problems for the version you're using, feel free to submit the issue through [GitHub's issue channel](https://github.com/omise/omise-magento/issues) by following the [Reporting the issue Guideline](https://guides.github.com/activities/contributing-to-open-source/#contributing).

**Can't find the version you're looking for?**  
Submit your requirement as an issue to [https://github.com/omise/omise-magento/issues](https://github.com/omise/omise-magento/issues)

## Getting Started

### Installation Instructions

#### Manually

The steps below shows how to install the module manually.

1. Download and extract the zip file from [Omise-Magento](https://github.com/omise/omise-magento/archive/v1.11.zip) to your `local machine` (or directly to your server).
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
    <p align="center"><a alt="omise-magento-enable-module-01" href='https://cloud.githubusercontent.com/assets/2154669/23231357/0dbb1822-f97a-11e6-851f-3a6f456cfbd6.png'><img title="omise-magento-enable-module-04" src='https://cloud.githubusercontent.com/assets/2154669/23231357/0dbb1822-f97a-11e6-851f-3a6f456cfbd6.png' /></a></p>

In some business models you may need to capture the charge at a later time.  
For example, when you can capture the charge only when you have the product in stock or before shipping. In that case, during checkout Omise will only authorize and hold the charge.  

> _You can hold a charge for as long as permitted by the issuing bank. This delay may vary between cards from 1 to 30 days depending on each card._

The 2 available options for **Payment Action** are `Authorize only` or `Authorize and Capture`.

And, other option is **New order status**, which can be set to `Pending` or `Processing` status. Your order status after checkout process will be set depends on the option here.

---

### Using omise-magento with One Page Checkout (OPC) extension

By default, the checkout process of Magento has many steps and it have been displayed step by step. This default checkout process can be adjusted by the merchant.

One of the adjustment is the merchant will find and install an extension to reduce the checkout steps or change the display of checkout process to be in one page.

There are so many third-party extensions that delivered this feature. But most of those extensions are not compatible with omise-magento. Because omise-magento has a secure step at the client side (the JavaScript at the web browser of the payer) to create a token to be the representative of the card information of the payer. That token is used to be the representative between merchant server and Omise API for process the payment.

Most of extensions have no any procedure to trigger this step of omise-magento. So the merchant who use the OPC need to modify their extension.

#### Prerequisites

1. omise-magento must be succesfully installed on your Magento server.
2. Your selected OPC extension must be successfully installed on your Magento server.
3. You need to have privilege to access and modify your files on the server.
4. You need to have the technical knowledge about JavaScript and PHP, if not please contact your technical staff.

#### Disclaimer about the example of third-party extension
- The reason that we use the IWD One Page Checkout free version because we found it available for download, use and modify.
- Please contact the third-party about their product or service information.
- To download the IWD One Page Checkout extension, please go to this [link](https://www.iwdagency.com/extensions/one-step-page-checkout.html).

**Note:** With the invalid modification, it may adversely affect to the normal process. So, please do the backup before the modification.

The steps below are an example of modifying the OPC extension, IWD One Page Checkout free version, to work with omise-magento.

1. Add a HTML element, hidden, to be the referece within the checkout page and across the extension

    - Open file app/design/frontend/base/default/template/payment/form/omisecc.phtml from the root path and then add an HTML element

    ```HTML
    <input type="hidden" id="omise-public-key" value="<?php echo $this->getOmiseKeys('public_key'); ?>" />
    ```

2. Insert the steps of create Omise token to the checkout process of the OPC extionsion

    - Open file skin/frontend/base/default/js/iwd/opc/checkout.js from the root path
    - Locate to the condition of save payment after validation has success

    ```PHP
    if (paymentMethodForm.validate()) {
        IWD.OPC.savePayment();
    } else {
        // ...
    }
    ```

    - Insert the steps of create Omise token as the example below

    ```PHP
    if (paymentMethodForm.validate()) {
        
        // Begin of the modification
        // Check a condition to create the Omise token only the payment method is omise_gateway
        if (payment.currentMethod == "omise_gateway") {
        
            // Use an external script, omise.js, by using the Omise utility function
            getScript("https://cdn.omise.co/omise.min.js.gz", function() {
                // Set the public key
                Omise.setPublicKey(document.getElementById("omise-public-key").value);

                // Create the card information for submit to create Omise token
                var card = {
                    "name"             : document.getElementById("omise_gateway_cc_name").value,
                    "number"           : document.getElementById("omise_gateway_cc_number").value,
                    "expiration_month" : document.getElementById("omise_gateway_expiration").value,
                    "expiration_year"  : document.getElementById("omise_gateway_expiration_yr").value,
                    "security_code"    : document.getElementById("omise_gateway_cc_cid").value
                };

                // Create Omise token
                Omise.createToken("card", card, function(statusCode, response) {
                    if (statusCode == 200) {
                        // Handle the success case

                        // Set the token value from the response to the HTML element, omise_gateway_token, for submit the checkout process
                        document.getElementById("omise_gateway_token").value = response.id;

                        // Perform the checkout process of the extension normally
                        IWD.OPC.savePayment();
                    } else {
                        // Handle the error case

                        // For an example, display the popup with the error message
                        alert(response.message);
                    }
                });
            });
        } else {
            // Remain the same process of the extension before modification
            IWD.OPC.savePayment();
        }
        // End of the modification

    } else {
        // ...
    }
    ```

Make sure to store the modified files to your server. The checkout process with omise-magento should be work successfully.

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
