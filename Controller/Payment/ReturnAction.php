<?php

namespace CoinGate\Merchant\Controller\Payment;

use Magento\Framework\App\Action\Action;

class ReturnAction extends Action
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
     * @return void
     */
    public function execute()
    {
        if ($this->_getCheckout()->getLastRealOrderId()) {
            $this->_redirect('checkout/onepage/success');
        }
    }
}
