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
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ConfigManagement
{
    private const XML_PATH_PAYMENT_COINGATE_MERCHANT_API_AUTH_TOKEN = 'payment/coingate_merchant/api_auth_token';
    private const XML_PATH_PAYMENT_COINGATE_MERCHANT_SANDBOX_MODE = 'payment/coingate_merchant/sandbox_mode';
    private const XML_PATH_PAYMENT_COINGATE_MERCHANT_RECEIVE_CURRENCY = 'payment/coingate_merchant/receive_currency';
    private ScopeConfigInterface $scopeConfig;
    private StoreManagerInterface $storeManager;
    private ?int $storeId = null;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(ScopeConfigInterface $scopeConfig, StoreManagerInterface $storeManager)
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
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

    public function getReceiveCurrency(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_COINGATE_MERCHANT_RECEIVE_CURRENCY,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return int|null
     */
    private function getStoreId(): ?int
    {
        if (!$this->storeId) {
            try {
                $store = $this->storeManager->getStore();
                $this->storeId = (int)$store->getId();
            } catch (NoSuchEntityException $exception) {
                return $this->storeId;
            }
        }

        return $this->storeId;
    }
}
