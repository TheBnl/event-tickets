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
    /**
     * @var OptionsetField
     */
    protected $fieldType = 'OptionsetField';

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
     * @param string  $fieldName
     * @param null    $defaultValue
     * @param boolean $main check if the field is for the main attendee
     *
     * @return OptionsetField
     */
    public function createField($fieldName, $defaultValue = null, $main = false)
    {
        /** @var OptionsetField $field */
        $field = parent::createField($fieldName, $defaultValue, $main);
        $field->setSource($this->Options()->map()->toArray());
        $field->setValue($defaultValue);
        return $field;
    }

    /**
     * Get the value by set option
     *
     * @return mixed
     */
    public function getValue()
    {
        if ($option = $this->Options()->byID($this->getField('Value'))) {
            return $option->Title;
        }

        return null;
    }
}
