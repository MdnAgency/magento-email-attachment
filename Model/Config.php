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

class Config
{
    public const XML_PATH_PREFIX = 'sales_email';

    private ScopeConfigInterface $scopeConfig;
    private SerializerInterface $serializer;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
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

    public function getAttachments()
    {
        return $this->serializer->unserialize($this->getConfig('static_attachments'));
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
