<?php

namespace Broarm\EventTickets\Model;

use Broarm\EventTickets\Forms\CheckInValidator;
use Broarm\EventTickets\Model\Attendee;
use SilverStripe\ORM\DataObject;

class CheckInValidatorResult extends DataObject
{
    private static $table_name = 'EventTickets_CheckInValidatorResult';

    private static $db = [
        'MessageCode' => 'Varchar',
        'TicketCode' => 'Varchar'
    ];

    private static $summary_fields = [
        'Created' => 'Created',
        'TicketCode' => 'TicketCode',
        'MessageLabel' => 'Message',
        'Attendee.Title' => 'Attendee',
    ];

    private static $has_one = [
        'Attendee' => Attendee::class
    ];

    public function getMessageLabel()
    {
        return CheckInValidator::messageLabel($this->MessageCode);
    }

    public function getMessage()
    {
        return CheckInValidator::message($this->MessageCode, $this->TicketCode);
    }

    public static function createFromValidatorResult(array $result) : CheckInValidatorResult
    {
        return self::create([
            'MessageCode' => $result['Code'],
            'TicketCode' => $result['Ticket'],
            'AttendeeID' => isset($result['Attendee']) ? $result['Attendee']->ID : 0,
        ]);
    }
}