<?php
/**
 * AttendeeExtraFieldOption.php
 *
 * @author Bram de Leeuw
 * Date: 24/05/17
 */

namespace Broarm\EventTickets;

/**
 * Class AttendeeExtraFieldOption
 *
 * @property string Title
 * @property boolean Default
 * @property int Sort
 * @method AttendeeExtraField Field
 * @deprecated deprecated since 1.1.5
 */
class AttendeeExtraFieldOption extends \DataObject
{
    private static $db = array(
        'Title' => 'Varchar(255)',
        'Default' => 'Boolean',
        'Sort' => 'Int'
    );

    private static $has_one = array(
        'Field' => 'Broarm\EventTickets\AttendeeExtraField'
    );
}