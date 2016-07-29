<?php
/**
 * CoinGate payment method model
 *
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace CoinGate\Merchant\Model;

use CoinGate\Merchant as CoinGateMerchant;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class Payment extends AbstractMethod
{
    const COINGATE_MAGENTO_VERSION = '1.0.3';
    const CODE = 'coingate_merchant';

    protected $_code = 'coingate_merchant';

    protected $_isInitializeNeeded = true;

    protected $urlBuilder;
    protected $coingate;
    protected $storeManager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param CoinGateMerchant $coingate
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @internal param ModuleListInterface $moduleList
     * @internal param TimezoneInterface $localeDate
     * @internal param CountryFactory $countryFactory
     * @internal param Http $response
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        CoinGateMerchant $coingate,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = array()
    )
    {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->urlBuilder = $urlBuilder;
        $this->coingate = $coingate;
        $this->storeManager = $storeManager;

        $this->coingate->initialize(array(
            'app_id' => $this->getConfigData('app_id'),
            'api_key' => $this->getConfigData('api_key'),
            'api_secret' => $this->getConfigData('api_secret'),
            'mode' => $this->getConfigData('sandbox_mode') ? 'sandbox' : 'live',
            'user_agent' => 'CoinGate - Magento 2 Extension v' . self::COINGATE_MAGENTO_VERSION
        ));
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getCoinGateRequest(Order $order)
    {
        $token = substr(md5(rand()), 0, 32);

        $payment = $order->getPayment();
        $payment->setAdditionalInformation('coingate_order_token', $token);
        $payment->save();

        $description = array();
        foreach ($order->getAllItems() as $item) {
            $description[] = number_format($item->getQtyOrdered(), 0) . ' × ' . $item->getName();
        }

        $params = array(
            'order_id' => $order->getIncrementId(),
            'price' => number_format($order->getGrandTotal(), 2, '.', ''),
            'currency' => $order->getOrderCurrencyCode(),
            'receive_currency' => $this->getConfigData('receive_currency'),
            'callback_url' => ($this->urlBuilder->getUrl('coingate/payment/callback') . '?token=' . $payment->getAdditionalInformation('coingate_order_token')),
            'cancel_url' => $this->urlBuilder->getUrl('checkout/onepage/failure'),
            'success_url' => $this->urlBuilder->getUrl('checkout/onepage/success'),
            'title' => $this->storeManager->getWebsite()->getName(),
            'description' => join($description, ', ')
        );

        $this->coingate->createOrder($params);

        if ($this->coingate->success) {
            return array(
                'status' => true,
                'payment_url' => $this->coingate->response['payment_url']
            );
        } else {
            return array(
                'status' => false
            );
        }
    }

    /**
     * @param Order $order
     */
    public function validateCoinGateCallback(Order $order)
    {
        try {
            if (!$order || !$order->getIncrementId()) {
                $request_order_id = (filter_input(INPUT_POST, 'order_id') ? filter_input(INPUT_POST, 'order_id') : filter_input(INPUT_GET, 'order_id'));

                throw new \Exception('Order #' . $request_order_id . ' does not exists');
            }

            $payment = $order->getPayment();
            $get_token = filter_input(INPUT_GET, 'order_id');
            $token1 = isset($get_token) ? $get_token : '';
            $token2 = $payment->getAdditionalInformation('coingate_order_token');

            if ($token2 == '' || $token1 != $token2) {
                throw new \Exception('Tokens do match.');
            }

            $request_id = (filter_input(INPUT_POST, 'id') ? filter_input(INPUT_POST, 'id') : filter_input(INPUT_GET, 'id'));
            $this->coingate->getOrder($request_id);

            if (!$this->coingate->success) {
                throw new \Exception('CoinGate Order #' . $request_id . ' does not exist');
            }

            if (!is_array($this->coingate->response)) {
                throw new \Exception('Something wrong with callback');
            }

            if ($this->coingate->response['status'] == 'paid') {
                $order
                    ->setState(Order::STATE_PROCESSING, TRUE)
                    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING))
                    ->save();
            } elseif (in_array($this->coingate->response['status'], array('invalid', 'expired', 'canceled'))) {
                $order
                    ->setState(Order::STATE_CANCELED, TRUE)
                    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED))
                    ->save();
            }
        } catch (\Exception $e) {
            exit('Error occurred: ' . $e);
        }
    }
}
