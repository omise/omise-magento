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
