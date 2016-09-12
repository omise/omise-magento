# Omise extension for Magento eCommerce

## Magento Version Compatibility
- Magento (CE) 1.9.2.4

## Dependencies
- [omise-php](https://github.com/omise/omise-php) (v2.4.0)

## Installation
Follow these steps to install **omise-magento:**  

1. Download this repository and unzip it into your `local machine` (or directly to your server)  
  Download links: [omise-margento.zip](https://github.com/omise/omise-magento/archive/master.zip) or [omise-margento.tar.gz](https://github.com/omise/omise-magento/archive/master.tar.gz)
  ![omise-magento Folder Structure](https://cdn.omise.co/assets/omise-magento/installation-01.png)  

2. Go to `/omise-magento/src` and copy **all files** into your **Magento Project**  

3. Open your **Magento website**, then go to `/admin` page  

If the everything went fine, the `Omise` menu will appear on the top of your admin page.  
  ![Omise menu in Magento's admin](https://cdn.omise.co/assets/omise-magento/installation-02.png)  

## Enabling Omise Module
In order to enable **Omise Payment Gateway** in checkout page, you have to enable omise-magento module from Magento's configuration page, in payment method section.  

1. In admin page. Go to `Omise` > `Module Setting` (from the top menu). It will lead you to the Magento's payment method configuration page.  
  ![Magento's configuration Menu](https://cdn.omise.co/assets/omise-magento/module-enable-01.png)  

2. In the bottom of the page that opens, you will see `Omise Payment Gateway` tab.  
  So, set the option `Module Enabled` to `Yes` to activate **Omise Payment Gateway** module. Then click save.  
  ![Magento's configuration Menu](https://cdn.omise.co/assets/omise-magento/module-enable-02.png)  

3. In some business models you may need to capture the charge at a later time. For example, when you can capture the charge only when you have the product in stock or before shipping. In that case, during checkout Omise will only authorize and hold the charge.  *You can hold a charge for as long as permitted by the issuing bank. This delay may vary between cards from 1 to 30 days depending on each card.*

The 2 available options for **Payment Action** are `Authorize only` or `Authorize and Capture`. 
  ![Omise's Payment Action Configuration](https://cdn.omise.co/assets/omise-magento/module-enable-03.png)  

Other option is **New order status**, which can be set to `Pending` or `Processing` status, depending on your business model.


## Omise Keys Setup
In order to use omise-magento you have to link it to your Omise account using your credentials:

1. In admin page. Go to `Omise` > `Keys Setting` (from the top menu)  
  ![Omise Menu](https://cdn.omise.co/assets/omise-magento/omise-keys-setup-01.png)

2. In this page you can save your `Omise Keys`. If you want to test Omise service integration, you can enable *test mode* by clicking `Enable test mode`. Your Magento will then process orders with your test keys.  
  ![Omise Payment Gateway Form](https://cdn.omise.co/assets/omise-magento/omise-keys-setup-02.png)

## Checkout with Omise Payment Gateway
After setting up your Omise keys, you can checkout with Omise Payment Gateway. In order to test it, make sure you set up your test keys and enabled test mode.
  ![Checkout](https://cdn.omise.co/assets/omise-magento/checkout-with-omise-01.png)

1. Visit your website and add something to your cart.

2. Go to your cart and checkout as regular. Let's focus on step 5 (or 4 if you already logged in) **Payment Information**)

3. In this step (step #5 in Magento) a new choice will appear: **Credit Card (Powered by Omise)**, select it. The form allows you to fill in credit card details. You can use a test credit card number from [our documentation](https://docs.omise.co/api/tests/).  
Then click the **Continue** button.  
![Checkout with Omise Payment Gateway Form](https://cdn.omise.co/assets/omise-magento/checkout-with-omise-02.png)

4. In previous step, we just created a Credit Card token to be used with `Omise Charge API` in this step. If the card is authorixed, just click on **Place Order** button. If you want to know how we collect and process your card, please check our documentation: [Collecting Cards](https://docs.omise.co/collecting-card-information/) and [Charging Cards](https://docs.omise.co/charging-cards/))  
  ![Checkout with Omise Payment Gateway](https://cdn.omise.co/assets/omise-magento/checkout-with-omise-03.png)  

5. Once completed, you will be redirected to Magento *Thank you* page.  
  ![Checkout complete](https://cdn.omise.co/assets/omise-magento/checkout-with-omise-04.png)  

6. Now head back to your admin dashboard orders section: `Sales` > `Orders`. You will see your order with `Pending` or `Processing` status (depends on your configuration).  
  ![Admin's order page](https://cdn.omise.co/assets/omise-magento/checkout-with-omise-05.png)  

## Using omise-magento with One Page Checkout (OPC) extension
By default, the checkout process of Magento has many steps and it have been displayed step by step. This default checkout process can be adjusted by the merchant.

One of the adjustment is the merchant will find and install an extension to reduce the checkout steps or change the display of checkout process to be in one page.

There are so many third-party extensions that delivered this feature. But most of those extensions are not compatible with omise-magento. Because omise-magento has a secure step at the client side (the JavaScript at the web browser of the payer) to create a token to be the representative of the card information of the payer. That token is used to be the representative between merchant server and Omise API for process the payment.

Most of extensions have no any procedure to trigger this step of omise-magento. So the merchant who use the OPC need to modify their extension.

## Prerequisites ##
1. omise-magento must be succesfully installed on your Magento server.
2. Your selected OPC extension must be successfully installed on your Magento server.
3. You need to have privilege to access and modify your files on the server.
4. You need to have the technical knowledge about JavaScript and PHP, if not please contact your technical staff.

#### Disclaimer about the example of third-party extension ####
- The reason that we use the IWD One Page Checkout free version because we found it available for download, use and modify.
- Please contact the third-party about their product or service information.
- To download the IWD One Page Checkout extension, please go to this [link](https://www.iwdagency.com/extensions/one-step-page-checkout.html).

**Note:**
With the invalid modification, it may adversely affect to the normal process. So, please do the backup before the modification.

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
      ...
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
      ...
  }
  ```

Make sure to store the modified files to your server. The checkout process with omise-magento should be work successfully.