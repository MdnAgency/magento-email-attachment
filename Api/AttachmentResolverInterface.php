<?php
/**
 * Created by Maisondunet.
 *
 * @project     dexergie
 * @author      vincent
 * @copyright   2023 LA MAISON DU NET
 * @link        https://www.maisondunet.com
 */

namespace Maisondunet\EmailAttachment\Api;

use Magento\Framework\Mail\MimePartInterface;
use Magento\Sales\Model\Order\Email\Container\Template;

interface AttachmentResolverInterface
{

    /**
     * @param Template $template
     *
     * @return MimePartInterface[]
     */
    public function getAttachments(Template $template): array;

}
