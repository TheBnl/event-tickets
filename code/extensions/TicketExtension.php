<?php
/**
 * TicketExtension.php
 *
 * @author Bram de Leeuw
 * Date: 09/03/17
 */

namespace Broarm\EventTickets;

use CalendarEvent_Controller;
use DataExtension;
use FieldList;
use GridField;
use GridFieldConfig_RecordEditor;
use HasManyList;
use HtmlEditorField;
use LiteralField;
use NumericField;
use SiteConfig;

/**
 * Class TicketExtension
 *
 * @package Broarm\EventTickets
 *
 * @property TicketExtension|\CalendarEvent $owner
 * @property int                            Capacity
 * @property int                            OrderMin
 * @property int                            OrderMax
 * @property string                         SuccessMessage
 * @property string                         SuccessMessageMail
 *
 * @method \HasManyList Tickets()
 * @method \HasManyList Reservations()
 * @method \HasManyList Attendees()
 * @method \HasManyList WaitingList()
 * @method \HasManyList Fields()
 */
class TicketExtension extends DataExtension
{
    /**
     * @var CalendarEvent_Controller
     */
    protected $controller;

    private static $db = array(
        'Capacity' => 'Int',
        'OrderMin' => 'Int',
        'OrderMax' => 'Int',
        'SuccessMessage' => 'HTMLText',
        'SuccessMessageMail' => 'HTMLText'
    );

    private static $has_many = array(
        'Tickets' => 'Broarm\EventTickets\Ticket.Event',
        'Reservations' => 'Broarm\EventTickets\Reservation.Event',
        'Attendees' => 'Broarm\EventTickets\Attendee.Event',
        'WaitingList' => 'Broarm\EventTickets\WaitingListRegistration.Event',
        'Fields' => 'Broarm\EventTickets\UserField.Event'
    );

    private static $defaults = array(
        'Capacity' => 50
    );

    private static $translate = array(
        'SuccessMessage',
        'SuccessMessageMail'
    );

    public function updateCMSFields(FieldList $fields)
    {
        $ticketLabel = _t('TicketExtension.Tickets', 'Tickets');
        $fields->addFieldsToTab(
            "Root.$ticketLabel", array(
            GridField::create('Tickets', $ticketLabel, $this->owner->Tickets(), TicketsGridFieldConfig::create($this->canCreateTickets())),
            NumericField::create('Capacity', _t('TicketExtension.Capacity', 'Capacity')),
            HtmlEditorField::create('SuccessMessage', _t('TicketExtension.SuccessMessage', 'Success message'))->setRows(4),
            HtmlEditorField::create('SuccessMessageMail', _t('TicketExtension.MailMessage', 'Mail message'))->setRows(4)
        ));

        // Create Reservations tab
        if ($this->owner->Reservations()->exists()) {
            $reservationLabel = _t('TicketExtension.Reservations', 'Reservations');
            $fields->addFieldToTab(
                "Root.$reservationLabel",
                GridField::create('Reservations', $reservationLabel, $this->owner->Reservations(), GridFieldConfig_RecordEditor::create())
            );
        }

        // Create Attendees tab
        if ($this->owner->Attendees()->exists()) {
            $guestListLabel = _t('TicketExtension.GuestList', 'GuestList');
            $fields->addFieldToTab(
                "Root.$guestListLabel",
                GridField::create('Attendees', $guestListLabel, $this->owner->Attendees(), GuestListGridFieldConfig::create($this->owner))
            );
        }

        // Create WaitingList tab
        if ($this->owner->WaitingList()->exists()) {
            $waitingListLabel = _t('TicketExtension.WaitingList', 'WaitingList');
            $fields->addFieldToTab(
                "Root.$waitingListLabel",
                GridField::create('WaitingList', $waitingListLabel, $this->owner->WaitingList(), GridFieldConfig_RecordEditor::create())
            );
        }

        // Create Fields tab
        $extraFieldsLabel = _t('TicketExtension.ExtraFields', 'Attendee fields');
        $fields->addFieldToTab(
            "Root.$extraFieldsLabel",
            GridField::create('ExtraFields', $extraFieldsLabel, $this->owner->Fields(), UserFieldsGridFieldConfig::create())
        );

        $this->owner->extend('updateTicketExtensionFields', $fields);
    }

    /**
     * Trigger actions after write
     */
    public function onAfterWrite()
    {
        $this->createDefaultFields();
        parent::onAfterWrite();
    }

