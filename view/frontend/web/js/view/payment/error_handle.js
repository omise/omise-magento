/**
 * Handle payment error
 */
function handlePaymentError(errorProcessor, stopPlaceOrder, messageContainer, response) {
    errorProcessor.process(response, messageContainer);
    stopPlaceOrder();
};