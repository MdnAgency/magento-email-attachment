<?php
/**
 * CreditMemo
 *
 * @copyright Copyright © 2023 Maison du Net. All rights reserved.
 * @author    vincent@maisondunet.com
 */

namespace Maisondunet\EmailAttachment\Model\AttachmentResolver;


use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimePartInterface;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Pdf\Creditmemo as PdfCreditMemo;
use Maisondunet\EmailAttachment\Api\AttachmentResolverInterface;
use Maisondunet\EmailAttachment\Model\Config;
use Psr\Log\LoggerInterface;

class CreditMemo implements AttachmentResolverInterface
{
    private array $templates;
    private PdfCreditMemo $pdfCreditMemo;
    private MimePartInterfaceFactory $mimePartInterfaceFactory;
    private LoggerInterface $logger;
    private Config $config;

    public function __construct(
        PdfCreditMemo        $pdfCreditMemo,
        MimePartInterfaceFactory $mimePartInterfaceFactory,
        LoggerInterface $logger,
        Config $config,
        array $templates
    ) {
        $this->templates = $templates;
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory;
        $this->logger = $logger;
        $this->config = $config;
        $this->pdfCreditMemo = $pdfCreditMemo;
    }

    /**
     * @param Template $template
     *
     * @return MimePartInterface[]
     */
    public function getAttachments(Template $template): array
    {
        $attachements = [];
        if ($this->config->isAttachInvoiceEnabled() && in_array($template->getTemplateId(),$this->templates)) {
            $pdf = $this->getPdf($template);
            if ($pdf !== null) {
                $attachements[] = $pdf;
            }
        }
        return $attachements;
    }

    protected function getCreditMemo(Template $template): \Magento\Sales\Model\Order\Creditmemo
    {
        return $template->getTemplateVars()["creditmemo"];
    }

    /**
     * Generate Attachment containing CreditMemo PDF
     * @param Template $template
     *
     * @return MimePartInterface|null
     */
    protected function getPdf(Template $template): ?MimePartInterface
    {
        $creditMemo = $this->getCreditMemo($template);
        $pdf = $this->pdfCreditMemo->getPdf([
            $creditMemo
        ]);
        try {
            return $this->mimePartInterfaceFactory->create([
                'content' => $pdf->render(),
                'fileName' => __('creditmemo_%1.pdf', $creditMemo->getIncrementId()),
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
