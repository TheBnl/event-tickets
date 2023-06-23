<?php

namespace Broarm\EventTickets\Reports;

use Broarm\EventTickets\Model\Attendee;
use Broarm\EventTickets\Model\Reservation;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\Reports\Report;

class GuestListReport extends Report
{
    public function title()
    {
        return _t(__CLASS__ . '.Title', 'Gastenlijst');
    }

    public function sourceRecords($params, $sort, $limit)
    {
        $reservation = Reservation::singleton()->baseTable();
        $attendee = Attendee::singleton()->baseTable();
        
        $ticketStatus = Attendee::STATUS_ACTIVE;
        if (isset($params['TicketStatus'])) {
            $ticketStatus = $params['TicketStatus'];
        }

        $reservationStatus = Reservation::STATUS_PAID;
        if (isset($params['ReservationStatus'])) {
            $reservationStatus = $params['ReservationStatus'];
        }

        $attendees = Attendee::get()
            ->leftJoin($reservation, "`$attendee`.`ReservationID` = `$reservation`.`ID`")
            ->filter([
                'TicketStatus' => $ticketStatus,
            ])
            ->filterAny([
                'ReservationID' => 0,
                'Status' => $reservationStatus
            ]);

        if (isset($params['TicketPage'])) {
            $attendees = $attendees->filter(['TicketPageID' => $params['TicketPage']]);
        }

        $from = null;
        $till = null;
        if (isset($params['DefinedPeriod'])) {
            $definedPeriod = $params['DefinedPeriod'];
            switch($definedPeriod) {
                case 'Day':
                    $from = date('Y-m-d');
                    $till = date('Y-m-d');
                    break;
                case 'Week':
                    $from = date('Y-m-d', strtotime('-1 week'));
                    $till = date('Y-m-d');
                    break;
                case 'Month':
                    $from = date('Y-m') . '-01';
                    $till = date('Y-m-t');
                    break;
                case 'Year':
                    $from = date('Y') . '-01-01';
                    $till = date('Y') . '-12-31';
                    break;
                default:
                case 'Other':
                    break;
            }
        }

        if (isset($params['CustomPeriodFrom'])) {
            $from = $params['CustomPeriodFrom'];
        }

        if (isset($params['CustomPeriodTill'])) {
            $till = $params['CustomPeriodTill'];
        }

        if ($from && $till) {
            $ticketPage = $this->getTicketPages();
            $eventDates = $ticketPage->map('ID', 'EventStartDate')->toArray();
            $events = [];
            foreach ($eventDates as $id => $startDate) {
                if (!$startDate) continue;
                /** @var DBDate $startDate */
                $startTime = $startDate->getTimestamp();
                
                if (
                    ($fromTime = strtotime($from)) && $fromTime <= $startTime &&
                    ($tillTime = strtotime($till)) && $tillTime >= $startTime
                ) {
                    $events[] = $id;
                }
            }

            if (count($events)) {
                $attendees = $attendees->filter(['TicketPageID' => $events]);
            } else {
                $attendees = new ArrayList();
            }
        }

        if ($sort) {
            $attendees = $attendees->sort($sort);
        }

        if ($limit) {
            $attendees = $attendees->limit($limit);
        }

        return $attendees;
    }

    public function columns()
    {
        // TODO: add edit link
        $fields = [
            'Created' => 'Aankoopdatum',
            'TicketPage.Title' => 'Event',
            'Title' => 'Name',
            'Ticket.Title' => 'Ticket',
            'TicketCode' => 'Ticket #',
            'CheckedIn.Nice' => 'Checked in',
            'TicketStatus' => 'Status',
            // 'CMSEditLink' => ['link' => true]
        ];

        return $fields;
    }
    
    public function getTicketPages()
    {
        $ticketPages = Attendee::get()->column('TicketPageID');
        if (count($ticketPages)) {
            return SiteTree::get()->filter(['ID' => $ticketPages])->sort('ID DESC');//->map()->toArray();
        } else {
            return new ArrayList();
        }
    }

    public function parameterFields()
    {
        $ticketPages = $this->getTicketPages();
        $fields = FieldList::create(
            DropdownField::create(
                'TicketPage', 
                _t('Broarm\EventTickets\Reports.TicketPage', 'All tickets for event'), 
                $ticketPages->map()->toArray()
            )->setEmptyString(_t('Broarm\EventTickets\Reports.TicketPageEmpty', 'Select event')),
            DropdownField::create(
                'TicketStatus', 
                _t(__CLASS__ . '.TicketStatus', 'Tickets with status'), 
                Attendee::singleton()->dbObject('TicketStatus')->enumValues(),
                Attendee::STATUS_ACTIVE
            ),
            DropdownField::create(
                'ReservationStatus', 
                _t(__CLASS__ . '.ReservationStatus', 'Tickets with reservation status'), 
                Reservation::singleton()->getStatusOptions(),
                Reservation::STATUS_PAID
            ),
            DropdownField::create(
                'DefinedPeriod', 
                _t('Broarm\EventTickets\Reports.DefinedPeriod', 'Period'), 
                [
                    'Day' => _t('Broarm\EventTickets\Reports.Day', 'Today'),
                    'Week' => _t('Broarm\EventTickets\Reports.Week', 'This week'),
                    'Month' => _t('Broarm\EventTickets\Reports.Month', 'This month'),
                    'Year' => _t('Broarm\EventTickets\Reports.Year', 'This year'),
                    'Other' => _t('Broarm\EventTickets\Reports.Other', 'Custom period'),
                ]
            )->setEmptyString(_t('Broarm\EventTickets\Reports.FilterPeriod', 'Filter on period')),
            DateField::create('CustomPeriodFrom',  _t('Broarm\EventTickets\Reports.CustomPeriodFrom', 'From date')),
            DateField::create('CustomPeriodTill',  _t('Broarm\EventTickets\Reports.CustomPeriodTill', 'Till date')),
        );

        $this->extend('updateParameterFields', $fields);
        return $fields;
    }
}
