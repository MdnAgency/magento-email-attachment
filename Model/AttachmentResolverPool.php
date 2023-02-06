<?php
/**
 * AttachmentResolver
 *
 * @copyright Copyright © 2023 Maison du Net. All rights reserved.
 * @author    vincent@maisondunet.com
 */

namespace Maisondunet\EmailAttachment\Model;

use Magento\Framework\Mail\MimePartInterface;
use Magento\Sales\Model\Order\Email\Container\Template;
use Maisondunet\EmailAttachment\Api\AttachmentResolverInterface;
use Psr\Log\LoggerInterface;

class AttachmentResolverPool implements AttachmentResolverInterface
{

    /**
     * @var AttachmentResolverInterface[]
     */
    private array $attachmentResolvers;
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        array $attachmentResolvers = []
    ) {
        $this->attachmentResolvers = $attachmentResolvers;
        $this->logger = $logger;
    }

    /**
     * @param Template $template
     *
     * @return MimePartInterface[]
     */
    public function getAttachments(Template $template): array
    {
        $attachments = [];
        foreach ($this->attachmentResolvers as $attachmentResolver) {
            try {
                $attachments = array_merge($attachments, $attachmentResolver->getAttachments($template));
            } catch (\Throwable $e) {
                $this->logger->notice("One AttachmentResolver failed with message : {$e->getMessage()}", ["exception" => $e]);
            }
        }
        return $attachments;
    }
}
