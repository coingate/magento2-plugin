<?php
/**
 * CoinGate Callback controller
 *
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace CoinGate\Merchant\Controller\Payment;

use CoinGate\Merchant\Model\Payment as CoinGatePayment;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;

class Callback extends Action
{
    protected $order;
    protected $coingatePayment;

    /**
     * @param Context $context
     * @param Order $order
     * @param Payment|CoinGatePayment $coingatePayment
     * @internal param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Order $order,
        CoinGatePayment $coingatePayment
    )
    {
        parent::__construct($context);

        $this->order = $order;
        $this->coingatePayment = $coingatePayment;
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $order = $this->order->loadByIncrementId($_REQUEST['order_id']);
        $this->coingatePayment->validateCoinGateCallback($order);

        $this->getResponse()->setBody('OK');
    }
}
