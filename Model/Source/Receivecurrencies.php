<?php
/**
 * Receive currencies Source Model
 *
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     https://github.com/coingate/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */
namespace CoinGate\Merchant\Model\Source;

class Receivecurrencies
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
          ['value' => 'btc', 'label' => 'Bitcoin (฿)'],
          ['value' => 'usdt', 'label' => 'USDT'],
          ['value' => 'eur', 'label' => 'Euros (€)'],
          ['value' => 'usd', 'label' => 'US Dollars ($)'],
          ['value' => 'DO_NOT_CONVERT', 'label' => 'Do not convert']
        ];
    }
}
