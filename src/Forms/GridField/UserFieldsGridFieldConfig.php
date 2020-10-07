<?php

namespace Broarm\EventTickets\Forms\GridField;

use SilverStripe\Forms\GridField\GridField_ActionMenu;
use SilverStripe\Forms\GridField\GridFieldConfig_Base;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * Class UserOptionSetFieldGridFieldConfig
 */
class UserFieldsGridFieldConfig extends GridFieldConfig_Base
{
    public function __construct($editableColumns = [])
    {
        parent::__construct();
        $this->removeComponentsByType(new GridFieldDataColumns());
        $this->addComponent(new GridFieldDetailForm());
        $this->addComponent(new GridFieldOrderableRows());
        $this->addComponent(new GridFieldAddNewInlineButton());
        $this->addComponent($gfEditableColumns = new GridFieldEditableColumns());
        $this->addComponent(new GridFieldDeleteAction());
        $this->addComponent(new GridFieldEditButton());
        $this->addComponent(new GridField_ActionMenu());

        if (!empty($editableColumns)) {
            $gfEditableColumns->setDisplayFields($editableColumns);
        }
    }
}
