<?php
/**
 * Invoice
 *
 * @copyright Copyright © 2023 Maison du Net. All rights reserved.
 * @author    vincent@maisondunet.com
 */

namespace Maisondunet\EmailAttachment\Model\AttachmentResolver;

use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Pdf\Invoice as PdfInvoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;

use Maisondunet\EmailAttachment\Api\AttachmentResolverInterface;
use Maisondunet\EmailAttachment\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Attach PDF invoice to "sales_email_invoice_template" and "sales_email_invoice_guest_template"
 */
class Invoice implements AttachmentResolverInterface
{
    private array $templates;
    private PdfInvoice $pdfInvoice;
    private MimePartInterfaceFactory $mimePartInterfaceFactory;
    private LoggerInterface $logger;
    private Config $config;

    public function __construct(
        PdfInvoice        $pdfInvoice,
        MimePartInterfaceFactory $mimePartInterfaceFactory,
        LoggerInterface $logger,
        Config $config,
        array $templates
    ) {
        $this->templates = $templates;
        $this->pdfInvoice = $pdfInvoice;
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param Template $template
     *
     * @return \Magento\Framework\Mail\MimePartInterface[]
     */
    public function getAttachments(Template $template): array
    {
        $attachements = [];
        if ($this->config->isAttachInvoiceEnabled() && in_array($template->getTemplateId(), $this->templates)) {
            $pdf = $this->getInvoicePdf($template);
            if ($pdf !== null) {
                $attachements[] = $pdf;
            }
        }
        return $attachements;
    }

    protected function getInvoice(Template $template): \Magento\Sales\Model\Order\Invoice
    {
        return $template->getTemplateVars()["invoice"];
    }

    /**
     * Generate Attachment containing Invoice PDF
     * @param Template $template
     *
     * @return \Magento\Framework\Mail\MimePartInterface|null
     */
    protected function getInvoicePdf(Template $template): ?\Magento\Framework\Mail\MimePartInterface
    {
        $invoice = $this->getInvoice($template);
        $pdf = $this->pdfInvoice->getPdf([
            $invoice
        ]);
        try {
            return $this->mimePartInterfaceFactory->create([
                'content' => $pdf->render(),
                'fileName' => sprintf('invoice_%s.pdf', $invoice->getIncrementId()),
                'disposition' => MimeInterface::DISPOSITION_ATTACHMENT,
                'encoding' => MimeInterface::ENCODING_BASE64,
                'type' => 'application/pdf'
            ]);
        } catch (\Zend_Pdf_Exception $e) {
            $this->logger->warning($e->getMessage(), ["exception" => $e]);
            return null;
        }
    }
}
