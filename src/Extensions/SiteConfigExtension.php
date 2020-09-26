<?php

namespace Broarm\EventTickets\Extensions;

use Page;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class SiteConfigExtension
 *
 * @package Broarm\EventTickets
 *
 * @property SiteConfig $owner
 * @property string SuccessMessage
 * @property string SuccessMessageMail
 *
 * @method Image TicketLogo()
 * @method Page TermsPage()
 */
class SiteConfigExtension extends DataExtension
{
    private static $db = array(
        'SuccessMessage' => 'HTMLText',
        'SuccessMessageMail' => 'HTMLText'
    );

    private static $has_one = array(
        'TicketLogo' => 'Image',
        'TermsPage' => 'SiteTree'
    );

    private static $defaults = array(
        'SuccessMessage' => "<p>Thanks for your order!<br/>The requested tickets are sent to you by mail.</p>",
        'SuccessMessageMail' => "<p>This is your ticket.<br/>You can scan the QR code at the ticket check.</p>"
    );

    public function updateCMSFields(FieldList $fields)
    {
        $termsPage = new TreeDropdownField('TermsPageID', 'Terms and conditions to mention on checkout', 'SiteTree');

        $success = new HtmlEditorField('SuccessMessage', 'Success message');
        $success->setRows(4);
        $success->setDescription(_t(
            'SiteConfigExtension.SUCCESS_MESSAGE_HELP',
            'This message is used on the success page. The text is overwriteble for a specific event under the "Tickets" tab'
        ));

        $mail = new HtmlEditorField('SuccessMessageMail', 'Mail message');
        $mail->setRows(4);
        $mail->setDescription(_t(
            'SiteConfigExtension.TICKET_MESSAGE_HELP',
            'This message is used on ticket. You can reference the therms and agreements here or explain the scan process'
        ));

        $uploadField = UploadField::create('TicketLogo', 'TicketLogo');
        $uploadField->setAllowedMaxFileNumber(1);
        $uploadField->setFolderName('event-tickets/ticket-logo');
        $uploadField->getValidator()->setAllowedExtensions(array('png', 'gif', 'jpg'));

        $fields->addFieldsToTab('Root.Tickets', array(
            $termsPage,
            $success,
            $mail,
            $uploadField
        ));

        return $fields;
    }
}
