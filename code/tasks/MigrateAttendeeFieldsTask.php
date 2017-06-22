<?php
/**
 * MigrateAttendeeFieldsTask.php
 *
 * @author Bram de Leeuw
 * Date: 30/05/17
 */

namespace Broarm\EventTickets;

use BuildTask;
use CalendarEvent;
use DB;
use Director;
use SQLSelect;

/**
 * Class CleanCartTask
 * Cleanup discarded tasks
 *
 * @deprecated after version 1.1.5 the custom fields where moved to UserFields.
 * @package Broarm\EventTickets
 */
class MigrateAttendeeFieldsTask extends BuildTask
{
    protected $title = 'Migrate attendee fields task';

    protected $description = 'Moves the attendee fields from the model to relations. This enables the custom fields feature from version 1.1.5';

    protected $enabled = false;

    /**
     * @param \SS_HTTPRequest $request
     */
    public function run($request)
    {
        if (!Director::is_cli()) echo '<pre>';
        echo "Start migration\n\n";

        $this->publishEvents();

        echo "Finished migration task \n";
        if (!Director::is_cli()) echo '</pre>';
    }

    /**
     * Publishes the events so the default fields are created
     */
    private function publishEvents() {
        /** @var CalendarEvent|TicketExtension $event */
        foreach (CalendarEvent::get() as $event) {
            if ($event->Tickets()->exists()) {
                if ($event->doPublish()) {
                    echo "[$event->ID] Published event \n";
                    $this->moveFieldData($event);
                } else {
                    echo "[$event->ID] Failed to publish event \n\n";
                }
            }
        }
    }

    /**
     * Migrate the field data from the attendee model to relations
     * Use an SQLSelect to access the data directly from the database
     *
     * @param CalendarEvent|TicketExtension $event
     */
    private function moveFieldData(CalendarEvent $event) {
        if ($event->Attendees()->exists()) {
            /** @var Attendee $attendee */
            foreach ($event->Attendees() as $attendee) {
                /** @var UserField $field */
                foreach ($event->Fields() as $field) {
                    $q = SQLSelect::create(
                        $field->FieldName,
                        "`{$attendee->getClassName()}`",
                        array('ID' => $attendee->ID)
                    );

                    try {
                        $value = $q->execute()->value();
                        $attendee->Fields()->add($field, array(
                            'Value' => $value
                        ));
                        echo "[$event->ID][$attendee->ID] Set '$field->FieldName' with '{$value}' \n";
                    } catch (\Exception $e) {
                        // fails silent
                        echo "[$event->ID][$attendee->ID] Failed, '$field->FieldName' does not exist \n";
                    }
                }
            }
            echo "[$event->ID] Finished migrating event \n\n";
        } else {
            echo "[$event->ID] No attendees to migrate \n\n";
        }
    }
}
