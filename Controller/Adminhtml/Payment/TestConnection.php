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
use Magento\Framework\App\ProductMetadataInterface;

class TestConnection extends Action implements HttpPostActionInterface
{
    private SerializerInterface $serializer;
    private ProductMetadataInterface $metadata;
    private ConfigManagement $configManagement;

    /**
     * @param Context $context
     * @param SerializerInterface $serializer
     * @param ConfigManagement $configManagement
     * @param ProductMetadataInterface $metadata
     */
    public function __construct(
        Context $context,
        SerializerInterface $serializer,
        ConfigManagement $configManagement,
        ProductMetadataInterface $metadata
    ) {
        $this->serializer = $serializer;
        $this->configManagement = $configManagement;
        $this->metadata = $metadata;

        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResponseInterface
     */
    public function execute(): ResponseInterface
    {
        $apiAuthToken = $this->configManagement->getApiAuthToken();
        $sandboxMode = $this->configManagement->isSandboxMode();
        $response = $this->getResponse();

        if (!$apiAuthToken) {
            $result = [
                'status'  => false,
                'content' => __('No API Token entered')
            ];

            return $response->representJson($this->serializer->serialize($result));
        }

        Client::setAppInfo($this->configManagement->getName(), $this->configManagement->getVersion());
        $clientResponse = Client::testConnection(
            $apiAuthToken,
            $sandboxMode
        );

        $result = [
            'status'  => false,
            'content' => __('An error has occurred. Check the correctness of the data.')
        ];

        if ($clientResponse) {
            $result = [
                'status'  => true,
                'content' => __('CoinGate connection is working properly.')
            ];
        }

        return $response->representJson($this->serializer->serialize($result));
    }
}
