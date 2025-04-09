<?php
/**
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     https://github.com/coingate/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */

declare(strict_types = 1);

namespace CoinGate\Merchant\Model;

use CoinGate\Client;
use CoinGate\Exception\ApiErrorException;
use CoinGate\Resources\CreateOrder;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\StoreManagerInterface;
use CoinGate\Merchant\Model\Ui\ConfigProvider;
use Psr\Log\LoggerInterface;

use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\Framework\DB\TransactionFactory;

/**
 * Class Payment
 */
class Payment
{
    /**
     * @var string
     */
    public const COINGATE_ORDER_TOKEN_KEY = 'coingate_order_token';

    /**
     * @var string
     */
    private const PAID_STATUS = 'paid';

    /**
     * @var array
     */
    private const STATUSES_FOR_CANCEL = [
        'invalid',
        'expired',
        'canceled',
    ];

    /**
     * @var array
     */
    private const STATUSES_FOR_REFUND = [
        'refunded',
        'partially_refunded',
    ];

    private UrlInterface $urlBuilder;
    private StoreManagerInterface $storeManager;
    private OrderManagementInterface $orderManagement;
    private OrderPaymentRepositoryInterface $paymentRepository;
    private ?Client $client = null;
    private ConfigManagement $configManagement;
    private OrderRepository $orderRepository;
    private LoggerInterface $logger;
    private EventManagerInterface $eventManager;

    private CreditmemoFactory $creditmemoFactory;
    private CreditmemoService $creditmemoService;
    private TransactionFactory $transactionFactory;

