<?php
use Broarm\EventTickets\UserOptionSetFieldGridFieldConfig;

/**
 * Class UserOptionSetField
 *
 * @author Bram de Leeuw
 * @package UserTextField
 *
 * @method \HasManyList Options()
 */
class UserOptionSetField extends Broarm\EventTickets\UserField
{
    private static $has_many = array(
        'Options' => 'Broarm\EventTickets\UserFieldOption'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        if ($this->exists()) {
            $fields->addFieldToTab('Root.Main', GridField::create(
                'Options',
                _t('AttendeeExtraField.Options', 'Add field options'),
                $this->Options(),
                UserOptionSetFieldGridFieldConfig::create()
            ));
        }

        return $fields;
    }

    /**
     * @param string $fieldName
     * @param null   $defaultValue
     *
     * @return OptionsetField
     */
    public function createField($fieldName, $defaultValue = null)
    {
        return OptionsetField::create($fieldName, $this->Title, $this->Options()->map()->toArray(), $defaultValue);
    }

    /**
     * Get the value by set option
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->Options()->byID($this->getField('Value'))->Title;
    }
}
