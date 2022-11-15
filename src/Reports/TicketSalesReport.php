<?php

namespace Broarm\EventTickets\Reports;

use Broarm\EventTickets\Model\Attendee;
use Broarm\EventTickets\Model\Reservation;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBCurrency;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\Reports\Report;
use SilverStripe\View\ArrayData;
use SilverStripe\View\ViewableData;

class TicketSalesReport extends Report
{
    protected $dataClass = ViewableData::class;

    public function title()
    {
        return _t(__CLASS__ . '.Title', 'Ticketverkoop');
    }

    public function sourceRecords($params, $sort, $limit)
    {
        $reservations = [];
        if (isset($params['TicketPage'])) {
            $eventId = $params['TicketPage'];
            $reservations = Reservation::get()->filter(['TicketPageID' => $eventId])->column();   
        }
    
        $groupParam = isset($params['GroupBy']) ? $params['GroupBy'] : 'Day';
        switch($groupParam) {
            default:
            case 'Day':
                $groupBy = "DATE_FORMAT(Created, '%Y-%m-%d')";
                break;
            case 'Week':
                $groupBy = "DATE_FORMAT(Created, '%Y-%u')";
                break;
            case 'Month':
                $groupBy = "DATE_FORMAT(Created, '%Y-%m')";
                break;
            case 'Year':
                $groupBy = 'YEAR(Created)';
                break;
        }

        $gateway = "Mollie', 'Manual";
        if (isset($params['Gateway'])) {
            $gateway = $params['Gateway'];
        }

        $paymentTable = Payment::config()->get('table_name');
        $select = SQLSelect::create()
            ->setFrom($paymentTable)
            ->setSelect("$groupBy as Created, SUM(MoneyAmount) AS AmountSum")
            ->addWhere("Gateway IN ('$gateway')")
            ->addWhere("Status IN ('Authorized', 'Captured')")
            ->addGroupBy("$groupBy")
            ->addOrderBy('Created DESC');

        if (count($reservations)) {
            $reservations = implode(',', $reservations);
            $select = $select->addWhere("ReservationID IN ($reservations)");
        }

        $list = new ArrayList();
        $query = $select->execute();
        while($item = $query->next()) {
            $list->add(new ArrayData($item));
        }

        return $list;
    }

    public function columns()
    {
        $fields = [
            'Created' => [
                'title' => _t(__CLASS__ . '.Created', 'Datum'),
                'formatting' => function ($value, $item) {
                    // week month nr -> date
                    return $value;

                    // if (is_int($value)) {
                    //     return $value;
                    // } else {
                    //     return DBDatetime::create()->setValue($value)->Format('dd MMM yyyy');
                    // }
                }
            ],
            'AmountSum' => [
                'title' => _t(__CLASS__ . '.AmountSum', 'Verkocht'),
                // 'casting' => DBCurrency::class,
                'formatting' => function ($value, $item) {
                    return DBCurrency::create()->setValue($value)->Nice();
                }
            ],
        ];

        return $fields;
    }
    
    public function parameterFields()
    {
        $ticketPages = Attendee::get()->column('TicketPageID');
        if (count($ticketPages)) {
            $ticketPages = SiteTree::get()->filter(['ID' => $ticketPages])->sort('ID DESC')->map()->toArray();
        } else {
            $ticketPages = [];
        }
        
        $fields = FieldList::create(
            DropdownField::create(
                'TicketPage', 
                _t('Broarm\EventTickets\Reports.TicketPage', 'All tickets for event'), 
                $ticketPages
            )->setEmptyString(_t('Broarm\EventTickets\Reports.TicketPageEmpty', 'Select event')),
            DropdownField::create(
                'Gateway', 
                _t(__CLASS__ . '.Gateway', 'Toon betalingen van'), 
                GatewayInfo::getSupportedGateways()
            )->setEmptyString(_t(__CLASS__ . '.Gateway', 'Toon betalingen van')),

            DropdownField::create(
                'GroupBy', 
                _t('Broarm\EventTickets\Reports.GroupBy', 'Group by'), 
                [
                    'Day' => _t('Broarm\EventTickets\Reports.GroupByDay', 'Day'),
                    'Week' => _t('Broarm\EventTickets\Reports.GroupByWeek', 'Week'),
                    'Month' => _t('Broarm\EventTickets\Reports.GroupByMonth', 'Month'),
                    'Year' => _t('Broarm\EventTickets\Reports.GroupByYear', 'Year'),
                ]
            ),
            // DropdownField::create(
            //     'DefinedPeriod', 
            //     _t('Broarm\EventTickets\Reports.DefinedPeriod', 'Period'), 
            //     [
            //         'Day' => _t('Broarm\EventTickets\Reports.Day', 'Today'),
            //         'Week' => _t('Broarm\EventTickets\Reports.Week', 'This week'),
            //         'Month' => _t('Broarm\EventTickets\Reports.Month', 'This month'),
            //         'Year' => _t('Broarm\EventTickets\Reports.Year', 'This year'),
            //         'Other' => _t('Broarm\EventTickets\Reports.Other', 'Custom period'),
            //     ]
            // )->setEmptyString(_t('Broarm\EventTickets\Reports.FilterPeriod', 'Filter on period')),
            // FieldGroup::create([
            //     DateField::create('CustomPeriodFrom',  _t('Broarm\EventTickets\Reports.CustomPeriodFrom', 'From date')),
            //     DateField::create('CustomPeriodTill',  _t('Broarm\EventTickets\Reports.CustomPeriodTill', 'Till date')),
            // ])  
        );

        $this->extend('updateParameterFields', $fields);
        return $fields;
    }
}