    /**
     * Creates and sets up the default fields
     */
    public function createDefaultFields()
    {
        $fields = Attendee::config()->get('default_fields');
        if (!$this->owner->Fields()->exists()) {
            foreach ($fields as $fieldName => $config) {
                $field = UserField::createDefaultField($fieldName, $config);
                $this->owner->Fields()->add($field);
            }
        }
    }

    /**
     * Extend the page actions with an start check in action
     *
     * @param FieldList $actions
     */
    public function updateCMSActions(FieldList $actions)
    {
        $checkInButton = new LiteralField('StartCheckIn',
            "<a class='action ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only'
                id='Edit_StartCheckIn'
                role='button'
                href='{$this->owner->Link('checkin')}'
                target='_blank'>
                Start check in
            </a>"
        );

        if ($this->owner->Attendees()->exists()) {
            $actions->push($checkInButton);
        }
    }

    /**
     * Get the leftover capacity
     *
     * @return int
     */
    public function getAvailability()
    {
        return $this->owner->Capacity - $this->owner->getGuestList()->count();
    }

    /**
     * Check if the tickets are still available
     *
     * @return bool
     */
    public function getTicketsAvailable()
    {
        return $this->owner->getAvailability() > 0;
    }

    /**
     * Check if the tickets are sold out
     * @return bool
     */
    public function getTicketsSoldOut()
    {
        return $this->owner->getAvailability() <= 0;
    }

    /**
     * get The sale start date
     *
     * @return \SS_DateTime
     */
    public function getTicketSaleStartDate()
    {
        $saleStart = null;
        if (($tickets = $this->owner->Tickets())) {
            /** @var Ticket $ticket */
            foreach ($tickets as $ticket) {
                if (($date = $ticket->getAvailableFrom()) && strtotime($date) < strtotime($saleStart) || $saleStart === null) {
                    $saleStart = $date;
                }
            }
        }

        return $saleStart;
    }

    /**
     * Check if the event is expired, either by unavailable tickets or because the date has passed
     *
     * @return bool
     */
    public function getEventExpired()
    {
        $expired = false;
        if (($tickets = $this->owner->Tickets()) && $expired = $tickets->exists()) {
            /** @var Ticket $ticket */
            foreach ($tickets as $ticket) {
                $expired = (!$ticket->validateDate() && $expired);
            }
        }

        return $expired;
    }

    /**
     * Check if the ticket sale is still pending
     *
     * @return bool
     */
    public function getTicketSalePending()
    {
        return time() < strtotime($this->owner->getTicketSaleStartDate());
    }

    /**
     * Get only the attendees who are certain to attend
     * Also includes attendees without any reservation, these are manually added
     *
     * @return \ArrayList
     */
    public function getGuestList()
    {
        return $this->owner->Attendees()->filterByCallback(function(Attendee $attendee, HasManyList $list) {
            return $attendee->Reservation()->Status === 'PAID' || !$attendee->Reservation()->exists();
        });
    }

    /**
     * Get the success message
     *
     * @return mixed|string
     */
    public function getSuccessContent()
    {
        if (!empty($this->owner->SuccessMessage)) {
            return $this->owner->dbObject('SuccessMessage');
        } else {
            return SiteConfig::current_site_config()->dbObject('SuccessMessage');
        }
    }

    /**
     * Get the mail message
     *
     * @return mixed|string
     */
    public function getMailContent()
    {
        if (!empty($this->owner->SuccessMessageMail)) {
            return $this->owner->dbObject('SuccessMessageMail');
        } else {
            return SiteConfig::current_site_config()->dbObject('SuccessMessageMail');
        }
    }

    /**
     * Get the Ticket logo
     *
     * @return \Image
     */
    public function getMailLogo()
    {
        return SiteConfig::current_site_config()->TicketLogo();
    }

    /**
     * Check if the current event can have tickets
     *
     * @return bool
     */
    public function canCreateTickets()
    {
        $currentDate = $this->owner->getController()->CurrentDate();
        if ($currentDate && $currentDate->exists()) {
            return $currentDate->dbObject('StartDate')->InFuture();
        }

        return false;
    }

    /**
     * Get the calendar controller
     *
     * @return CalendarEvent_Controller
     */
    public function getController()
    {
        return $this->controller
            ? $this->controller
            : $this->controller = CalendarEvent_Controller::create($this->owner);
    }
}
