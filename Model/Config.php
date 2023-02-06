<?php
/**
 * Config
 *
 * @copyright Copyright © 2023 Maison du Net. All rights reserved.
 * @author    vincent@maisondunet.com
 */

namespace Maisondunet\EmailAttachment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class Config
{
    public const XML_PATH_PREFIX = 'sales_email';

    private ScopeConfigInterface $scopeConfig;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @param string $key
     * @param null|int $store
     * @return null|string
     */
    public function getConfig(string $key, string $group = 'mdn_attachment', ?int $store = null)
    {
        $result = $this->scopeConfig->getValue(
            self::XML_PATH_PREFIX . "/$group/$key",
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $store
        );
        return $result;
    }

    /**
     * Get status module
     * @return bool
     */
    public function isEnabled(): bool
    {
        return !!$this->getConfig('enabled');
    }

    /**
     * Return list of attachment defined in system config
     * @return array
     */
    public function getAttachments(): array
    {
        try {
            $attachements = $this->serializer->unserialize($this->getConfig('static_attachments'));
            if (is_array($attachements)) {
                return $attachements;
            }
        } catch (\InvalidArgumentException $e) {
            $this->logger->debug("Cannot unserialize attachements", ["exception" => $e]);
        }
        return [];
    }

    /**
     * Is Invoice Attachement enabled
     * @return bool
     */
    public function isAttachInvoiceEnabled(): bool
    {
        return !!$this->getConfig('mdn_attach_pdf', 'invoice');
    }

    /**
     * Is Shipment Attachement enabled
     * @return bool
     */
    public function isAttachShipmentEnabled(): bool
    {
        return !!$this->getConfig('mdn_attach_pdf', 'shipment');
    }

    /**
     * Is Shipment Attachement enabled
     * @return bool
     */
    public function isAttachCreditMemoEnabled(): bool
    {
        return !!$this->getConfig('mdn_attach_pdf', 'creditmemo');
    }
}
