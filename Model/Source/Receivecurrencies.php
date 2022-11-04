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

/**
 * Class Receivecurrencies
 *
 * Source of option values in a form of value-label pairs
 */
class Receivecurrencies implements OptionSourceInterface
{
    /**
     * @var string
     */
    private const BTC = 'btc';

    /**
     * @var string
     */
    private const USDT = 'usdt';

    /**
     * @var string
     */
    private const EUR = 'eur';

    /**
     * @var string
     */
    private const USD = 'usd';

    /**
     * @var string
     */
    private const DO_NOT_CONVERT = 'DO_NOT_CONVERT';

    /**
     * Return array of options as value-label pairs
     *
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
          ['value' => self::BTC, 'label' => __('Bitcoin (฿)')],
          ['value' => self::USDT, 'label' => __('USDT')],
          ['value' => self::EUR, 'label' => __('Euros (€)')],
          ['value' => self::USD, 'label' => __('US Dollars ($)')],
          ['value' => self::DO_NOT_CONVERT, 'label' => __('Do not convert')]
        ];
    }
}
