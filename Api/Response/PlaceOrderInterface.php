<?php
/**
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     https://github.com/coingate/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */

declare(strict_types = 1);

namespace CoinGate\Merchant\Api\Response;

interface PlaceOrderInterface
{
    /**
     * @return string
     */
    public function getPaymentUrl(): string;

    /**
     * @param string $paymentUrl
     *
     * @return void
     */
    public function setPaymentUrl(string $paymentUrl): void;

    /**
     * @return bool
     */
    public function getStatus(): bool;

    /**
     * @param bool $status
     *
     * @return void
     */
    public function setStatus(bool $status): void;
}
