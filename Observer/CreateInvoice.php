<?php
/**
 * @category    CoinGate
 * @package     CoinGate_Merchant
 * @author      CoinGate
 * @copyright   CoinGate (https://coingate.com)
 * @license     https://github.com/coingate/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */

declare(strict_types = 1);

namespace CoinGate\Merchant\Observer;

use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Class CreateInvoice
 *
 * @package CoinGate\Merchant\Observer
 */
class CreateInvoice implements ObserverInterface
{
    private InvoiceService $invoiceService;
    private Transaction $transaction;
    private InvoiceSender $invoiceSender;
    private LoggerInterface $logger;

    /**
     * CreateInvoice constructor.
     *
     * @param InvoiceService $invoiceService
     * @param InvoiceSender $invoiceSender
     * @param Transaction $transaction
     * @param LoggerInterface $logger
     */
    public function __construct(
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        Transaction $transaction,
        LoggerInterface $logger
    ) {
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->logger = $logger;
    }

    /**
     * Create an invoice after successful payment
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();
        $order = $event->getOrder();

        if ($order->canInvoice()) {
            try {
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->register();
                $invoice->pay();

                $order = $invoice->getOrder();
                $order->addCommentToStatusHistory(__('Invoice #%1 created.', $invoice->getId()));
                $order->setIsCustomerNotified(true);
                $transactionSave = $this->transaction->addObject($invoice);
                $transactionSave->addObject($order);
                $transactionSave->save();
                $this->invoiceSender->send($invoice);
            } catch (Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }
    }
}
