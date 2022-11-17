<?php

namespace Broarm\EventTickets\Dev;

use Broarm\EventTickets\Fields\AttendeeField;
use Broarm\EventTickets\Model\Attendee;
use Broarm\EventTickets\Model\UserFields\UserField;
use LeKoala\ExcelImportExport\ExcelBulkLoader;

class GuestListBulkLoader extends ExcelBulkLoader
{
    private $ticketPageId = null;
    
    private $sendTickets = false;

    public $columnMap = [
        'Email' => '->setEmail',
        'FirstName' => '->setFirstName',
        'Surname' => '->setSurname',
        'TicketPageID' => 'TicketPageID'
    ];

    private static $required_columns = [
        'Email',
        'FirstName',
        'Surname' 
    ];

    public $duplicateChecks = [
        'TicketCode' => 'TicketCode'
    ];

    public function __construct($objectClass, $ticketPageID, $sendTickets = false)
    {
        $this->sendTickets = $sendTickets;
        $this->ticketPageId = $ticketPageID;
        parent::__construct($objectClass);
    }

    protected function processRecord($record, $columnMap, &$results, $preview = false, $makeRelations = false)
    {
        // check if all required columns are present
        if ($requiredColumns = $this->config()->get('required_columns')) {
            foreach ($requiredColumns as $required) {
                if (!isset($record[$required])) {
                    return null;
                }
            }
        }

        $record['TicketPageID'] = $this->ticketPageId;
        $objId = parent::processRecord($record, $columnMap, $results, $preview, $makeRelations);
        
        // make sure we have an TicketCode
        $attendee = Attendee::get_by_id($objId);
        $attendee->write();

        if ($this->sendTickets) {
            $attendee->sendTicket();
        }

        return $objId;
    }

    public function setEmail(Attendee $attendee, $val, $record)
    {
        $field = UserField::get_one(UserField::class, ['Name' => 'Email']);
        if ($field && $field->exists()) {
            $attendee->Fields()->add($field->ID, ['Value' => $val]);
        }
    }

    public function setFirstName(Attendee $attendee, $val, $record)
    {
        $field = UserField::get_one(UserField::class, ['Name' => 'FirstName']);
        if ($field && $field->exists()) {
            $attendee->Fields()->add($field->ID, ['Value' => $val]);
        }
    }

    public function setSurname(Attendee $attendee, $val, $record)
    {
        $field = UserField::get_one(UserField::class, ['Name' => 'Surname']);
        if ($field && $field->exists()) {
            $attendee->Fields()->add($field->ID, ['Value' => $val]);
        }
    }
}
