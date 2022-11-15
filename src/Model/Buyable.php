<?php

namespace Broarm\EventTickets\Model;

use Broarm\EventTickets\Extensions\TicketExtension;
use Exception;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\CurrencyField;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\FieldType\DBField;

/**
 * Class Buyable
 *
 * @package Broarm\EventTickets
 *
 * @property string Title
 * @property float Price
 * @property int OrderMin
 * @property int OrderMax
 * @property string AvailableFromDate
 * @property string AvailableTillDate
 * @property NumericField AmountField the amount field is set on the TicketForm
 *
 * @method TicketExtension|SiteTree TicketPage()
 */
class Buyable extends DataObject
{
    private static $table_name = 'EventTickets_Buyable';

    /**
     * The default sale start date
     * This defaults to the event start date '-3 week'
     *
     * @var string
     */
    private static $sale_start_threshold = '-3 week';

    /**
     * The default sale end date
     * This defaults to the event start date time '-12 hours'
     *
     * @var string
     */
    private static $sale_end_threshold = '-12 hours';

    private static $db = [
        'Title' => 'Varchar',
        'Price' => 'Currency',
        'IsAvailable' => 'Boolean(1)',
        'AvailableFromDate' => 'DBDatetime',
        'AvailableTillDate' => 'DBDatetime',
        'OrderMin' => 'Int',
        'OrderMax' => 'Int',
        'Capacity' => 'Int',
        'Sort' => 'Int'
    ];

    private static $default_sort = 'Sort ASC, AvailableFromDate DESC';

    private static $has_one = [
        'TicketPage' => SiteTree::class
    ];

    private static $defaults = [
        'OrderMin' => 1,
        'OrderMax' => 5
    ];

    private static $summary_fields = [
        'Title' => 'Title',
        'Price.Nice' => 'Price',
        'AvailableFrom' => 'Available from',
        'AvailableTill' => 'Available till',
        'AvailableSummary' => 'Available'
    ];

    private static $searchable_fields = [
        'Title',
        'Price',
        'AvailableFromDate',
        'AvailableTillDate',
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['TicketPageID', 'Sort']);

        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', _t(__CLASS__ . '.TITLE_LABEL', 'Title for the ticket')),
            CurrencyField::create('Price', _t(__CLASS__ . '.PRICE_LABEL', 'Ticket price')),
            NumericField::create('Capacity', _t(__CLASS__ . '.Capacity', 'Amount of tickets available (from this type)')),
            $saleStart = DatetimeField::create('AvailableFromDate',
                _t(__CLASS__ . '.SALE_START_LABEL', 'Ticket sale starts from')),
            $saleEnd = DatetimeField::create('AvailableTillDate', _t(__CLASS__ . '.SALE_END_LABEL', 'Ticket sale ends on')),
            NumericField::create('OrderMin', _t(__CLASS__ . '.OrderMin', 'Order minimum'))
                ->setDescription(_t(__CLASS__ . '.OrderMinDescription', 'Minimum allowed amount of tickets from this type to be sold at once')),
            NumericField::create('OrderMax', _t(__CLASS__ . '.OrderMax', 'Order maximum'))
                ->setDescription(_t(__CLASS__ . '.OrderMaxDescription', 'Maximum allowed amount of tickets from this type to be sold at once'))
        ));

        //$saleStart->getDateField()->setConfig('showcalendar', true);
        if ($availableFrom = $this->getAvailableFrom()) {
            $saleStart->setDescription(_t(
                __CLASS__ . '.SALE_START_DESCRIPTION',
                'If no date is given the following date will be used: {date}', null,
                array('date' => $availableFrom ->Nice())
            ));
        }

        //$saleEnd->getDateField()->setConfig('showcalendar', true);
        if ($eventStart = $this->getEventStartDate()) {
            $saleEnd->setDescription(_t(
                __CLASS__ . '.SALE_END_DESCRIPTION',
                'If no date is given the event start date will be used: {date}', null,
                array('date' => $eventStart->Nice())
            ));
        }

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
     * @return DBDate|DBField|null
     * @throws Exception
     */
    public function getAvailableFrom()
    {
        if ($this->AvailableFromDate) {
            return $this->dbObject('AvailableFromDate');
        } elseif ($startDate = $this->getEventStartDate()) {
            $lastWeek = new DBDate();
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
     * @return DBDatetime|DBField|null
     * @throws Exception
     */
    public function getAvailableTill()
    {
        if ($this->AvailableTillDate) {
            return $this->dbObject('AvailableTillDate');
        } elseif ($startDate = $this->getEventStartDate()) {
            $till = strtotime(self::config()->get('sale_end_threshold'), strtotime($startDate->getValue()));
            $date = DBDatetime::create();
            $date->setValue(date('Y-m-d H:i:s', $till));
            return $date;
        }


        return null;
    }

    /**
     * Validate if the start and end date are in the past and the future
     *
     * @return bool
     * @throws Exception
     */
    public function validateDate()
    {
        if (
            ($from = $this->getAvailableFrom()) &&
            ($till = $this->getAvailableTill()) &&
            $from->InPast() &&
            $till->InFuture()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Implement on subclass
     *
     * @return bool
     */
    protected function validateAvailability()
    {
        if ($this->Capacity !== 0) {
            return $this->getAvailability() > 0;
        }
     
        return true;
    }

    /**
     * Get the ticket availability for this type
     * A buyable always checks own capacity before event capacity
     */
    public function getAvailability()
    {
        if ($this->Capacity !== 0) {
            $sold = OrderItem::get()->filter(['BuyableID' => $this->ID])->count();
            $available = $this->Capacity - $sold;
            return $available < 0 ? 0 : $available;
        }

        // fallback to page availability if capacity is not set
        return $this->TicketPage()->getAvailability();
    }

    /**
     * Return if the ticket is available or not
     *
     * @return bool
     * @throws Exception
     */
    public function getAvailable()
    {
        if (!$this->IsAvailable) {
            return false;
        }

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
     * @throws Exception
     */
    public function getAvailableSummary()
    {
        $available = $this->getAvailable()
            ? '<span style="color: #3adb76;">' . _t(__CLASS__ . '.Available', 'Tickets available') . '</span>'
            : '<span style="color: #cc4b37;">' . _t(__CLASS__ . '.Unavailable', 'Not for sale') . '</span>';

        return new LiteralField('Available', $available);
    }

    /**
     * Get the event start date
     *
     * @return DBDatetime|null
     * @throws Exception
     */
    private function getEventStartDate()
    {
        $startDate = $this->TicketPage()->getEventStartDate();
        $this->extend('updateEventStartDate', $startDate);
        return $startDate;
    }

    /**
     * A buyable doesn't create attendees
     * @see Ticket::createAttendees()
     */
    public function createAttendees($amount)
    {
        return [];
    }
}
