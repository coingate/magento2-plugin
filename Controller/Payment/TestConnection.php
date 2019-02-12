<?php

/**
 * CoinGate test API authorization controller
 *
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     https://github.com/coingate/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */

namespace CoinGate\Merchant\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use CoinGate\CoinGate;
use \Magento\Store\Model\ScopeInterface;
use CoinGate\Merchant\Model\Payment\Interceptor;

class TestConnection extends Action
{

    protected $checkoutSession;
    protected $scopeConfig;


    public function __construct(
        Context $context,
        Session $checkoutSession,
        ScopeConfigInterface $scopeConfig
    )

    {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
    }


    public function execute()
    {
        if (!$this->scopeConfig->getValue('payment/coingate_merchant/api_auth_token', ScopeInterface::SCOPE_STORE)) {
            $this->getResponse()->setBody(json_encode([
                'status' => false,
                'reason' => $test,
            ]));
                return;
        }

        $test =  CoinGate::testConnection(CoinGate::config([
            'environment' => $this->scopeConfig->getValue('payment/coingate_merchant/sandbox_mode',ScopeInterface::SCOPE_STORE) ? 'sandbox' : 'live',
            'auth_token'  => $this->scopeConfig->getValue('payment/coingate_merchant/api_auth_token', ScopeInterface::SCOPE_STORE),
            'user_agent'  => 'CoinGate - Magento 2 Extension v' . Interceptor::COINGATE_MAGENTO_VERSION
        ]));

        if($test !== true) {

            $this->getResponse()->setBody(json_encode([
                'status' => false,
                'reason' => $test,
            ]));
                return;
        } else {

            $this->getResponse()->setBody(json_encode([
                'status' => true,
            ]));
                return;
        }
    }


}
