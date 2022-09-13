<?php
/**
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     https://github.com/coingate/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */

declare(strict_types = 1);

namespace CoinGate\Merchant\Model\Services;

use CoinGate\Merchant\Api\PaymentInterface;
use CoinGate\Merchant\Api\Response\PlaceOrderInterface as Response;
use CoinGate\Merchant\Model\Payment as CoinGatePayment;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

class Payment implements PaymentInterface
{
    private Response $response;
    private CheckoutSession $checkoutSession;
    private OrderRepository $orderRepository;
    private CartRepositoryInterface $quoteRepository;
    private CoinGatePayment $coinGatePayment;
    private Order $order;
    private CoinGatePayment $coingatePayment;
    private RequestInterface $request;
    private LoggerInterface $logger;

    /**
     * @param Response $response
     * @param CheckoutSession $checkoutSession
     * @param OrderRepository $orderRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param CoinGatePayment $coinGatePayment
     * @param Order $order
     * @param CoinGatePayment $coingatePayment
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     */
    public function __construct(
        Response $response,
        CheckoutSession $checkoutSession,
        OrderRepository $orderRepository,
        CartRepositoryInterface $quoteRepository,
        CoinGatePayment $coinGatePayment,
        Order $order,
        CoinGatePayment $coingatePayment,
        RequestInterface $request,
        LoggerInterface $logger
    ) {
        $this->response = $response;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->coinGatePayment = $coinGatePayment;
        $this->order = $order;
        $this->coingatePayment = $coingatePayment;
        $this->request = $request;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function placeOrder(): Response
    {
        $orderId = $this->checkoutSession->getLastOrderId();

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (InputException | NoSuchEntityException $exception) {
            $this->logger->critical($exception->getMessage());
            $this->response->setStatus(false);

            return $this->response;
        }

        if (!$order->getIncrementId()) {
            $this->response->setStatus(false);

            return $this->response;
        }

        $quote = $this->quoteRepository->get($order->getQuoteId());
        $quote->setIsActive(1);
        $this->quoteRepository->save($quote);
        $cgOrder = $this->coinGatePayment->getCoinGateOrder($order);

        if (!$cgOrder) {
            $this->response->setStatus(false);

            return $this->response;
        }

        $this->response->setStatus(true);
        $this->response->setPaymentUrl($cgOrder->payment_url);

        return $this->response;
    }

    /**
     * @inheritDoc
     */
    public function updateOrder(): void
    {
        $requestOrderId = $this->request->getParam('order_id');
        $requestId = (int)$this->request->getParam('id');

        if (!$requestOrderId) {
            return;
        }

        $order = $this->order->loadByIncrementId($requestOrderId);

        if (!$order->getId()) {
            return;
        }

        $payment = $order->getPayment();
        $token = $this->request->getParam('token');

        if (!$token || $token !== $payment->getAdditionalInformation('coingate_order_token')) {
            return;
        }

        $this->coingatePayment->validateCoinGateCallback($order, $requestId);
    }
}
