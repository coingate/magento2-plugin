<?php
/**
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     https://github.com/coingate/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */

declare(strict_types = 1);

namespace CoinGate\Merchant\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Receivecurrencies implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
          ['value' => 'btc', 'label' => __('Bitcoin (฿)')],
          ['value' => 'usdt', 'label' => __('USDT')],
          ['value' => 'eur', 'label' => __('Euros (€)')],
          ['value' => 'usd', 'label' => __('US Dollars ($)')],
          ['value' => 'DO_NOT_CONVERT', 'label' => __('Do not convert')]
        ];
    }
}
