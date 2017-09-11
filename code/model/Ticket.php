<?php
/**
 * Ticket.php
 *
 * @author Bram de Leeuw
 * Date: 09/03/17
 */

namespace Broarm\EventTickets;

use CalendarDateTime;
use CalendarEvent;
use CalendarEvent_Controller;
use DataObject;
use Date;
use DateField;
use FieldList;
use LiteralField;
use NumericField;
use SS_Datetime;
use Tab;
use TabSet;
use TextField;

/**
 * Class Ticket
 *
 * @package Broarm\EventTickets
 *
 * @property string       Title
 * @property float        Price
 * @property int          OrderMin
 * @property int          OrderMax
 * @property string       AvailableFromDate
 * @property string       AvailableTillDate
 * @property NumericField AmountField the amount field is set on the TicketForm
 *
 * @method CalendarEvent|TicketExtension Event
 */
class Ticket extends DataObject
{
    /**
     * The default sale start date
     * This defaults to the event start date '-1 week'
     *
     * @var string
     */
    private static $sale_start_threshold = '-1 week';

    /**
     * The default sale end date
     * This defaults to the event start date time '-12 hours'
     *
     * @var string
     */
    private static $sale_end_threshold = '-12 hours';

    private static $db = array(
        'Title' => 'Varchar(255)',
        'Price' => 'Currency',
        'AvailableFromDate' => 'Date',
        'AvailableTillDate' => 'Date',
        'OrderMin' => 'Int',
        'OrderMax' => 'Int',
        'Sort' => 'Int'
    );

    private static $default_sort = 'Sort ASC, AvailableFromDate DESC';

    private static $has_one = array(
        'Event' => 'CalendarEvent'
    );

    private static $defaults = array(
        'OrderMin' => 1,
        'OrderMax' => 5
    );

    private static $summary_fields = array(
        'Title' => 'Title',
        'Price.NiceDecimalPoint' => 'Price',
        'AvailableFrom' => 'Available from',
        'AvailableTill' => 'Available till',
        'AvailableSummary' => 'Available'
    );

    private static $translate = array(
        'Title'
    );

    public function getCMSFields()
    {
        $fields = new FieldList(new TabSet('Root', $mainTab = new Tab('Main')));

        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', _t('Ticket.TITLE_LABEL', 'Title for the ticket')),
            NumericField::create('Price', _t('Ticket.PRICE_LABEL', 'Ticket price')),
            $saleStart = DateField::create('AvailableFromDate',
                _t('Ticket.SALE_START_LABEL', 'Ticket sale starts from')),
            $saleEnd = DateField::create('AvailableTillDate', _t('Ticket.SALE_END_LABEL', 'Ticket sale ends on')),
            NumericField::create('OrderMin', _t('Ticket.OrderMin', 'Minimum allowed amount of tickets from this type')),
            NumericField::create('OrderMax', _t('Ticket.OrderMax', 'Maximum allowed amount of tickets from this type'))
        ));

        $saleStart->setConfig('showcalendar', true);
        $saleStart->setDescription(_t(
            'Ticket.SALE_START_DESCRIPTION',
            'If no date is given the following date will be used: {date}', null,
            array('date' => $this->getAvailableFrom()->Nice())
        ));

        $saleEnd->setConfig('showcalendar', true);
        $saleEnd->setDescription(_t(
            'Ticket.SALE_END_DESCRIPTION',
            'If no date is given the event start date will be used: {date}', null,
            array('date' => $this->getAvailableTill()->Nice())
        ));

        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    /**
     * Returns the singular name without the namespaces
     *
     * @return string
     */
    public function singular_name()
    {
        $name = explode('\\', parent::singular_name());
        return trim(end($name));
    }

    /**
     * Get the available form date if it is set,
     * otherwise get it from the parent
     *
     * @return \SS_DateTime|Date|\DBField|null
     */
    public function getAvailableFrom()
    {
        if ($this->AvailableFromDate) {
            return $this->dbObject('AvailableFromDate');
        } elseif ($startDate = $this->getEventStartDate()) {
            $lastWeek = new Date();
            $lastWeek->setValue(strtotime(self::config()->get('sale_start_threshold'), strtotime($startDate->value)));
            return $lastWeek;
        }

        return null;
    }

    /**
     * Get the available till date if it is set,
     * otherwise get it from the parent
     * Use the event start date as last sale possibility
     *
     * @return \SS_DateTime|Date|\DBField|null
     */
    public function getAvailableTill()
    {
        if ($this->AvailableTillDate) {
            return $this->dbObject('AvailableTillDate');
        } elseif ($startDate = $this->getEventStartDate()) {
            $till = strtotime(self::config()->get('sale_end_threshold'), strtotime($startDate->Nice()));
            $date = SS_Datetime::create();
            $date->setValue(date('Y-m-d H:i:s', $till));
            return $date;
        }

        return null;
    }

    /**
     * Validate if the start and end date are in the past and the future
     *
     * @return bool
     */
    public function validateDate()
    {
        if ($this->getAvailableFrom() && $this->getAvailableTill()) {
            if ($this->getAvailableFrom()->InPast() && $this->getAvailableTill()->InFuture()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate the available capacity
     *
     * @return bool
     */
    private function validateAvailability()
    {
        return $this->Event()->getAvailability() > 0;
    }

    /**
     * Return if the ticket is available or not
     *
     * @return bool
     */
    public function getAvailable()
    {
        if (!$this->getAvailableFrom() && !$this->getAvailableTill()) {
            return false;
        } elseif ($this->validateDate() && $this->validateAvailability()) {
            return true;
        }

        return false;
    }

    /**
     * Return availability for use in grid fields
     *
     * @return LiteralField
     */
    public function getAvailableSummary()
    {
        $available = $this->getAvailable()
            ? '<span style="color: #3adb76;">' . _t('Ticket.AVAILABLE', 'Tickets available') . '</span>'
            : '<span style="color: #cc4b37;">' . _t('Ticket.UNAVAILABLE', 'Not for sale') . '</span>';

        return new LiteralField('Available', $available);
    }

    /**
     * Get the event start date
     *
     * @return Date
     */
    private function getEventStartDate()
    {
        $currentDate = $this->Event()->getController()->CurrentDate();
        if ($currentDate && $currentDate->exists()) {
            $date = $currentDate->obj('StartDate')->Format('Y-m-d');
            $time = $currentDate->obj('StartTime')->Format('H:i:s');
            $dateTime = SS_Datetime::create();
            $dateTime->setValue("$date $time");
            return $dateTime;
        } else {
            return null;
        }
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
