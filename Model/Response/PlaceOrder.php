<?php
/**
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     https://github.com/coingate/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */

declare(strict_types = 1);

namespace CoinGate\Merchant\Model\Response;

use CoinGate\Merchant\Api\Response\PlaceOrderInterface as Response;

class PlaceOrder implements Response
{
    private string $paymentUrl = '';
    private bool $status = false;

    /**
     * @inheritDoc
     */
    public function getPaymentUrl(): string
    {
        return $this->paymentUrl;
    }

    /**
     * @inheritDoc
     */
    public function setPaymentUrl(string $paymentUrl): void
    {
        $this->paymentUrl = $paymentUrl;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }
}
