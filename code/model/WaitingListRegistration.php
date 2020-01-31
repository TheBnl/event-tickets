<?php
/**
 * WaitingListRegistration.php
 *
 * @author Bram de Leeuw
 * Date: 09/05/17
 */

namespace Broarm\EventTickets;

use DataObject;
use FieldList;
use ReadonlyField;
use Tab;
use TabSet;

/**
 * Class WaitingListRegistration
 *
 * @package Broarm\EventTickets
 *
 * @property string Title
 * @property string Email
 * @property string Telephone
 *
 * @method \CalendarEvent Event
 */
class WaitingListRegistration extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(255)',
        'Email' => 'Varchar(255)',
        'Telephone' => 'Varchar(255)'
    );

    private static $has_one = array(
        'Event' => 'CalendarEvent'
    );

    private static $summary_fields = array(
        'Title' => 'Name',
        'Email' => 'Email',
        'Telephone' => 'Telephone'
    );

    public function getCMSFields()
    {
        $fields = new FieldList(new TabSet('Root', $mainTab = new Tab('Main')));
        $fields->addFieldsToTab('Root.Main', array(
            ReadonlyField::create('Title', _t('WaitingListRegistration.Name', 'Name')),
            ReadonlyField::create('Email', _t('WaitingListRegistration.Email', 'Email')),
            ReadonlyField::create('Telephone', _t('WaitingListRegistration.Telephone', 'Telephone'))
        ));

        $fields->addFieldsToTab('Root.Main', array());
        $this->extend('updateCMSFields', $fields);
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

    public function canView($member = null)
    {
        return $this->Event()->canView($member);
    }

    public function canEdit($member = null)
    {
        return $this->Event()->canEdit($member);
    }

    public function canDelete($member = null)
    {
        return $this->Event()->canDelete($member);
    }

    public function canCreate($member = null)
    {
        return $this->Event()->canCreate($member);
    }
}
