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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Module\ResourceInterface;

/**
 * Class ConfigManagement
 */
class ConfigManagement
{
    /**
     * @var string
     */
    private const NAME = 'CoinGate_Merchant';

    /**
     * @var string
     */
    private const XML_PATH_PAYMENT_COINGATE_MERCHANT_API_AUTH_TOKEN = 'payment/coingate_merchant/api_auth_token';

    /**
     * @var string
     */
    private const XML_PATH_PAYMENT_COINGATE_MERCHANT_SANDBOX_MODE = 'payment/coingate_merchant/sandbox_mode';

    /**
     * @var string
     */
    private const XML_PATH_PAYMENT_COINGATE_MERCHANT_RECEIVE_CURRENCY = 'payment/coingate_merchant/receive_currency';

    /**
     * @var string
     */
    private const XML_PATH_PAYMENT_COINGATE_MERCHANT_PRE_FILL_EMAIL = 'payment/coingate_merchant/pre_fill_email';

    private ScopeConfigInterface $scopeConfig;
    private StoreManagerInterface $storeManager;
    private LoggerInterface $logger;
    private ResourceInterface $resource;
    private ?int $storeId = null;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param ResourceInterface $resource
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        ResourceInterface $resource
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->resource->getDataVersion(self::NAME);
    }

    /**
     * Get Api Authorization Token
     *
     * @return string|null
     */
    public function getApiAuthToken(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_COINGATE_MERCHANT_API_AUTH_TOKEN,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get Status Mode
     *
     * @return bool
     */
    public function isSandboxMode(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PAYMENT_COINGATE_MERCHANT_SANDBOX_MODE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ) ?? false;
    }

    /**
     * Get Receive Currency
     *
     * @return string|null
     */
    public function getReceiveCurrency(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_COINGATE_MERCHANT_RECEIVE_CURRENCY,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return bool
     */
    public function isPreFillEmail(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PAYMENT_COINGATE_MERCHANT_PRE_FILL_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ) ?? false;
    }

    /**
     * Get Store Id
     *
     * @return int|null
     */
    private function getStoreId(): ?int
    {
        if (!$this->storeId) {
            try {
                $store = $this->storeManager->getStore();
                $this->storeId = (int) $store->getId();
            } catch (NoSuchEntityException $exception) {
                $this->logger->critical($exception->getMessage());

                return $this->storeId;
            }
        }

        return $this->storeId;
    }
}
