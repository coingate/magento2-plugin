<?php
/**
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     https://github.com/coingate/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */

declare(strict_types = 1);

namespace CoinGate\Merchant\Controller\Payment;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Model\OrderRepository;
use Psr\Log\LoggerInterface;

class CancelOrder implements HttpGetActionInterface
{
    private CheckoutSession $checkoutSession;
    private OrderRepository $orderRepository;
    private ResultFactory $resultFactory;
    private LoggerInterface $logger;

    /**
     * @param CheckoutSession $checkoutSession
     * @param OrderRepository $orderRepository
     * @param ResultFactory $resultFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        OrderRepository $orderRepository,
        ResultFactory $resultFactory,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        if ($this->checkoutSession->getLastRealOrderId()) {
            $order = $this->checkoutSession->getLastRealOrder();
            $this->checkoutSession->restoreQuote();

            try {
                $this->orderRepository->delete($order);
            } catch (Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $redirect->setPath('checkout/cart');
    }
}
