<?php

namespace Broarm\EventTickets\Model\UserFields;

use SilverStripe\Forms\TextField;

/**
 * Class UserTextField
 *
 * @author Bram de Leeuw
 * @package UserTextField
 */
class UserTextField extends UserField
{
    private static $table_name = 'EventTickets_UserTextField';

    /**
     * @var TextField
     */
    protected $fieldType = 'TextField';
}
