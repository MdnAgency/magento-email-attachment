<?php
/**
 * StaticAttachment
 *
 * @copyright Copyright © 2023 Maison du Net. All rights reserved.
 * @author    vincent@maisondunet.com
 */

namespace Maisondunet\EmailAttachment\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class MailAttachments extends Serialized
{
    private FileManager $fileUploader;
    private ?Json $serializer;
    private LoggerInterface $logger;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        LoggerInterface $logger,
        FileManager $fileManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data,
            $serializer
        );
        $this->fileUploader = $fileManager;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->logger = $logger;
    }

    /**
     * Processing file upload before save data
     *
     * @return $this
     */
    public function beforeSave(): MailAttachments
    {
        $value = $this->getValue() ?: [];
        unset($value["__empty"]);
        // Handle file uploads
        foreach ($value as $field => $item) {
            try {
                $oldFile = $item['file']['file_orig'] ?? '';
                $newFile = $this->fileUploader->handleUpload($this->getPath(), $field);
                if ($newFile != null) {
                    $value[$field]["file"] = $newFile;
                } elseif ($oldFile) {
                    $value[$field]["file"] = $oldFile;
                } else {
                    unset($value[$field]);
                }
            } catch (\Exception $e) {
            }
        }
        try {
            $oldValue = $this->serializer->unserialize($this->getOldValue());
            if ($oldValue) {
                // Remove old files
                foreach ($oldValue as $oldfield => $val) {
                    if (!array_key_exists($oldfield, $value)) {
                        $fileName = $val['file'];
                        $this->fileUploader->delete($fileName);
                    }
                }
            }
        } catch (\InvalidArgumentException $e) {
            $this->logger->debug("[Maisondunet_EmailAttachment] - Cannot unserialize old config.");
        }
        $this->setValue($value);
        return parent::beforeSave();
    }
}
