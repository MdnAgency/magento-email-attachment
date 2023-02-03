<?php
/**
 * MailAttachments
 *
 * @copyright Copyright © 2023 Maison du Net. All rights reserved.
 * @author    vincent@maisondunet.com
 */

namespace Maisondunet\EmailAttachment\Block\System\Config\Form\Field;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Maisondunet\EmailAttachment\View\Element\Html\File as FileField;

class MailAttachments extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    const ATTACHMENT_NAME_COL = "name";
    const EMAIL_TEMPLATES_COL = "templates";
    const ATTACHMENT_FILE_COL = "file";

    /**
     * Grid columns
     *
     * @var array
     */
    protected $_columns = [];
    /**
     * Enable the "Add after" button or not
     *
     * @var bool
     */
    protected $_addAfter = true;
    /**
     * Label of add button
     *
     * @var string
     */
    protected $_addButtonLabel;
    private TemplateSelectColumn $emailTemplateRenderer;

    private FileField $fileRenderer;

    /**
     * Check if columns are defined, set template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            self::ATTACHMENT_NAME_COL,
            [
                'label' => __('Name'),
            ]
        );
        $this->addColumn(
            self::EMAIL_TEMPLATES_COL,
            [
                'label' => __('Templates'),
                'renderer' => $this->getEmailTemplateRenderer()
            ]
        );
        $this->addColumn(
            self::ATTACHMENT_FILE_COL,
            [
                'label' => __('File'),
                'renderer' => $this->getFileRenderer()
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @return TemplateSelectColumn
     * @throws LocalizedException
     */
    private function getEmailTemplateRenderer(): TemplateSelectColumn
    {
        if (!isset($this->emailTemplateRenderer)) {
            $this->emailTemplateRenderer = $this->getLayout()->createBlock(
                TemplateSelectColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->emailTemplateRenderer;
    }

    /**
     * @return FileField
     * @throws LocalizedException
     */
    private function getFileRenderer(): FileField
    {
        if (!isset($this->fileRenderer)) {
            $this->fileRenderer = $this->getLayout()->createBlock(
                FileField::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->fileRenderer;
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $templates = $row->getData(self::EMAIL_TEMPLATES_COL);
        if (is_array($templates) && count($templates) > 0) {
            foreach ($templates as $template) {
                $options['option_' . $this->getEmailTemplateRenderer()->calcOptionHash($template)]
                    = 'selected="selected"';
            }
        }

        $file = $row->getData(self::ATTACHMENT_FILE_COL);
        if ($file !== null) {
            $options['link'] = $this->getFileRenderer()->getFileUrl($file);
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName == self::ATTACHMENT_NAME_COL) {
            $this->_columns[$columnName]['class'] = 'input-text required-entry validate-data';
            $this->_columns[$columnName]['style'] = 'width:150px';
        }
        return parent::renderCellTemplate($columnName);
    }
}
