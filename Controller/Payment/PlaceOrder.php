<?php
/**
 * CoinGate PlaceOrder controller
 *
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     https://github.com/coingate/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */

namespace CoinGate\Merchant\Controller\Payment;

use CoinGate\Merchant\Model\Payment as CoinGatePayment;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class PlaceOrder extends Action
{
    protected $orderFactory;
    protected $coingatePayment;
    protected $checkoutSession;
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;


    /**
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param Session $checkoutSession
     * @param CoinGatePayment $coingatePayment
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        Context $context,
        OrderFactory $orderFactory,
        Session $checkoutSession,
        CoinGatePayment $coingatePayment,
        ScopeConfigInterface $scopeConfig
    ) {

        parent::__construct($context);
        $this->quoteRepository = $quoteRepository;
        $this->_eventManager = $eventManager;
        $this->orderFactory = $orderFactory;
        $this->coingatePayment = $coingatePayment;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
    }


    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }


    public function execute()
    {
        $id = $this->checkoutSession->getLastOrderId();

        $order = $this->orderFactory->create()->load($id);

        if (!$order->getIncrementId()) {
            $this->getResponse()->setBody(json_encode([
                'status' => false,
                'reason' => 'Order Not Found',
            ]));
            return;
        }

        ///Restores Cart
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $quote->setIsActive(1);
        $this->quoteRepository->save($quote);

        $this->getResponse()->setBody(json_encode($this->coingatePayment->getCoinGateRequest($order)));
    }

}
