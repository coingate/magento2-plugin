<?php

namespace CoinGate\Merchant\Controller\Payment;

use Magento\Framework\App\Action\Action;

class CancelOrder extends Action
{

    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }


    /**
     * Redirect to to checkout success
     *
     */
    public function execute()
    {
        if ($this->_getCheckout()->getLastRealOrderId()) {

            $order = $this->_getCheckout()->getLastRealOrder();
            if ($order->getId() && ! $order->isCanceled()) {
                $order->registerCancellation('Canceled by Customer')->save();
                return true;
            }

            $this->_getCheckout()->restoreQuote();
            $this->_redirect('checkout/cart');
        }
    }

}