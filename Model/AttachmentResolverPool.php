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

class AttachmentResolverPool implements AttachmentResolverInterface
{

    /**
     * @var AttachmentResolverInterface[]
     */
    private array $attachmentResolvers;

    public function __construct(
        array $attachmentResolvers = []
    ) {
        $this->attachmentResolvers = $attachmentResolvers;
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
            $attachments = array_merge($attachments, $attachmentResolver->getAttachments($template));
        }
        return $attachments;
    }
}
