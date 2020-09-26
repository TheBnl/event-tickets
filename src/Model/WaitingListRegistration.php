<?php

namespace Broarm\EventTickets\Model;

use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;

/**
 * Class WaitingListRegistration
 *
 * @package Broarm\EventTickets
 *
 * @property string Title
 * @property string Email
 * @property string Telephone
 */
class WaitingListRegistration extends DataObject
{
    private static $table_name = 'EventTickets_WaitingListRegistration';

    private static $db = array(
        'Title' => 'Varchar',
        'Email' => 'Varchar',
        'Telephone' => 'Varchar'
    );

    private static $has_one = array(
        //'Event' => 'CalendarEvent'
    );

    private static $summary_fields = array(
        'Title' => 'Name',
        'Email' => 'Email',
        'Telephone' => 'Telephone'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab('Root.Main', array(
            ReadonlyField::create('Title', _t(__CLASS__ . '.Name', 'Name')),
            ReadonlyField::create('Email', _t(__CLASS__ . '.Email', 'Email')),
            ReadonlyField::create('Telephone', _t(__CLASS__ . '.Telephone', 'Telephone'))
        ));

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

//    public function canView($member = null)
//    {
//        return $this->Event()->canView($member);
//    }
//
//    public function canEdit($member = null)
//    {
//        return $this->Event()->canEdit($member);
//    }
//
//    public function canDelete($member = null)
//    {
//        return $this->Event()->canDelete($member);
//    }
//
//    public function canCreate($member = null)
//    {
//        return $this->Event()->canCreate($member);
//    }
}
