<?php
/**
 * CoinGate Callback controller
 *
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     https://github.com/coingate/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
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
    ) {

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
        $request_order_id = (filter_input(INPUT_POST, 'order_id')
            ? filter_input(INPUT_POST, 'order_id') : filter_input(INPUT_GET, 'order_id'));

        $order = $this->order->loadByIncrementId($request_order_id);
        $this->coingatePayment->validateCoinGateCallback($order);

        $this->getResponse()->setBody('OK');
    }
}
