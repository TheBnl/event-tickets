<?php
/**
 * UserFieldsGridFieldConfig.php
 *
 * @author Bram de Leeuw
 * Date: 24/05/17
 */

namespace Broarm\EventTickets;

use GridFieldAddNewInlineButton;
use GridFieldConfig;
use GridFieldDeleteAction;
use GridFieldEditableColumns;
use GridFieldEditButton;
use GridFieldFooter;
use GridFieldOrderableRows;
use GridFieldTitleHeader;
use GridFieldToolbarHeader;

/**
 * Class UserOptionSetFieldGridFieldConfig
 */
class UserOptionSetFieldGridFieldConfig extends GridFieldConfig
{
    public function __construct()
    {
        parent::__construct();
        $this->addComponent(new GridFieldToolbarHeader());
        $this->addComponent(new GridFieldTitleHeader());
        $this->addComponent(new GridFieldOrderableRows());
        $this->addComponent(new GridFieldAddNewInlineButton("toolbar-header-right"));
        $this->addComponent(new GridFieldEditableColumns());
        $this->addComponent(new GridFieldDeleteAction());
        $this->addComponent(new GridFieldFooter());
    }
}