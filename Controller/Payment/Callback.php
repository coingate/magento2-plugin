<?php

namespace CoinGate\Merchant\Controller\Payment;

use CoinGate\Merchant\Model\Payment as CoinGatePayment;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Laminas\Http\Response;
use Laminas\Http\AbstractMessage;

class Callback implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var string
     */
    private const TOKEN_KEY = 'token';

    /**
     * @var string
     */
    private const ORDER_ID_KEY = 'order_id';

    /**
     * @var string
     */
    private const ID_KEY = 'id';

    /**
     * @var string
     */
    private const NOT_FOUND_PHRASE = 'Not Found';

    /**
     * @var string
     */
    private const UNPROCESSABLE_CONTENT_PHRASE = 'Unprocessable Content';

    private ResponseInterface $response;
    private RequestInterface $request;
    private Order $order;
    private CoinGatePayment $coingatePayment;
    private SerializerInterface $serializer;
    private EventManagerInterface $eventManager;

    /**
     * @param Order $order
     * @param CoinGatePayment $coingatePayment
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param SerializerInterface $serializer
     * @param EventManagerInterface $eventManager
     */
    public function __construct(
        Order $order,
        CoinGatePayment $coingatePayment,
        RequestInterface $request,
        ResponseInterface $response,
        SerializerInterface $serializer,
        EventManagerInterface $eventManager
    ) {
        $this->order = $order;
        $this->coingatePayment = $coingatePayment;
        $this->request = $request;
        $this->response = $response;
        $this->serializer = $serializer;
        $this->eventManager = $eventManager;
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResponseInterface
     */
    public function execute(): ResponseInterface
    {
        $requestOrderId = $this->request->getParam(self::ORDER_ID_KEY) ?? '';
        $requestId = (int) $this->request->getParam(self::ID_KEY) ?? 0;

        if (!$requestOrderId) {
            return $this->response->setStatusHeader(
                Response::STATUS_CODE_422,
                AbstractMessage::VERSION_11,
                self::UNPROCESSABLE_CONTENT_PHRASE
            );
        }

        $order = $this->order->loadByIncrementId($requestOrderId);

        if (!$order->getId()) {
            return $this->response->setStatusHeader(
                Response::STATUS_CODE_422,
                AbstractMessage::VERSION_11,
                self::UNPROCESSABLE_CONTENT_PHRASE
            );
        }

        $payment = $order->getPayment();
        $token = $this->request->getParam(self::TOKEN_KEY) ?? '';

        if (!$token || $token !== $payment->getAdditionalInformation(CoinGatePayment::COINGATE_ORDER_TOKEN_KEY)) {
            return $this->response->setStatusHeader(
                Response::STATUS_CODE_422,
                AbstractMessage::VERSION_11,
                self::UNPROCESSABLE_CONTENT_PHRASE
            );
        }

        if (!$this->coingatePayment->validateCoinGateCallback($order, $requestId)) {
            return $this->response->setStatusHeader(
                Response::STATUS_CODE_404,
                AbstractMessage::VERSION_11,
                self::NOT_FOUND_PHRASE
            );
        }

        $this->eventManager->dispatch('coingate_merchant_callback_send', ['order' => $order]);

        return $this->response->setStatusHeader(Response::STATUS_CODE_200, AbstractMessage::VERSION_11);
    }

    /**
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
