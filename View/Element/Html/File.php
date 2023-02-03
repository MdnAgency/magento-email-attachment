<?php
/**
 * File
 *
 * @copyright Copyright © 2023 Maison du Net. All rights reserved.
 * @author    vincent@maisondunet.com
 */

namespace Maisondunet\EmailAttachment\View\Element\Html;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Maisondunet\EmailAttachment\Model\Config\Backend\FileManager;

/**
 * @method getColumnName()
 * @method setName($value)
 * @method setId($value)
 * @method getName()
 */
class File extends AbstractBlock
{
    private UrlInterface $urlBuilder;

    public function __construct(
        Context $context,
        UrlInterface $urlBuilder,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Sets "name" for <input> element
     *
     * @param $value
     *
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <input> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_beforeToHtml()) {
            return '';
        }
        $columnName = $this->getColumnName();
        $inputId = $this->_getCellInputElementId('<%- _id %>', $columnName);
        $cellInputElementName = $this->_getCellInputElementName($columnName . '_orig');
        $cellInputFileName = $this->_getCellInputElementName($columnName . '_new');
        // Create hidden input for previously uploaded files
        $html = "<input id=\"$inputId\" type=\"hidden\" name=\"$cellInputElementName\" value=\"<%- $columnName %>\"/>";
        // Create file input
        $html .= "<input class='mdn_fileupload' id=\"{$inputId}_file\" name=\"$cellInputFileName\" type=\"file\" <% if (!option_extra_attrs.link) { %> required <% } %>/>";
        $label = __("Choose File");
        $html .= "<label for=\"{$inputId}_file\" class=\"action-default\">$label</label>";
        // Create link to download an eventual file
        $html .= "<% if (option_extra_attrs.link) { %><a href=\"<%- option_extra_attrs.link %>\" target=\"_blank\"><%- $columnName %></a><% } %>";

        return $html;
    }

    /**
     * Get name for cell element
     *
     * @param string $rowId
     * @param string $columnName
     * @return string
     */
    protected function _getCellInputElementId($rowId, $columnName)
    {
        return $rowId . '_' . $columnName;
    }

    /**
     * Get id for cell element
     *
     * @param string $columnName
     * @return string
     */
    protected function _getCellInputElementName($columnName)
    {
        return $this->getName() . '[' . $columnName . ']';
    }

    /**
     * Get file url
     *
     * @param $fileName
     *
     * @return string
     */
    public function getFileUrl($fileName): string
    {
        return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . FileManager::FILE_PATH . $fileName;
    }
}