    /**
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param OrderManagementInterface $orderManagement
     * @param OrderPaymentRepositoryInterface $paymentRepository
     * @param ConfigManagement $configManagement
     * @param OrderRepository $orderRepository
     * @param LoggerInterface $logger
     * @param EventManagerInterface $eventManager
     * @param CreditmemoFactory $creditmemoFactory
     * @param CreditmemoService $creditmemoService
     * @param TransactionFactory $transactionFactory
     * @param InvoiceRepository $invoiceRepository
     */
    public function __construct(
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        OrderManagementInterface $orderManagement,
        OrderPaymentRepositoryInterface $paymentRepository,
        ConfigManagement $configManagement,
        OrderRepository $orderRepository,
        LoggerInterface $logger,
        EventManagerInterface $eventManager,
        CreditmemoFactory $creditmemoFactory,
        CreditmemoService $creditmemoService,
        TransactionFactory $transactionFactory,
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->orderManagement = $orderManagement;
        $this->paymentRepository = $paymentRepository;
        $this->configManagement = $configManagement;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * Get CoinGate Order From API
     *
     * @param OrderInterface $order
     *
     * @return CreateOrder|mixed
     */
    public function getCoinGateOrder(OrderInterface $order)
    {
        $description = [];
        $token = substr(hash('sha256', (string) rand()), 0, 32);
        $payment = $order->getPayment();
        $payment->setAdditionalInformation(self::COINGATE_ORDER_TOKEN_KEY, $token);
        $this->paymentRepository->save($payment);

        foreach ($order->getAllItems() as $item) {
            $description[] = number_format((float)$item->getQtyOrdered(), 0) . ' × ' . $item->getName();
        }

        try {
            $params = $this->getCoinGateOrderParams($order, $description);
        } catch (LocalizedException $exception) {
            $this->logger->critical($exception->getMessage());

            return null;
        }

        $client = $this->getClient();

        try {
            $cgOrder = $client->order->create($params);
        } catch (ApiErrorException $exception) {
            $this->logger->critical($exception->getMessage());

            return null;
        }

        return $cgOrder;
    }

    /**
     * Validate CoinGate Callback
     *
     * @param Order $order
     * @param int $requestId
     *
     * @return bool
     */
    public function validateCoinGateCallback(Order $order, int $requestId): bool
    {
        if (!$this->isCoingatePaymentMerchant($order)) {
            return false;
        }

        try {
            $client = $this->getClient();
            $cgOrder = $client->order->get($requestId);

            if (!$cgOrder) {
                throw new Exception('CoinGate Order #' . $requestId . ' does not exist');
            }

            if ($cgOrder->status === self::PAID_STATUS) {
                $this->processOrderPaid($order);
            } elseif (in_array($cgOrder->status, self::STATUSES_FOR_CANCEL)) {
                $this->orderManagement->cancel($cgOrder->order_id);
            } elseif (in_array($cgOrder->status, self::STATUSES_FOR_REFUND)) {
                $this->processOrderRefund($order);
            }
        } catch (Exception $exception) {
            $this->logger->critical($exception);

            return false;
        }

        return true;
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function isCoingatePaymentMerchant(Order $order): bool
    {
        $payment = $order->getPayment();

        return $payment->getMethod() === ConfigProvider::CODE;
    }

    /**
     * @param Order $order
     *
     * @return void
     */
    private function processOrderPaid(Order $order): void
    {
        $order->setState(Order::STATE_PROCESSING);
        $orderConfig = $order->getConfig();
        $order->setStatus($orderConfig->getStateDefaultStatus(Order::STATE_PROCESSING));
        $this->orderRepository->save($order);

        $this->eventManager->dispatch('coingate_merchant_callback_send', ['order' => $order]);
    }

    /**
     * @param Order $order
     *
     * @return void
     */
    private function processOrderRefund(Order $order): void
    {
        if (!$order->hasInvoices()) {
            throw new Exception('Order #' . $order->getId() . ' has no invoices');
        }

        foreach ($order->getInvoiceCollection() as $invoice) {
            $creditmemo = $this->creditmemoFactory->createByInvoice($invoice);
            $creditmemo->setRefundRequested(true);
            $creditmemo->setOfflineRequested(true);
            $this->creditmemoService->refund($creditmemo);
            $this->transactionFactory->create()->addObject($creditmemo)->save();
        }
    }


    /**
     * Get Http CoinGate Client
     *
     * @return Client|null
     */
    private function getClient(): ?Client
    {
        if (!$this->client) {
            $environment = $this->configManagement->isSandboxMode();
            Client::setAppInfo($this->configManagement->getName(), $this->configManagement->getVersion());
            $this->client = new Client($this->configManagement->getApiAuthToken(), $environment);
        }

        return $this->client;
    }

    /**
     * @param OrderInterface $order
     * @param array $description
     *
     * @return array
     *
     * @throws LocalizedException
     */
    private function getCoinGateOrderParams(OrderInterface $order, array $description): array
    {
        $payment = $order->getPayment();
        $params = [
            'order_id' => $order->getIncrementId(),
            'price_amount' => number_format((float) $order->getGrandTotal(), 2, '.', ''),
            'price_currency' => $order->getOrderCurrencyCode(),
            'callback_url' => $this->urlBuilder->getUrl(
                'coingate/payment/callback',
                [
                    '_query' => [
                        'token' => $payment->getAdditionalInformation(self::COINGATE_ORDER_TOKEN_KEY)
                    ]
                ]
            ),
            'cancel_url' => $this->urlBuilder->getUrl('coingate/payment/cancelOrder'),
            'success_url' => $this->urlBuilder->getUrl('checkout/onepage/success'),
            'title' => $this->storeManager->getWebsite()->getName(),
            'description' => implode(', ', $description),
            'token' => $payment->getAdditionalInformation(self::COINGATE_ORDER_TOKEN_KEY)
        ];

        if ($this->configManagement->isPreFillShopperDetails()) {
            $params['shopper'] = $this->getShopperInfo($order);
        }

        return $params;
    }

    /**
     * @param OrderInterface $order
     *
     * @return array
     */
    private function getShopperInfo(OrderInterface $order): array
    {
        $billingAddress = $order->getBillingAddress();
        $isBusiness = !empty($billingAddress->getCompany()) || !empty($billingAddress->getVatId());
        $street = $billingAddress->getStreet()[0] ?? '';

        $shopper = [
            'type' => $isBusiness ? 'business' : 'personal',
            'ip_address' => $order->getRemoteIp(),
            'email' => $order->getCustomerEmail(),
            'first_name' => $order->getCustomerFirstname(),
            'last_name' => $order->getCustomerLastname(),
            'date_of_birth' => $order->getCustomerDob() ? date('Y-m-d', strtotime($order->getCustomerDob())) : null,
        ];

        if ($isBusiness) {
            $shopper['company_details'] = [
                'name' => $billingAddress->getCompany(),
                'address' => $street,
                'postal_code' => $billingAddress->getPostcode(),
                'city' => $billingAddress->getCity(),
                'country' => $billingAddress->getCountryId(),
            ];
        } else {
            $shopper['residence_address'] = $street;
            $shopper['residence_postal_code'] = $billingAddress->getPostcode();
            $shopper['residence_city'] = $billingAddress->getCity();
            $shopper['residence_country'] = $billingAddress->getCountryId();
        }

        return $shopper;
    }
}
