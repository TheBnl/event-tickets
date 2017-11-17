<?php
/**
 * Reservation.php
 *
 * @author Bram de Leeuw
 * Date: 09/03/17
 */

namespace Broarm\EventTickets;

use BetterButtonCustomAction;
use CalendarEvent;
use CheckboxField;
use Config;
use DataObject;
use DropdownField;
use Email;
use FieldList;
use Folder;
use GridField;
use GridFieldConfig_RecordViewer;
use HasManyList;
use ManyManyList;
use ReadonlyField;
use SilverStripe\Omnipay\GatewayInfo;
use SiteConfig;
use Tab;
use TabSet;

/**
 * Class Reservation
 *
 * @package Broarm\EventTickets
 *
 * @property string Status
 * @property string Title
 * @property float  Subtotal
 * @property float  Total
 * @property string Comments
 * @property string ReservationCode
 * @property string Gateway
 *
 * @property int    EventID
 * @property int    MainContactID
 *
 * @method CalendarEvent|TicketExtension Event()
 * @method Attendee MainContact()
 * @method HasManyList Payments()
 * @method HasManyList Attendees()
 * @method ManyManyList PriceModifiers()
 */
class Reservation extends DataObject
{
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

    private static $db = array(
        'Status' => 'Enum("CART,PENDING,PAID,CANCELED","CART")',
        'Title' => 'Varchar(255)',
        'Subtotal' => 'Currency',
        'Total' => 'Currency',
        'Gateway' => 'Varchar(255)',
        'Comments' => 'Text',
        'AgreeToTermsAndConditions' => 'Boolean',
        'ReservationCode' => 'Varchar(255)'
    );

    private static $default_sort = 'Created DESC';

    private static $has_one = array(
        'Event' => 'CalendarEvent',
        'MainContact' => 'Broarm\EventTickets\Attendee'
    );

    private static $has_many = array(
        'Payments' => 'Payment',
        'Attendees' => 'Broarm\EventTickets\Attendee.Reservation'
    );

    private static $belongs_many_many = array(
        'PriceModifiers' => 'Broarm\EventTickets\PriceModifier'
    );

    private static $indexes = array(
        'ReservationCode' => 'unique("ReservationCode")'
    );

    private static $summary_fields = array(
        'ReservationCode' => 'Reservation',
        'Title' => 'Customer',
        'Total.Nice' => 'Total',
        'State' => 'Status',
        'GatewayNice' => 'Payment method',
        'Created.Nice' => 'Date'
    );

    /**
     * Actions usable on the cms detail view
     *
     * @var array
     */
    private static $better_buttons_actions = array(
        'send'
    );

