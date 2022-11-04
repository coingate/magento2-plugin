<?php
/**
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     https://github.com/coingate/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */

declare(strict_types = 1);

namespace CoinGate\Merchant\Api;

use CoinGate\Merchant\Api\Response\PlaceOrderInterface;

/**
 * Interface PaymentInterface
 */
interface PaymentInterface
{
    /**
     * @return \CoinGate\Merchant\Api\Response\PlaceOrderInterface
     */
    public function placeOrder(): PlaceOrderInterface;
}
