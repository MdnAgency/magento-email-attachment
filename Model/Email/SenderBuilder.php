<?php
/**
 * SenderBuilder
 *
 * @copyright Copyright © 2023 Maison du Net. All rights reserved.
 * @author    vincent@maisondunet.com
 */

namespace Maisondunet\EmailAttachment\Model\Email;

use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;
use Maisondunet\EmailAttachment\Model\AttachmentResolverPool;

class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
    private AttachmentResolverPool $attachmentResolverPool;

    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        TransportBuilder $transportBuilder,
        AttachmentResolverPool $attachmentResolverPool,
        TransportBuilderByStore $transportBuilderByStore = null
    ) {
        parent::__construct($templateContainer, $identityContainer, $transportBuilder, $transportBuilderByStore);
        $this->attachmentResolverPool = $attachmentResolverPool;
        $this->transportBuilder = $transportBuilder;
    }

    protected function configureEmailTemplate()
    {
        parent::configureEmailTemplate();
        $attachments = $this->attachmentResolverPool->getAttachments($this->templateContainer);
        foreach ($attachments as $attachment) {
            $this->transportBuilder->addAttachment($attachment);
        }
    }
}
