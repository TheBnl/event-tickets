<?php
/**
 * AttendeeExtraFieldOption.php
 *
 * @author Bram de Leeuw
 * Date: 24/05/17
 */

namespace Broarm\EventTickets;

use CheckboxField;
use DataObject;
use FieldList;
use Tab;
use TabSet;
use TextField;

/**
 * Class AttendeeExtraFieldOption
 *
 * @property string Title
 * @property boolean Default
 * @method UserField Field
 */
class UserFieldOption extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(255)',
        'Default' => 'Boolean',
        'Sort' => 'Int'
    );
    
    private static $default_sort = 'Sort ASC';

    private static $has_one = array(
        'Field' => 'Broarm\EventTickets\UserField'
    );

    private static $summary_fields = array(
        'Title',
        'Default'
    );

    private static $translate = array(
        'Title'
    );

    public function getCMSFields()
    {
        $fields = new FieldList(new TabSet('Root', $mainTab = new Tab('Main')));
        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', _t('AttendeeExtraFieldOption.Title', 'Title')),
            CheckboxField::create('Default', _t('AttendeeExtraFieldOption.Default', 'Set as default'))
        ));

        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    public function canView($member = null)
    {
        return $this->Field()->canView($member);
    }

    public function canEdit($member = null)
    {
        return $this->Field()->canEdit($member);
    }

    public function canDelete($member = null)
    {
        return $this->Field()->canDelete($member);
    }

    public function canCreate($member = null)
    {
        return $this->Field()->canCreate($member);
    }
}
