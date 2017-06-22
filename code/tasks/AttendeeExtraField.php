<?php
/**
 * AttendeeExtraField.php
 *
 * @author Bram de Leeuw
 * Date: 24/05/17
 */

namespace Broarm\EventTickets;

use DataObject;

/**
 * Class AttendeeExtraField
 *
 * @property string     Title
 * @property string     FieldName
 * @property \FormField FieldType
 * @property string     DefaultValue
 * @property string     ExtraClass
 * @property boolean    Required
 * @property boolean    Editable
 * @property int        Sort
 * @property int        EventID
 *
 * @method \CalendarEvent Event()
 * @method \HasManyList Options()
 * @deprecated deprecated after 1.1.5
 */
class AttendeeExtraField extends DataObject
{
    /**
     * Field name to be used in the AttendeeField (Composite field)
     *
     * @var string
     */
    protected $fieldName;

    private static $db = array(
        'Title' => 'Varchar(255)',
        'DefaultValue' => 'Varchar(255)',
        'ExtraClass' => 'Varchar(255)',
        'FieldName' => 'Varchar(255)',
        'Required' => 'Boolean',
        'Editable' => 'Boolean',
        'FieldType' => 'Enum("TextField,EmailField,CheckboxField,OptionsetField","TextField")',
        'Sort' => 'Int'
    );

    private static $has_one = array(
        'Event' => 'CalendarEvent'
    );

    private static $has_many = array(
        'Options' => 'Broarm\EventTickets\AttendeeExtraFieldOption'
    );
}