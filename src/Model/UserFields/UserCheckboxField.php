<?php

namespace Broarm\EventTickets\Model\UserFields;

use SilverStripe\Forms\CheckboxField;

/**
 * Class UserCheckboxField
 *
 * @author Bram de Leeuw
 * @package UserTextField
 */
class UserCheckboxField extends UserField
{
    private static $table_name = 'EventTickets_UserCheckboxField';

    /**
     * @var CheckboxField
     */
    protected $fieldType = 'CheckboxField';

    /**
     * Get the value in a readable manner
     *
     * @return string
     */
    public function getNiceValue()
    {
        return (bool)$this->getField('Value')
            ? _t('Boolean.YESANSWER', 'Yes')
            : _t('Boolean.NOANSWER', 'No');
    }
}
