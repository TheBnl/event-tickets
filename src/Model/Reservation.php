<?php

namespace Broarm\EventTickets\Model;

use Broarm\EventTickets\Extensions\TicketExtension;
use Exception;
use LeKoala\CmsActions\CustomAction;
use LeKoala\CmsActions\CustomLink;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use SilverStripe\Assets\FileNameFilter;
use SilverStripe\Assets\Folder;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Omnipay\Extensions\Payable;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\ValidationException;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\SSViewer;

/**
 * Class Reservation
 *
 * @package Broarm\EventTickets
 *
 * @property string Status
 * @property string Title
 * @property float Subtotal
 * @property float Total
 * @property string Comments
 * @property string ReservationCode
 * @property string Gateway
 * @property boolean SentTickets
 * @property boolean SentReservation
 * @property boolean SentNotification
 *
 * @property int TicketPageID
 * @property int MainContactID
 *
 * @method TicketExtension|SiteTree TicketPage()
 * @method Attendee MainContact()
 * @method HasManyList Payments()
 * @method HasManyList Attendees()
 * @method ManyManyList PriceModifiers()
 */
class Reservation extends DataObject
{
    private static $table_name = 'EventTickets_Reservation';

    const STATUS_CART = 'CART';
    const STATUS_PENDING = 'PENDING';
    const STATUS_PAID = 'PAID';
    const STATUS_CANCELED = 'CANCELED';

    /**
     * Time to wait before deleting the discarded cart
     * Give a string that is parsable by strtotime
     *
     * @var string
     */
    private static $delete_after = '+1 hour';

    /**
     * The address to whom the ticket notifications are sent
     * By default the admin email is used
     *
     * @config
     * @var string
     */
    private static $mail_sender;

    /**
     * The address from where the ticket mails are sent
     * By default the admin email is used
     *
     * @config
     * @var string
     */
    private static $mail_receiver;

    /**
     * Send the receipt mail
     * For organisations that only do free events you can configure
     * this to hold back the receipt and only send the tickets
     *
     * @config
     * @var bool
     */
    private static $send_receipt_mail = true;

    /**
     * Send the admin notification
     *
     * @config
     * @var bool
     */
    private static $send_admin_notification = true;

    private static $db = array(
        'Status' => 'Enum("CART,PENDING,PAID,CANCELED","CART")',
        'Title' => 'Varchar',
        'Subtotal' => 'Currency',
        'Total' => 'Currency',
        'Gateway' => 'Varchar',
        'Comments' => 'Text',
        'AgreeToTermsAndConditions' => 'Boolean',
        'ReservationCode' => 'Varchar',
        'SentReservation' => 'Boolean',
        'SentNotification' => 'Boolean',
    );

    private static $default_sort = 'Created DESC';

    private static $has_one = array(
        'TicketPage' => SiteTree::class,
        'MainContact' => Attendee::class
    );

    private static $has_many = array(
        'Attendees' => Attendee::class . '.Reservation',
        'OrderItems' => OrderItem::class . '.Reservation'
    );

    private static $extensions = [
        Payable::class
    ];

    private static $belongs_many_many = array(
        'PriceModifiers' => PriceModifier::class
    );

    private static $indexes = array(
        'ReservationCode' => [
            'type' => 'unique',
            'columns' => ['ReservationCode']
        ]
    );

    private static $summary_fields = array(
        'ReservationCode' => 'Reservation',
        'Title' => 'Customer',
        'Total.Nice' => 'Total',
        'State' => 'Status',
        'GatewayNice' => 'Payment method',
        'Created.Nice' => 'Date'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['Attendees', 'OrderItems', 'Payments', 'PriceModifiers', 'Subtotal', 'Gateway', 'SentTickets', 'SentReservation', 'SentNotification', 'TicketPageID']);
        $gridFieldConfig = GridFieldConfig_RecordViewer::create();
        $fields->addFieldsToTab('Root.Main', array(
            ReadonlyField::create('ReservationCode', _t(__CLASS__ . '.Code', 'Code')),
            ReadonlyField::create('Created', _t(__CLASS__ . '.Created', 'Date')),
            DropdownField::create('Status', _t(__CLASS__ . '.Status', 'Status'), $this->getStates()),
            ReadonlyField::create('Title', _t(__CLASS__ . '.MainContact', 'Main contact')),
            ReadonlyField::create('GateWayNice', _t(__CLASS__ . '.Gateway', 'Gateway')),
            ReadonlyField::create('Total', _t(__CLASS__ . '.Total', 'Total')),
            ReadonlyField::create('Comments', _t(__CLASS__ . '.Comments', 'Comments')),
            CheckboxField::create('AgreeToTermsAndConditions', _t(__CLASS__ . '.AgreeToTermsAndConditions', 'Agreed to terms and conditions'))->performReadonlyTransformation(),
            GridField::create('Attendees', 'Attendees', $this->Attendees(), $gridFieldConfig),
            GridField::create('OrderItems', 'OrderItems', $this->OrderItems(), $gridFieldConfig),
            GridField::create('Payments', 'Payments', $this->Payments(), $gridFieldConfig),
            GridField::create('PriceModifiers', 'PriceModifiers', $this->PriceModifiers(), $gridFieldConfig)
        ));

