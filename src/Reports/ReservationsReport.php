<?php

namespace Broarm\EventTickets\Reports;

use Broarm\EventTickets\Model\Attendee;
use Broarm\EventTickets\Model\Reservation;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Reports\Report;

class ReservationsReport extends Report
{
    public function title()
    {
        return _t(__CLASS__ . '.Title', 'Reserveringen');
    }

    public function sourceRecords($params, $sort, $limit)
    {
        $reservationStatus = Reservation::STATUS_PAID;
        if (isset($params['ReservationStatus'])) {
            $reservationStatus = $params['ReservationStatus'];
        }

        $reservations = Reservation::get()->filter([
            'Status' => $reservationStatus
        ]);

        if (isset($params['TicketPage'])) {
            $reservations = $reservations->filter(['TicketPageID' => $params['TicketPage']]);
        }

        if (isset($params['Gateway'])) {
            $reservations = $reservations->filter(['Gateway' => $params['Gateway']]);
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

        if ($from) {
            $reservations = $reservations->filter([
                'Created:GreaterThanOrEqual' => $from
            ]);
        }

        if ($till) {
            $reservations = $reservations->filter([
                'Created:LessThanOrEqual' => $till
            ]);
        }

        if ($sort) {
            $reservations = $reservations->sort($sort);
        }

        if ($limit) {
            $reservations = $reservations->limit($limit);
        }

        return $reservations;
    }

    public function columns()
    {
        // TODO: add edit link
        $fields = [
            'Created.Nice' => 'Aankoopdatum',
            'ReservationCode' => 'Reservation',
            'Title' => 'Customer',
            'Total.Nice' => 'Total',
            'State' => 'Status',
            'GatewayNice' => 'Payment method',
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
                'ReservationStatus', 
                _t(__CLASS__ . '.ReservationStatus', 'Status'), 
                Reservation::singleton()->getStatusOptions(),
                Reservation::STATUS_PAID
            ),
            DropdownField::create(
                'Gateway', 
                _t(__CLASS__ . '.Gateway', 'Payment method'), 
                GatewayInfo::getSupportedGateways(),
            )->setEmptyString(_t(__CLASS__ . '.GatewayEmpty', 'Select payment method')),
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
            FieldGroup::create([
                DateField::create('CustomPeriodFrom',  _t('Broarm\EventTickets\Reports.CustomPeriodFrom', 'From date')),
                DateField::create('CustomPeriodTill',  _t('Broarm\EventTickets\Reports.CustomPeriodTill', 'Till date')),
            ])
        );

        $this->extend('updateParameterFields', $fields);
        return $fields;
    }
}
