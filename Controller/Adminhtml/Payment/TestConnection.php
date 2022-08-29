<?php
/**
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     https://github.com/coingate/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */

declare(strict_types = 1);

namespace CoinGate\Merchant\Controller\Adminhtml\Payment;

use CoinGate\Client;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use CoinGate\Merchant\Model\ConfigManagement;

class TestConnection extends Action implements HttpPostActionInterface
{
    private SerializerInterface $serializer;
    private ConfigManagement $configManagement;

    /**
     * @param Context $context
     * @param SerializerInterface $serializer
     * @param ConfigManagement $configManagement
     */
    public function __construct(Context $context, SerializerInterface $serializer, ConfigManagement $configManagement)
    {
        $this->serializer = $serializer;
        $this->configManagement = $configManagement;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface
     */
    public function execute(): ResponseInterface
    {
        $apiAuthToken = $this->configManagement->getApiAuthToken();
        $sandboxMode = $this->configManagement->isSandboxMode();

        if (!$apiAuthToken) {
            $result = [
                'status'  => false,
                'content' => __('No API Token entered')
            ];

            return $this->getResponse()->representJson($this->serializer->serialize($result));
        }

        $response = Client::testConnection(
            $apiAuthToken,
            $sandboxMode
        );

        $result = [
            'status'  => false,
            'content' => __('An error has occurred. Check the correctness of the data.')
        ];

        if ($response) {
            $result = [
                'status'  => true,
                'content' => __('CoinGate connection is working properly.')
            ];
        }

        return $this->getResponse()->representJson($this->serializer->serialize($result));
    }
}
