<?php

namespace Broarm\EventTickets\Extensions;

use Page;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\SiteTree;
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
 * @property string PrintedTicketContent
 *
 * @method Image TicketLogo()
 * @method SiteTree TermsPage()
 */
class SiteConfigExtension extends DataExtension
{
    private static $db = array(
        'SuccessMessage' => 'HTMLText',
        'SuccessMessageMail' => 'HTMLText',
        'PrintedTicketContent' => 'HTMLText'
    );

    private static $has_one = array(
        'TicketLogo' => Image::class,
        'TermsPage' => SiteTree::class
    );

    private static $owns = [
        'TicketLogo'
    ];

    private static $defaults = array(
        'SuccessMessage' => "<p>Thanks for your order!<br/>The requested tickets are sent to you by mail.</p>",
        'SuccessMessageMail' => "<p>This is your ticket.<br/>You can scan the QR code at the ticket check.</p>"
    );

    public function updateCMSFields(FieldList $fields)
    {
        $termsPage = new TreeDropdownField('TermsPageID', 'Terms and conditions to mention on checkout', SiteTree::class);

        $success = HtmlEditorField::create('SuccessMessage', 'Success message')
            ->addExtraClass('stacked')
            ->setRows(4)
            ->setDescription(_t(
                __CLASS__ . '.SuccessMessageHelp',
                'This message is used on the success page. The text is over-writable for a specific event under the "Tickets" tab'
            ));

        $mail = HtmlEditorField::create('SuccessMessageMail', 'Mail message')
            ->addExtraClass('stacked')
            ->setRows(4)
            ->setDescription(_t(
                __CLASS__ . '.SuccessMessageMailHelp',
                'This message is used in the ticket email. The text is over-writable for a specific event under the "Tickets" tab'
            ));

        $printedTicket = HtmlEditorField::create('PrintedTicketContent', 'Ticket description')
            ->addExtraClass('stacked')
            ->setRows(4)
            ->setDescription(_t(
                __CLASS__ . '.PrintedTicketContentHelp',
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
            $uploadField,
            $printedTicket
        ));

        return $fields;
    }
}
