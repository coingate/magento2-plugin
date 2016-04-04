<?php
/**
 * CoinGate PlaceOrder controller
 *
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace CoinGate\Merchant\Controller\Payment;

use CoinGate\Merchant\Model\Payment as CoinGatePayment;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderFactory;

class PlaceOrder extends Action
{
    protected $orderFactory;
    protected $coingatePayment;
    protected $checkoutSession;

    /**
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param Session $checkoutSession
     * @param CoinGatePayment $coingatePayment
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        Session $checkoutSession,
        CoinGatePayment $coingatePayment
    )
    {
        parent::__construct($context);

        $this->orderFactory = $orderFactory;
        $this->coingatePayment = $coingatePayment;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute()
    {
        $id = $this->checkoutSession->getLastOrderId();

        $order = $this->orderFactory->create()->load($id);

        if (!$order->getIncrementId()) {
            $this->getResponse()->setBody(json_encode(array(
                'status' => false,
                'reason' => 'Order Not Found',
            )));

            return;
        }

        $this->getResponse()->setBody(json_encode($this->coingatePayment->getCoinGateRequest($order)));

        return;
    }
}
