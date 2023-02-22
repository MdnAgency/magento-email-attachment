<?php
/**
 * ConfigStatic
 *
 * @copyright Copyright © 2023 Maison du Net. All rights reserved.
 * @author    vincent@maisondunet.com
 */

namespace Maisondunet\EmailAttachment\Model\AttachmentResolver;

use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Sales\Model\Order\Email\Container\Template;
use Maisondunet\EmailAttachment\Api\AttachmentResolverInterface;
use Maisondunet\EmailAttachment\Model\Config;
use Maisondunet\EmailAttachment\Model\Config\Backend\FileManager;



/**
 * Load Sales email attachment defined in System Configuration
 */
class SystemConfiguration implements AttachmentResolverInterface
{
    private Config $config;
    private FileManager $fileManager;
    private MimePartInterfaceFactory $mimePartInterfaceFactory;

    public function __construct(
        Config $config,
        FileManager $fileManager,
        MimePartInterfaceFactory $mimePartInterfaceFactory
    ) {
        $this->config = $config;
        $this->fileManager = $fileManager;
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory;
    }

    /**
     * @param Template $template
     *
     * @return array|\Magento\Framework\Mail\MimePartInterface[]
     */
    public function getAttachments(Template $template): array
    {
        $attachments = [];
        foreach ($this->config->getAttachments() as $attachment) {
            if (in_array($template->getTemplateId(), $attachment["templates"])) {
                $fileName = $attachment["file"];
                $mimeType = $this->fileManager->getFileMime($fileName);
                $content = $this->fileManager->getFileContent($fileName);
                if ($content != null && $mimeType != null) {
                    $attachments[] = $this->mimePartInterfaceFactory->create([
                        'content' => $content,
                        'fileName' => $fileName,
                        'disposition' => MimeInterface::DISPOSITION_ATTACHMENT,
                        'encoding' => MimeInterface::ENCODING_BASE64,
                        'type' => $mimeType
                    ]);
                }
            }
        }
        return $attachments;
    }
}