        return $fields;
    }

    public function getCMSActions()
    {
        $actions = parent::getCMSActions();

        // Send ticket action
        $actions->push($sendTicket = new CustomAction('sendReservation', _t(__CLASS__ . '.sendReservation', 'Send reservation')));
        $sendTicket->setButtonType('outline-secondary');
        $sendTicket->setButtonIcon('p-mail');

        // Download ticket action
        $actions->push($downloadTicket = new CustomLink('downloadTickets', _t(__CLASS__ . '.DownloadTickets', 'Download tickets')));
        $downloadTicket->setButtonType('outline-secondary');
        $downloadTicket->setButtonIcon('p-download');
        $downloadTicket->setNewWindow(true);

        return $actions;
    }

    /**
     * Generate a reservation code if it does not yet exists
     */
    public function onBeforeWrite()
    {
        // Set the title to the name of the reservation holder
        $this->Title = $this->getName();

        // Create a validation code to be used for confirmation and in the barcode
        if ($this->exists() && empty($this->ReservationCode)) {
            $this->ReservationCode = $this->createReservationCode();
        }

        parent::onBeforeWrite();
    }

    /**
     * After deleting a reservation, delete the attendees and files
     */
    public function onBeforeDelete()
    {
        // If a reservation is deleted remove the names from the guest list
        foreach ($this->Attendees() as $attendee) {
            /** @var Attendee $attendee */
            if ($attendee->exists()) {
                $attendee->delete();
            }
        }

        // If a reservation is deleted remove order items
        foreach ($this->OrderItems() as $orderItem) {
            /** @var OrderItem $attendee */
            if ($orderItem->exists()) {
                $orderItem->delete();
            }
        }

        // Remove the folder
        if (($folder = Folder::get()->find('Name', $this->ReservationCode)) && $folder->exists() && $folder->isEmpty()) {
            $folder->delete();
        }

        parent::onBeforeDelete();
    }

    /**
     * Gets a nice unnamespaced name
     *
     * @return string
     */
    public function singular_name()
    {
        $name = explode('\\', parent::singular_name());
        return trim(end($name));
    }

    /**
     * Returns the nice gateway title
     *
     * @return string
     */
    public function getGatewayNice()
    {
        return GatewayInfo::niceTitle($this->Gateway);
    }

    /**
     * Check if the cart is still in cart state and the delete_after time period has been exceeded
     *
     * @return bool
     */
    public function isDiscarded()
    {
        $deleteAfter = strtotime(self::config()->get('delete_after'), strtotime($this->Created));
        return ($this->Status === 'CART') && (time() > $deleteAfter);
    }

    /**
     * Get the full name
     *
     * @return string
     */
    public function getName()
    {
        /** @var Attendee $attendee */
        if (!($mainContact = $this->MainContact()) || !$mainContact->exists() || !($name = $mainContact->getName())) {
            $name = _t(__CLASS__ . '.NewReservation', 'new reservation');
        }

        $this->extend('updateName', $name);

        return $name;
    }

    /**
     * Return the translated state
     *
     * @return string
     */
    public function getState()
    {
        if ($this->exists()) {
            return _t(__CLASS__ . ".{$this->Status}", $this->Status);
        }

        return null;
    }

    /**
     * Get a the translated map of available states
     *
     * @return array
     */
    public function getStates()
    {
        return array_map(function ($state) {
            return _t(__CLASS__ . ".$state", $state);
        }, $this->dbObject('Status')->enumValues());
    }

    /**
     * Get the total by querying the sum of attendee ticket prices
     *
     * @return float
     */
    public function calculateTotal()
    {
        // $ticket = DataObject::getSchema()->tableName(Ticket::class);
        // $attendee = DataObject::getSchema()->tableName(Attendee::class);

        // TODO change to order item sum price
        $total = $this->Subtotal = $this->OrderItems()->sum('Total');
        // $total = $this->Subtotal = $this->Attendees()->leftJoin(
        //     $ticket,
        //     "`$attendee`.`TicketID` = `$ticket`.`ID`"
        // )->sum('Price');

        // Calculate any price modifications if added
        if ($this->PriceModifiers()->exists()) {
            foreach ($this->PriceModifiers() as $priceModifier) {
                $priceModifier->updateTotal($total, $this);
            }
        }

        return $this->Total = $total;
    }

    /**
     * Safely change to a state
     * todo check if state direction matches
     *
     * @param $state
     *
     * @return boolean
     */
    public function changeState($state)
    {
        $availableStates = $this->dbObject('Status')->enumValues();
        if (in_array($state, $availableStates)) {
            $this->Status = $state;
            return true;
        } else {
            user_error(_t(__CLASS__ . '.STATE_CHANGE_ERROR', 'Selected state is not available'));
            return false;
        }
    }

    /**
     * Complete the reservation
     *
     * @throws ValidationException
     */
    public function complete()
    {
        $this->changeState('PAID');
        $this->send();
        $this->write();
        $this->extend('onAfterComplete');
    }

    /**
     * Set the main contact id
     * @param $id
     *
     * @throws ValidationException
     */
    public function setMainContact($id)
    {
        $this->MainContactID = $id;
        $this->write();
    }

    /**
     * Create a reservation code
     *
     * @return string
     */
    public function createReservationCode()
    {
        return uniqid($this->ID);
    }

    /**
     * Creates a printable ticket for the attendee
     *
     * @return Mpdf
     * @throws \Mpdf\MpdfException
     */
    public function createTicketFile()
    {
        // Set the template and parse the data
        $html = SSViewer::execute_template('Broarm\\EventTickets\\ReservationPrintable', $this);
        $pdf = new Mpdf();
        $pdf->WriteHTML($html);
        return $pdf;
    }

    /**
     * Send the reservation mail
     *
     * @return bool
     * @throws \Mpdf\MpdfException
     */
    public function sendReservation()
    {
        if (!self::config()->get('send_receipt_mail')) {
            return true;
        }

        // Get the mail sender or fallback to the admin email
        if (!($from = self::config()->get('mail_sender')) || empty($from)) {
            $from = Email::config()->get('admin_email');
        }

        $eventName = SiteConfig::current_site_config()->getTitle();
        if (($event = $this->TicketPage()) && $event->hasMethod('getEventTitle')) {
            $eventName = $event->getEventTitle();
        }

        // Create the email with given template and reservation data
        $email = new Email();
        $email->setSubject(_t(
            __CLASS__ .'.ReservationSubject',
            'Your tickets for {event}',
            null,
            array(
                'event' => $eventName
            )
        ));
        $email->setFrom($from);
        $email->setTo($this->MainContact()->getEmail());
        $email->setHTMLTemplate('Broarm\\EventTickets\\ReservationEmail');

        $pdf = $this->createTicketFile();
        $fileName = FileNameFilter::create()->filter("Tickets {$eventName}.pdf");
        $email->addAttachmentFromData($pdf->Output($fileName, Destination::STRING_RETURN), $fileName, 'application/pdf');

        $email->setData($this);
        $this->extend('updateReservationMail', $email);
        return $email->send();
    }

    /**
     * Send a booking notification to the ticket mail sender or the site admin
     *
     * @return bool
     * @throws Exception
     */
    public function sendNotification()
    {
        if (!self::config()->get('send_admin_notification')) {
            return true;
        }

        if (!($from = self::config()->get('mail_sender')) || empty($from)) {
            $from = Email::config()->get('admin_email');
        }

        if (!($to = self::config()->get('mail_receiver')) || empty($to)) {
            $to = Email::config()->get('admin_email');
        }

        $eventName = SiteConfig::current_site_config()->getTitle();
        if (($event = $this->TicketPage()) && $event->hasMethod('getEventTitle')) {
            $eventName = $event->getEventTitle();
        }

        $email = new Email();
        $email->setSubject(_t(
            __CLASS__ . '.NotificationSubject',
            'New reservation for {event} by {name}',
            null, [
                'event' => $eventName,
                'name' => $this->getName()
            ]
        ));

        $email->setFrom($from);
        $email->setTo($to);
        $email->setHTMLTemplate('Broarm\\EventTickets\\NotificationMail');
        $email->setData($this);
        $this->extend('updateNotificationMail', $email);
        return $email->send();
    }

    /**
     * Send the reservation and notification
     * @throws Exception
     */
    public function send()
    {
        $this->extend('onBeforeSend');
        $this->SentReservation = (boolean)$this->sendReservation();
        $this->SentNotification = (boolean)$this->sendNotification();
    }

    public function downloadTickets()
    {
        $eventName = $this->TicketPage()->getTitle();
        $pdf = $this->createTicketFile();
        $fileName = FileNameFilter::create()->filter("Tickets {$eventName}.pdf");
        return $pdf->Output($fileName, Destination::INLINE);
    }

    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    public function canDelete($member = null)
    {
        return $this->exists() && $this->Status !== self::STATUS_PAID;
    }
}
