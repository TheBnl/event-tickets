<?php

namespace Broarm\EventTickets\Reports;

use Broarm\EventTickets\Forms\CheckInValidator;
use Broarm\EventTickets\Model\CheckInValidatorResult;
use Broarm\EventTickets\Model\Ticket;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataQuery;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\Reports\Report;
use SilverStripe\View\ArrayData;

class CheckInReport extends Report
{
    public function title()
    {
        return _t(__CLASS__ . '.Title', 'Checkinverloop');
    }

    public function sourceRecords($params, $sort, $limit)
    {
        $table = CheckInValidatorResult::singleton()->baseTable();
        $groupByParam = isset($params['GroupBy']) ? $params['GroupBy'] : null;
        $groupParam = isset($params['GroupByScanDate']) ? $params['GroupByScanDate'] : null;
        switch($groupParam) {
            case 'Minute':
                $groupByScanDate = "DATE_FORMAT($table.Created, '%Y-%m-%d %H:%i')";
                break;
            case 'Hour':
                $groupByScanDate = "DATE_FORMAT($table.Created, '%Y-%m-%d %H:00')";
                break;
            case 'Day':
                $groupByScanDate = "DATE_FORMAT($table.Created, '%Y-%m-%d')";
                break;
            case 'Week':
                $groupByScanDate = "DATE_FORMAT($table.Created, '%Y-%u')";
                break;
            case 'Month':
                $groupByScanDate = "DATE_FORMAT($table.Created, '%Y-%m')";
                break;
            case 'Year':
                $groupByScanDate = "YEAR($table.Created)";
                break;
            default:
                $groupByScanDate = null;
        }

        $filter = [
            "$table.MessageCode" => isset($params['Message']) ? $params['Message'] : null,
            'Attendee.TicketPageID' => isset($params['TicketPage']) ? $params['TicketPage'] : null,
            'Buyable.Title' => isset($params['TicketType']) ? $params['TicketType'] : null,
        ];
        
        $select = SQLSelect::create()
            ->setFrom($table)
            ->addLeftJoin('EventTickets_Attendee', "Attendee.ID = $table.AttendeeID", 'Attendee')
            ->addLeftJoin('EventTickets_Buyable', 'Buyable.ID = Attendee.TicketID', 'Buyable')
            ->addLeftJoin('SiteTree', 'Event.ID = Attendee.TicketPageID', 'Event')
            ->setSelect("$table.MessageCode as MessageCode")
            ->addSelect("$table.Created as Created")
            ->addSelect("$table.TicketCode as TicketCode")
            ->addSelect("Attendee.TicketPageID as TicketPageID")
            ->addSelect("Attendee.Title as AttendeeName")
            ->addSelect("Buyable.Title as TicketTitle")
            ->addSelect("Event.Title as EventTitle");

        if ($groupByScanDate || !empty($groupByParam)) {
            $select = $select->setSelect("COUNT($table.ID) AS AmountScanned");

            if ($groupByScanDate) {
                $select
                    ->addSelect("$groupByScanDate as Created")
                    ->addGroupBy($groupByScanDate);
            } else {
                $select->addSelect("MAX($table.Created) as Created");
            }

            $aliasToSelect = [
                'TicketTitle' => 'Buyable.Title as TicketTitle',
                'AttendeeName' => 'Attendee.Title as AttendeeName',
                'EventTitle' => 'Event.Title as EventTitle',
                'MessageCode' => 'MessageCode',
            ];

            if (is_array($groupByParam)) {
                foreach ($groupByParam as $groupBy => $value) {
                    $select
                        ->addSelect($aliasToSelect[$groupBy])
                        ->addGroupBy($groupBy);
                }
            }
        }

        $from = null;
        $till = null;
        if (isset($params['DefinedPeriod'])) {
            $definedPeriod = $params['DefinedPeriod'];
            switch($definedPeriod) {
                case 'Day':
                    $from = date('Y-m-d');
                    $till = date('Y-m-d', strtotime('+1 day'));
                    break;
                case 'Week':
                    $from = date('Y-m-d', strtotime('-1 week'));
                    $till = date('Y-m-d', strtotime('+1 day'));
                    break;
                case 'Month':
                    $from = date('Y-m') . '-01';
                    $till = date('Y-m-t');
                    break;
                case 'Year':
                    $from = date('Y') . '-01-01';
                    $till = date('Y', strtotime('+1 year')) . '-01-01';
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
            $filter["$table.Created >= ?"] = $from;
            $filter["$table.Created <= ?"] = $till;
        }

        $filter = array_filter($filter);
        $select->addWhere($filter);
        
        if ($sort) {
            $select = $select->setOrderBy($sort);
        }

        if ($limit) {
            $select = $select->setLimit($limit);
        }

        $query = $select->execute();
        $list = new ArrayList();
        while($item = $query->next()) {
            $list->add(new ArrayData($item));
        }

        return $list;
    }

    public function columns()
    {
        $fields = [
            'Created' => [
                'title' => _t(__CLASS__ . '.Created', 'Scanned on'),
                // 'casting' => DBDate::class,
                // 'formatting' => function ($value, $item) {
                //     return DBDate::create()->setValue($value)->Nice();
                // }
            ],
            'AmountScanned' => 'Gescand',
            // 'MessageLabel' => 'Message',
            'MessageCode' => [
                'title' => _t(__CLASS__ . '.Message', 'Message'),
                // 'casting' => DBCurrency::class,
                'formatting' => function ($value, $item) {
                    return $value 
                        ? CheckInValidator::messageLabel($value)
                        : '';
                }
            ],
            'TicketCode' => 'TicketCode',
            'AttendeeName' => 'Name',
            'TicketTitle' => 'Ticket',
            'EventTitle' => 'Event',
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
        $ticketTypes = Ticket::get()->columnUnique('Title');

        $fields = FieldList::create(
            DropdownField::create(
                'TicketPage', 
                _t('Broarm\EventTickets\Reports.TicketPage', 'All checkins for event'), 
                $ticketPages->map()->toArray()
            )->setEmptyString(_t('Broarm\EventTickets\Reports.TicketPageEmpty', 'Select event')),
            DropdownField::create(
                'TicketType', 
                _t('Broarm\EventTickets\Reports.TicketType', 'All checkins for ticket type'), 
                array_combine($ticketTypes, $ticketTypes)
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
            DropdownField::create(
                'GroupByScanDate', 
                _t('Broarm\EventTickets\Reports.GroupByScanDate', 'Group by scan date'), 
                [
                    'Minute' => _t('Broarm\EventTickets\Reports.GroupByMinute', 'Minute'),
                    'Hour' => _t('Broarm\EventTickets\Reports.GroupByHour', 'Hour'),
                    'Day' => _t('Broarm\EventTickets\Reports.GroupByDay', 'Day'),
                    'Week' => _t('Broarm\EventTickets\Reports.GroupByWeek', 'Week'),
                    'Month' => _t('Broarm\EventTickets\Reports.GroupByMonth', 'Month'),
                    'Year' => _t('Broarm\EventTickets\Reports.GroupByYear', 'Year'),
                ]
            )->setEmptyString(_t('Broarm\EventTickets\Reports.GroupByEmpty', 'Don\'t group')),
            CheckboxSetField::create('GroupBy', _t('Broarm\EventTickets\Reports.GroupByMessage', 'Group by'), [
                'MessageCode' => _t('Broarm\EventTickets\Reports.GroupByMessage', 'Group by message'),
                'TicketTitle' => _t('Broarm\EventTickets\Reports.GroupByTicket', 'Group by ticket'),
                'EventTitle' => _t('Broarm\EventTickets\Reports.GroupByEvent', 'Group by event'),
                'AttendeeName' => _t('Broarm\EventTickets\Reports.GroupByAttendee', 'Group by attendee'),
            ]),
        );

        $this->extend('updateParameterFields', $fields);
        return $fields;
    }
}
