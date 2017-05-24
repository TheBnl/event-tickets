<?php
/**
 * GridFieldConfig_Fields.php
 *
 * @author Bram de Leeuw
 * Date: 24/05/17
 */

namespace Broarm\EventTickets;

use GridFieldAddNewButton;
use GridFieldConfig;
use GridFieldDataColumns;
use GridFieldDeleteAction;
use GridFieldDetailForm;
use GridFieldEditButton;
use GridFieldFooter;
use GridFieldOrderableRows;
use GridFieldTitleHeader;
use GridFieldToolbarHeader;

/**
 * Class GridFieldConfig_Editable
 */
class GridFieldConfig_Fields extends GridFieldConfig
{

    /**
     * GridFieldConfig_Editable constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->addComponent(new GridFieldToolbarHeader());
        $this->addComponent(new GridFieldTitleHeader());
        $this->addComponent(new GridFieldDetailForm());
        $this->addComponent(new GridFieldOrderableRows());
        $this->addComponent(new GridFieldAddNewButton("toolbar-header-right"));
        $this->addComponent(new GridFieldDataColumns());
        $this->addComponent(new GridFieldEditButton());
        $this->addComponent(new GridFieldDeleteAction());
        $this->addComponent(new GridFieldFooter());
    }
}