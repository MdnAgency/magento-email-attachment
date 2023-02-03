# Magento 2 Email Attachment module

This module Open-source module allow the administrator to attach Invoice, Shipment, Credit Memo and Custom PDF Files to sales email notification.
It has been design to be as simple, expendable and as unobtrusive as possible. 

It doesn't contains any bloatware or advertisment.

# Installation

To install the Magento 2 GTM Cookie consent, simply run the command below:

```bash
composer require maisondunet/module-email-attachment
```

To enable the module:

```bash
bin/magento module:enable Maisondunet_EmailAttachment
```

# Module configuration

Module configuration is located at :

Stores > Configuration > Sales > Sales Emails

## Invoice Options

| Field                               | Description                                          |
|-------------------------------------|------------------------------------------------------|
| Attach PDF Invoice                  | Attach a PDF invoice to invoice notification e-mails |

## Shipment Options

| Field                               | Description                                            |
|-------------------------------------|--------------------------------------------------------|
| Attach PDF Invoice                  | Attach a PDF Shipment to shipment notification e-mails |

## Credit Memo Options

| Field                  | Description                                                  |
|------------------------|--------------------------------------------------------------|
| Attach PDF Credit Memo | Attach a PDF Credit Memo to credit_memo notification e-mails |

## Email Attachments Options

This functionality allow the administrator to attach custom Files to sales-emails.
 

| Name            | Templates                                            | File              |
|-----------------|------------------------------------------------------|-------------------|
| Attachment Name | The file will be attached to selected template email | The file attached |


## Extensibility

Create a class to handle you custom attachment

```php
class CreditMemo implements AttachmentResolverInterface
{
    // --------
    
    /**
    * @param Template $template
    * @return MimePartInterface[]
     */
    public function getAttachments(Template $template): array{
        // Build your custom attachment there
    }
}
```

And register to service inside a di.xml

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Maisondunet\EmailAttachment\Model\AttachmentResolverPool">
        <arguments>
            <argument name="attachmentResolvers" xsi:type="array">
                <item name="credit_memo" xsi:type="object">Maisondunet\EmailAttachment\Model\AttachmentResolver\SystemConfiguration</item>
            </argument>
        </arguments>
    </type>
</config>
```
