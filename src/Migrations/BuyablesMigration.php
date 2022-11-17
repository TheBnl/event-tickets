<?php

namespace Broarm\EventTickets\Migrations;

use Broarm\EventTickets\Extensions\TicketExtension;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Dev\MigrationTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\Queries\SQLUpdate;
use SilverStripe\Versioned\Versioned;

class BuyablesMigration extends MigrationTask
{
    private static $segment = 'buyables-migration';
    
    protected $title = 'Migrate Ticket table to Buyable.';
    
    protected $description = 'Migrate Ticket table to Buyable and Capacity to MaxCapacity or vice-versa.';

    public function up()
    {
        $this->changeTable('EventTickets_Ticket', 'EventTickets_Buyable');
        $this->changeField('Capacity', 'MaxCapacity');
    }

    public function down()
    {
        $this->changeTable('EventTickets_Buyable', 'EventTickets_Ticket');
        $this->changeField('MaxCapacity', 'Capacity');
    }

    protected function changeTable($from, $to)
    {
        if (ClassInfo::hasTable($to)) {
            if (DB::query("SELECT 1 FROM $to")->numRecords()) {
                return;
            }

            // empty so drop table
            DB::query("DROP TABLE $to");
        }

        if (!ClassInfo::hasTable($from)) {
            $from = '_obsolete_' . $from;
        }

        if (!ClassInfo::hasTable($from)) {
            return;
        }

        DB::get_conn()->withTransaction(function() use ($from, $to) {
            DB::query("ALTER TABLE $from RENAME TO $to");
            DB::get_schema()->alterationMessage("Altered table '$from' renamed to '$to'", 'changed');
        } , function () use ($from) {
            DB::get_schema()->alterationMessage("Failed to alter table '$from'", 'error');
        }, false, true);
    }

    protected function changeField($from, $to)
    {
        $classes = ClassInfo::classesWithExtension(TicketExtension::class);
        foreach ($classes as $class) {
            $tableName = DataObject::getSchema()->tableName($class);
            $fields = DB::field_list($tableName);

            if (!isset($fields[$from])) {
                return;
            }

            $tables = [
                $tableName
            ];

            $isVersioned = DataObject::has_extension($class, Versioned::class);
            if ($isVersioned) {
                $tables[] = $tableName . "_" . Versioned::LIVE;
                $tables[] = $tableName . "_Versions";
            }

            DB::get_conn()->withTransaction(function() use ($tables, $from, $to) {
                foreach ($tables as $table) {
                    $query = SQLUpdate::create("\"$table\"")
                        ->assignSQL($to, $from);
                    $query->execute();
                    DB::query("ALTER TABLE \"$table\" DROP COLUMN $from");
                }
    
                DB::get_schema()->alterationMessage('Migrated Capacity field to MaxCapacity', 'changed');
            } , function () {
                DB::get_schema()->alterationMessage('Failed to alter Capacity field', 'error');
            }, false, true);
        }
    }
}
