<?php

namespace Broarm\EventTickets\Model\UserFields;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

/**
 * Class AttendeeExtraFieldOption
 *
 * @property string Title
 * @property boolean Default
 *
 * @method UserField Field()
 */
class UserFieldOption extends DataObject
{
    private static $table_name = 'EventTickets_UserFieldOption';

    private static $db = array(
        'Title' => 'Varchar',
        'Default' => 'Boolean',
        'Sort' => 'Int'
    );
    
    private static $default_sort = 'Sort ASC';

    private static $has_one = array(
        'Field' => UserField::class
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
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', _t('AttendeeExtraFieldOption.Title', 'Title')),
            CheckboxField::create('Default', _t('AttendeeExtraFieldOption.Default', 'Set as default'))
        ));

        return $fields;
    }
}
