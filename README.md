![Omise-Magento](https://omise-cdn.s3.amazonaws.com/artwork/magento_omise_bodered.png)

## Magento Version Compatibility
- Magento (CE) 1.9.1.0

## Dependencies
- [omise-php](https://github.com/omise/omise-php) (v2.2.0)

## Installation
Follow these steps to install **omise-opencart:**  

1. Download this repository and unzip it into your `local machine` (or directly to your server)  
  Download links: [omise-margento-v1.0.zip]() or [omise-margento-v1.0.tar.gz]()

2. Go to `/omise-magento/src` and copy **all files** into your **Magento Project**  

3. Open your **Magento website**, then go to `/admin` page  

If the everything went fine, the `Omise` menu will appear on the top of your admin page.

## Module enable
In order to enable **Omise Payment Method** in checkout page, you have to enable omise-magento module from Magento's configuration page, in payment method section.  

1. In admin page. Go to `Omise` > `Module Setting` (from the top menu). It will lead you to the Magento's payment method configuration page.  
![Magento's configuration Menu](https://omise-cdn.s3.amazonaws.com/assets/omise-magento/module-enable-01.png)  

2. In the bottom of the page that opens, you will see `Omise Payment Gateway` tab.  
So, in `Module Enabled` filed, set it to `Yes` for enable **Omise Payment Method**. Then save the config.  
![Magento's configuration Menu](https://omise-cdn.s3.amazonaws.com/assets/omise-magento/module-enable-02.png)  

3. In some of business model needs to authorize a card only (without capture amount from cards). So, you can set **Payment Action** in 2 choices `Authorize only` or `Authorize and Capture`. It's depends on your business model.  
![Omise's Payment Action Configuration](https://omise-cdn.s3.amazonaws.com/assets/omise-magento/module-enable-03.png)  
Also **New order status**. It's available for set to `Pending` or `Processing` status, depens on your business model too.


## Omise Keys Setup
In order to use omise-magento you have to link it to your Omise account using your credentials:

1. In admin page. Go to `Omise` > `Keys Setting` (from the top menu)  
![Omise Menu](https://omise-cdn.s3.amazonaws.com/assets/omise-magento/omise-keys-setup-01.png)

2. The page that opens allows you to save your `Omise Keys`. If you want to test Omise service integration, you can enable *test mode* by clicking Enable test mode. Your Magento will then process orders with your test keys.  
![Omise Payment Gateway Form](https://omise-cdn.s3.amazonaws.com/assets/omise-magento/omise-keys-setup-02.png)

## Checkout with Omise Payment Gateway
After setting up your Omise keys, you can checkout with Omise Payment Gateway. In order to test it, make sure you set up your test keys and enabled test mode.
![Checkout](https://omise-cdn.s3.amazonaws.com/assets/omise-magento/checkout-with-omise-01.png)

1. Visit your website and add something to your cart.

2. Go to your cart and checkout (regular Magento process until now. Let's focus on step 5 **Payment Information**)

3. In this step (step #5 in Magento) the **Credit Card (Powered by Omise)** choice will be available, select it. The form allows you to fill in your credit card details. You can use a test credit card number from [our documentation](https://docs.omise.co/api/tests/). Then click **Continue** button
![Checkout with Omise Payment Gateway Form](https://omise-cdn.s3.amazonaws.com/assets/omise-magento/checkout-with-omise-02.png)

4. In previous step, we just created a card's token for use with `Omise Capture API` in this step. If everything fine, just click **Place Order** button. So, if you set your **Payment Action** in **Omise Payment Method** module to `Authorize only` it just authorize your card only (without capture).
![Checkout with Omise Payment Gateway](https://omise-cdn.s3.amazonaws.com/assets/omise-magento/checkout-with-omise-03.png)

## Uninstalling Omise
