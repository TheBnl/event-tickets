<?php

namespace Broarm\EventTickets\Dev;

use Broarm\EventTickets\Fields\AttendeeField;
use Broarm\EventTickets\Model\Attendee;
use Broarm\EventTickets\Model\UserFields\UserField;
use LeKoala\ExcelImportExport\ExcelBulkLoader;

class GuestListBulkLoader extends ExcelBulkLoader
{
    private $ticket_page_id = null;

    public $columnMap = [
        'Email' => '->setEmail',
        'FirstName' => '->setFirstName',
        'Surname' => '->setSurname',
        'TicketPageID' => 'TicketPageID'
    ];

    public $requiredColumns = [
        'Email',
        'FirstName',
        'Surname' 
    ];

    public $duplicateChecks = [
        'TicketCode' => 'TicketCode'
    ];

    public function __construct($objectClass, $ticketPageID)
    {
        $this->ticket_page_id = $ticketPageID;
        parent::__construct($objectClass);
    }

    protected function processRecord($record, $columnMap, &$results, $preview = false, $makeRelations = false)
    {
        // check if all required columns are present
        if ($requiredColumns = $this->requiredColumns) {
            foreach ($requiredColumns as $required) {
                if (!isset($record[$required])) {
                    return null;
                }
            }
        }

        $record['TicketPageID'] = $this->ticket_page_id;
        $objId = parent::processRecord($record, $columnMap, $results, $preview, $makeRelations);
        
        // make sure we have an TicketCode
        $attendee = Attendee::get_by_id($objId);
        $attendee->write();

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
