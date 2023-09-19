<?php

namespace Broarm\EventTickets\Reports;

use Broarm\EventTickets\Forms\CheckInValidator;
use Broarm\EventTickets\Model\CheckInValidatorResult;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Reports\Report;

class CheckInReport extends Report
{
    public function title()
    {
        return _t(__CLASS__ . '.Title', 'Checkinverloop');
    }

    public function sourceRecords($params, $sort, $limit)
    {
        $results = CheckInValidatorResult::get();
        $filter = array_filter([
            'MessageCode' => isset($params['Message']) ? $params['Message'] : null,
            'Attendee.TicketPageID' => isset($params['TicketPage']) ? $params['TicketPage'] : null,
        ]);

        if (count($filter)) {
            $results = $results->filter($filter);
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
            $results = $results->filter([
                'Created:GreaterThanOrEqual' => $from,
                'Created:LessThanOrEqual' => $till
            ]);
        }

        if ($sort) {
            $results = $results->sort($sort);
        }

        if ($limit) {
            $results = $results->limit($limit);
        }

        return $results;
    }

    public function columns()
    {
        $fields = [
            'Created' => 'Checked on',
            'MessageLabel' => 'Message',
            'TicketCode' => 'TicketCode',
            'Attendee.Title' => 'Name',
            'Attendee.Ticket.Title' => 'Ticket',
            'Attendee.TicketPage.Title' => 'Event',
        ];

        return $fields;
    }
    
    public function getTicketPages()
    {
        return SiteTree::get()->leftJoin(
            'EventTickets_Attendee', 
            'EventTickets_Attendee.TicketPageID = SiteTree.ID',
        )
            ->where('EventTickets_Attendee.TicketPageID != 0')
            ->sort('ID DESC');
    }

    public function getMessageOptions(array $messages)
    {
        $messages = array_combine($messages, $messages);
        return array_map(function ($message) {
            return CheckInValidator::messageLabel($message);
        }, $messages);
    }

    public function parameterFields()
    {
        $ticketPages = $this->getTicketPages();
        $fields = FieldList::create(
            DropdownField::create(
                'TicketPage', 
                _t('Broarm\EventTickets\Reports.TicketPage', 'All checkins for event'), 
                $ticketPages->map()->toArray()
            )->setEmptyString(_t('Broarm\EventTickets\Reports.TicketPageEmpty', 'Select event')),
            DropdownField::create(
                'Message', 
                _t(__CLASS__ . '.Message', 'Checkin message'), 
                $this->getMessageOptions([
                    CheckInValidator::MESSAGE_ERROR,
                    CheckInValidator::MESSAGE_NO_CODE,
                    CheckInValidator::MESSAGE_CODE_NOT_FOUND,
                    CheckInValidator::MESSAGE_TICKET_CANCELLED,
                    CheckInValidator::MESSAGE_ALREADY_CHECKED_IN,
                    CheckInValidator::MESSAGE_CHECK_OUT_SUCCESS,
                    CheckInValidator::MESSAGE_CHECK_IN_SUCCESS,
                ])
            )->setEmptyString(_t('Broarm\EventTickets\Reports.AllMessages', 'All messages')),
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
