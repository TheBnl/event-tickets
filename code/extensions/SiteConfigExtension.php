<?php
/**
 * SiteConfigExtension.php
 *
 * @author Bram de Leeuw
 * Date: 09/03/17
 */

namespace Broarm\EventTickets;

use DataExtension;
use FieldList;
use HtmlEditorField;
use SiteConfig;
use UploadField;

/**
 * Class SiteConfigExtension
 *
 * @package Broarm\EventTickets
 *
 * @property SiteConfigExtension|\SiteConfig $owner
 * @property string SuccessMessage
 * @property string SuccessMessageMail
 *
 * @method \Image TicketLogo
 */
class SiteConfigExtension extends DataExtension
{
    private static $db = array(
        'SuccessMessage' => 'HTMLText',
        'SuccessMessageMail' => 'HTMLText'
    );

    private static $has_one = array(
        'TicketLogo' => 'Image'
    );

    private static $defaults = array(
        'SuccessMessage' => "<p>Thanks for your order!<br/>The requested tickets are sent to you by mail.</p>",
        'SuccessMessageMail' => "<p>Here are your tickets!<br/>You can scan the QR code at the ticket check, this is possible from your screen. If you prefer a paper ticket you can use the pint button to download your PDF.</p>"
    );

    public function updateCMSFields(FieldList $fields)
    {
        $success = new HtmlEditorField('SuccessMessage', 'Success message');
        $success->setRows(4);
        $success->setDescription(_t(
            'SiteConfigExtension.SUCCESS_MESSAGE_HELP',
            'This message is used on the success page. The text is overwriteble for a specific event under the "Tickets" tab'
        ));

        $mail = new HtmlEditorField('SuccessMessageMail', 'Mail message');
        $mail->setRows(4);
        $mail->setDescription(_t(
            'SiteConfigExtension.MAIL_MESSAGE_HELP',
            'This message is used on ticket and in the mail. The text is overwriteble for a specific event under the "Tickets" tab'
        ));

        $uploadField = UploadField::create('TicketLogo', 'TicketLogo');
        $uploadField->setAllowedMaxFileNumber(1);
        $uploadField->setFolderName('event-tickets/ticket-logo');
        $uploadField->getValidator()->setAllowedExtensions(array('png', 'gif', 'jpg'));

        $fields->addFieldsToTab('Root.Tickets', array(
            $success,
            $mail,
            $uploadField
        ));

        return $fields;
    }

    /**
     * Fixme: doesn't work
     * /
    public function populateDefaults()
    {
        if (empty($this->owner->SuccessMessage)) {
            $this->owner->SuccessMessage = SiteConfig::config()->get('defaults')['SuccessMessage'];
        }

        if (empty($this->owner->SuccessMessageMail)) {
            $this->owner->SuccessMessageMail = SiteConfig::config()->get('defaults')['SuccessMessageMail'];
        }

        parent::populateDefaults();
    } //*/
}
