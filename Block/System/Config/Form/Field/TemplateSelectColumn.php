<?php
/**
 * TemplateSelectColumn
 *
 * @copyright Copyright © 2023 Maison du Net. All rights reserved.
 * @author    vincent@maisondunet.com
 */

namespace Maisondunet\EmailAttachment\Block\System\Config\Form\Field;

use Magento\Framework\View\Element\Html\Select;
class TemplateSelectColumn extends Select
{
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value . '[]');
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        $this->setExtraParams('multiple="multiple" style="width: 200px"');
        return parent::_toHtml();
    }

    private function getSourceOptions(): array
    {
        return [
            ['label' => __('Creditmemo'),               'value' => 'sales_email_creditmemo_template'],
            ['label' => __('Creditmemo (Guest)'),       'value' => 'sales_email_creditmemo_guest_template'],
            ['label' => __('Creditmemo Comment'),       'value' => 'sales_email_creditmemo_comment_template'],
            ['label' => __('Creditmemo Update (Guest)'),'value' => 'sales_email_creditmemo_comment_guest_template'],
            ['label' => __('Invoice'),                  'value' => 'sales_email_invoice_template'],
            ['label' => __('Invoice (Guest)'),          'value' => 'sales_email_invoice_guest_template'],
            ['label' => __('Invoice Comment'),          'value' => 'sales_email_invoice_comment_template'],
            ['label' => __('Invoice Comment (Guest)'),  'value' => 'sales_email_invoice_comment_guest_template'],
            ['label' => __('Order'),                    'value' => 'sales_email_order_template'],
            ['label' => __('Order (Guest)'),            'value' => 'sales_email_order_guest_template'],
            ['label' => __('Order Comment'),            'value' => 'sales_email_order_comment_template'],
            ['label' => __('Order Comment (Guest)'),    'value' => 'sales_email_order_comment_guest_template'],
            ['label' => __('Shipment'),                 'value' => 'sales_email_shipment_template'],
            ['label' => __('Shipment (Guest)'),         'value' => 'sales_email_shipment_guest_template'],
            ['label' => __('Shipment Comment'),         'value' => 'sales_email_shipment_comment_template'],
            ['label' => __('Shipment Comment (Guest)'), 'value' => 'sales_email_shipment_comment_guest_template'],
        ];
    }
}