    public function getCMSFields()
    {
        $fields = new FieldList(new TabSet('Root', $mainTab = new Tab('Main')));
        $gridFieldConfig = GridFieldConfig_RecordViewer::create();
        $fields->addFieldsToTab('Root.Main', array(
            ReadonlyField::create('ReservationCode', _t('Reservation.Code', 'Code')),
            ReadonlyField::create('Created', _t('Reservation.Created', 'Date')),
            DropdownField::create('Status', _t('Reservation.Status', 'Status'), $this->getStates()),
            ReadonlyField::create('Title', _t('Reservation.MainContact', 'Main contact')),
            ReadonlyField::create('GateWayNice', _t('Reservation.Gateway', 'Gateway')),
            ReadonlyField::create('Total', _t('Reservation.Total', 'Total')),
            ReadonlyField::create('Comments', _t('Reservation.Comments', 'Comments')),
            CheckboxField::create('AgreeToTermsAndConditions', _t('Reservation.AgreeToTermsAndConditions', 'Agreed to terms and conditions'))->performReadonlyTransformation(),
            GridField::create('Attendees', 'Attendees', $this->Attendees(), $gridFieldConfig),
            GridField::create('Payments', 'Payments', $this->Payments(), $gridFieldConfig)
        ));
        $fields->addFieldsToTab('Root.Main', array());
        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    /**
     * Add utility actions to the reservation details view
     *
     * @return FieldList
     */
    public function getBetterButtonsActions()
    {
        /** @var FieldList $fields */
        $fields = parent::getBetterButtonsActions();
        $fields->push(BetterButtonCustomAction::create('send', _t('Reservation.RESEND', 'Resend the reservation')));

        return $fields;
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
        if (($mainContact = $this->MainContact()) && $mainContact->exists() && $name = $mainContact->getName()) {
            return $name;
        } else {
            return 'new reservation';
        }
    }

    /**
     * Return the translated state
     *
     * @return string
     */
    public function getState()
    {
        return _t("Reservation.{$this->Status}", $this->Status);
    }

    /**
     * Get a the translated map of available states
     *
     * @return array
     */
    private function getStates()
    {
        return array_map(function ($state) {
            return _t("Reservation.$state", $state);
        }, $this->dbObject('Status')->enumValues());
    }

    /**
     * Get the total by querying the sum of attendee ticket prices
     *
     * @return float
     */
    public function calculateTotal()
    {
        $total = $this->Subtotal = $this->Attendees()->leftJoin(
            'Broarm\EventTickets\Ticket',
            '`Broarm\EventTickets\Attendee`.`TicketID` = `Broarm\EventTickets\Ticket`.`ID`'
        )->sum('Price');

        // Calculate any price modifications if added
        if ($this->PriceModifiers()->exists()) {
            foreach ($this->PriceModifiers() as $priceModifier) {
                $priceModifier->updateTotal($total);
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
            user_error(_t('Reservation.STATE_CHANGE_ERROR', 'Selected state is not available'));
            return false;
        }
    }

    /**
     * Set the main contact id
     *
     * @param $id
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
     * Create the folder for the qr code and ticket file
     *
     * @return Folder|DataObject|null
     */
    public function fileFolder()
    {
        return Folder::find_or_make("/event-tickets/{$this->ReservationCode}/");
    }

    /**
     * Generate the qr codes and downloadable pdf
     */
    public function createFiles()
    {
        $folder = $this->fileFolder();
        /** @var Attendee $attendee */
        foreach ($this->Attendees() as $attendee) {
            $attendee->createQRCode($folder);
            $attendee->createTicketFile($folder);
        }
    }

    /**
     * Send the reservation mail
     */
    public function sendReservation()
    {
        // Get the mail sender or fallback to the admin email
        if (empty($from = self::config()->get('mail_sender'))) {
            $from = Config::inst()->get('Email', 'admin_email');
        }

        // Create the email with given template and reservation data
        $email = new Email();
        $email->setSubject(_t(
            'ReservationMail.TITLE',
            'Your order at {sitename}',
            null,
            array(
                'sitename' => SiteConfig::current_site_config()->Title
            )
        ));
        $email->setFrom($from);
        $email->setTo($this->MainContact()->Email);
        $email->setTemplate('ReservationMail');
        $email->populateTemplate($this);
        $this->extend('updateReservationMail', $email);
        $email->send();
    }

    /**
     * Send the reserved tickets
     */
    public function sendTickets()
    {
        // Get the mail sender or fallback to the admin email
        if (empty($from = self::config()->get('mail_sender'))) {
            $from = Config::inst()->get('Email', 'admin_email');
        }

        // Send the tickets to the main contact
        $email = new Email();
        $email->setSubject(_t(
            'MainContactMail.TITLE',
            'Uw tickets voor {event}',
            null,
            array(
                'event' => $this->Event()->Title
            )
        ));
        $email->setFrom($from);
        $email->setTo($this->MainContact()->Email);
        $email->setTemplate('MainContactMail');
        $email->populateTemplate($this);
        $this->extend('updateMainContactMail', $email);
        $email->send();


        // Get the attendees for this event that are checked as receiver
        $ticketReceivers = $this->Attendees()->filter('TicketReceiver', 1)->exclude('ID', $this->MainContactID);
        if ($ticketReceivers->exists()) {
            /** @var Attendee $ticketReceiver */
            foreach ($ticketReceivers as $ticketReceiver) {
                $email = new Email();
                $email->setSubject(_t(
                    'AttendeeMail.TITLE',
                    'Your ticket for {event}',
                    null,
                    array(
                        'event' => $this->Event()->Title
                    )
                ));
                $email->setFrom($from);
                $email->setTo($ticketReceiver->Email);
                $email->setTemplate('AttendeeMail');
                $email->populateTemplate($ticketReceiver);
                $this->extend('updateTicketMail', $email);
                $email->send();
            }
        }
    }


    /**
     * Send a booking notification to the ticket mail sender or the site admin
     */
    public function sendNotification()
    {
        if (empty($from = self::config()->get('mail_sender'))) {
            $from = Config::inst()->get('Email', 'admin_email');
        }

        if (empty($to = self::config()->get('mail_receiver'))) {
            $to = Config::inst()->get('Email', 'admin_email');
        }

        $email = new Email();
        $email->setSubject(_t(
            'NotificationMail.TITLE',
            'Nieuwe reservering voor {event}',
            null, array('event' => $this->Event()->Title)
        ));

        $email->setFrom($from);
        $email->setTo($to);
        $email->setTemplate('NotificationMail');
        $email->populateTemplate($this);
        $this->extend('updateNotificationMail', $email);
        $email->send();
    }

    /**
     * Create the files and send the reservation, notification and tickets
     */
    public function send()
    {
        $this->createFiles();
        $this->sendReservation();
        $this->sendNotification();
        $this->sendTickets();
    }

    /**
     * Get the download link
     *
     * @return string
     */
    public function getDownloadLink()
    {
        return $this->reservation->Attendees()->first()->TicketFile()->Link();
    }

    public function canView($member = null)
    {
        return $this->Event()->canView($member);
    }

    public function canEdit($member = null)
    {
        return $this->Event()->canEdit($member);
    }

    public function canDelete($member = null)
    {
        return $this->Event()->canDelete($member);
    }

    public function canCreate($member = null)
    {
        return $this->Event()->canCreate($member);
    }
}
