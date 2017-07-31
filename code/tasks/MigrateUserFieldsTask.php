<?php
/**
 * MigrateUserFieldsTask.php
 *
 * @author Bram de Leeuw
 * Date: 22/06/17
 */

namespace Broarm\EventTickets;

use BuildTask;
use DB;
use Director;

/**
 * Class MigrateUserFieldsTask
 * Migrate the attendee fields to new user fields
 *
 * @package Broarm\EventTickets
 */
class MigrateUserFieldsTask extends BuildTask
{
    protected $title = 'Migrate user fields task';

    protected $description = 'Migrate the AttendeeExtraFields to the new UserFields';

    protected $userFields;
    protected $userFieldOption;

    /**
     * @param \SS_HTTPRequest $request
     */
    public function run($request)
    {
        if (!Director::is_cli()) {
            echo '<pre>';
        }

        echo "Start user field migration\n\n";

        $this->migrateValues();
        $this->migrateFields();

        echo "Finished user field migration task \n";
        if (!Director::is_cli()) {
            echo '</pre>';
        }
    }

    /**
     * Migrate the AttendeeExtraFields to UserFields
     */
    private function migrateFields()
    {
        if ($attendeeFields = AttendeeExtraField::get()) {
            foreach ($attendeeFields as $attendeeField) {
                $this->findOrMakeUserFieldFor($attendeeField);
            }
        }
    }

    /**
     * Make a new UserField based on the given AttendeeExtraField
     *
     * @param AttendeeExtraField $attendeeField
     */
    private function findOrMakeUserFieldFor(AttendeeExtraField $attendeeField)
    {
        if (!$field = $this->getUserFields()->byID($attendeeField->ID)) {
            /** @var UserField $field */
            $class = self::mapFieldType($attendeeField->FieldType, $attendeeField->FieldName);
            $field = $class::create();
        }

        $field->ClassName = self::mapFieldType($attendeeField->FieldType, $attendeeField->FieldName);
        $field->ID = $attendeeField->ID;
        $field->Name = $attendeeField->FieldName;
        $field->Title = $attendeeField->Title;
        $field->Default = $attendeeField->DefaultValue;
        $field->ExtraClass = $attendeeField->ExtraClass;
        $field->Required = $attendeeField->Required;
        $field->Editable = $attendeeField->Editable;
        $field->EventID = $attendeeField->EventID;
        $field->Sort = $attendeeField->Sort;

        if ($attendeeField->Options()->exists() && $field->ClassName === 'UserOptionSetField') {
            /** @var \UserOptionSetField $field */
            /** @var AttendeeExtraFieldOption $attendeeOption */
            foreach ($attendeeField->Options() as $option) {
                $field->Options()->add($this->findOrMakeUserOptionFor($option));
                echo "[{$field->ID}] Added AttendeeExtraFieldOption as UserFieldOption\n";
            }
        }

        $field->write();
        echo "[{$field->ID}] Migrated AttendeeExtraField to $field->ClassName\n";
    }

    /**
     * Find or make an option based on the given AttendeeExtraFieldOption
     *
     * @param AttendeeExtraFieldOption $attendeeOption
     *
     * @return \DataObject|UserFieldOption
     */
    private function findOrMakeUserOptionFor(AttendeeExtraFieldOption $attendeeOption)
    {
        if (!$option = $this->getUserFieldOption()->byID($attendeeOption->ID)) {
            $option = UserFieldOption::create();
        }

        $option->ID = $attendeeOption->ID;
        $option->Title = $attendeeOption->Title;
        $option->Default = $attendeeOption->Default;
        $option->Sort = $attendeeOption->Sort;
        return $option;
    }

    /**
     * Map the given field type to one of the available class names
     * Also looks into the current mapping if the field has a new type
     *
     * @param      $type
     * @param null $name
     *
     * @return string
     */
    private static function mapFieldType($type, $name = null)
    {
        $types = array(
            'TextField' => 'UserTextField',
            'EmailField' => 'UserEmailField',
            'CheckboxField' => 'UserCheckboxField',
            'OptionsetField' => 'UserOptionSetField'
        );

        $currentDefaults = Attendee::config()->get('default_fields');
        if ($currentDefaults && key_exists($name, $currentDefaults)) {
            return $currentDefaults[$name]['FieldType'];
        } else {
            return $types[$type];
        }

    }

    /**
     * Migrate the set values
     */
    public function migrateValues()
    {
        DB::query("
           UPDATE `Broarm\EventTickets\Attendee_Fields`
            SET `Broarm\EventTickets\UserFieldID` = `Broarm\EventTickets\AttendeeExtraFieldID`
        ");

        echo "\nMigrated the set values\n\n";
    }

    /**
     * Get and store the user fields list
     *
     * @return \DataList
     */
    private function getUserFields()
    {
        return $this->userFields ? $this->userFields : UserField::get();
    }

    /**
     * Get and store the User field options
     *
     * @return \DataList
     */
    private function getUserFieldOption()
    {
        return $this->userFieldOption ? $this->userFieldOption : UserFieldOption::get();
    }
}
