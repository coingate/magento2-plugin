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
