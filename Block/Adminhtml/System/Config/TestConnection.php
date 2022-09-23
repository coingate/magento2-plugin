<?php
/**
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     https://github.com/coingate/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */

declare(strict_types = 1);

namespace CoinGate\Merchant\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class TestConnection
 */
class TestConnection extends Field
{
    /**
     * @var string
     */
    private const BUTTON_LABEL_KEY = 'button_label';

    /**
     * @var string
     */
    private const BUTTON_URL_KEY = 'button_url';

    /**
     * @var string
     */
    private const HTML_ID_KEY = 'html_id';

    /**
     * @var string
     */
    protected $_template = 'CoinGate_Merchant::system/config/test-connection.phtml';

    /**
     * Unset scope
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element->unsScope();

        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $originalData = $element->getOriginalData();

        $this->addData([
            self::BUTTON_LABEL_KEY => $originalData[self::BUTTON_LABEL_KEY],
            self::BUTTON_URL_KEY => $this->getUrl($originalData[self::BUTTON_URL_KEY], ['_current' => true]),
            self::HTML_ID_KEY => $element->getHtmlId(),
        ]);

        return $this->_toHtml();
    }
}
