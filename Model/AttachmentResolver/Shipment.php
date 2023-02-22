<?php
/**
 * Invoice
 *
 * @copyright Copyright © 2023 Maison du Net. All rights reserved.
 * @author    vincent@maisondunet.com
 */

namespace Maisondunet\EmailAttachment\Model\AttachmentResolver;

use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimePartInterface;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Pdf\Shipment as PdfShipment;
use Magento\Sales\Model\Order\Shipment as ShipmentModel;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;

use Maisondunet\EmailAttachment\Api\AttachmentResolverInterface;
use Maisondunet\EmailAttachment\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Attach PDF invoice to "sales_email_invoice_template" and "sales_email_invoice_guest_template"
 */
class Shipment implements AttachmentResolverInterface
{
    private array $templates;
    private PdfShipment $pdfShipment;
    private MimePartInterfaceFactory $mimePartInterfaceFactory;
    private LoggerInterface $logger;
    private Config $config;

    public function __construct(
        PdfShipment        $pdfShipment,
        MimePartInterfaceFactory $mimePartInterfaceFactory,
        LoggerInterface $logger,
        Config $config,
        array $templates
    ) {
        $this->templates = $templates;
        $this->pdfShipment = $pdfShipment;
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param Template $template
     *
     * @return MimePartInterface[]
     */
    public function getAttachments(Template $template): array
    {
        $attachements = [];
        if ($this->config->isAttachShipmentEnabled() && in_array($template->getTemplateId(), $this->templates)) {
            $pdf = $this->getPdf($template);
            if ($pdf !== null) {
                $attachements[] = $pdf;
            }
        }
        return $attachements;
    }

    /**
     * @param Template $template
     *
     * @return ShipmentModel
     */
    protected function getShipment(Template $template): ShipmentModel
    {
        return $template->getTemplateVars()["shipment"];
    }

    /**
     * Generate Attachment containing Shipment PDF
     * @param Template $template
     *
     * @return MimePartInterface|null
     */
    protected function getPdf(Template $template): ?MimePartInterface
    {
        $shipment = $this->getShipment($template);
        $pdf = $this->pdfShipment->getPdf([
            $shipment
        ]);
        try {
            return $this->mimePartInterfaceFactory->create([
                'content' => $pdf->render(),
                'fileName' => __("shipment_%1.pdf", $shipment->getIncrementId()),
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
