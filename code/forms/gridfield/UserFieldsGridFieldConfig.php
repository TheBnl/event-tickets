<?php
/**
 * UserFieldsGridFieldConfig.php
 *
 * @author Bram de Leeuw
 * Date: 24/05/17
 */

namespace Broarm\EventTickets;

use ClassInfo;
use GridFieldAddNewButton;
use GridFieldAddNewInlineButton;
use GridFieldAddNewMultiClass;
use GridFieldDeleteAction;
use GridFieldDetailForm;
use GridFieldEditButton;

/**
 * Class UserFieldsGridFieldConfig
 */
class UserFieldsGridFieldConfig extends UserOptionSetFieldGridFieldConfig
{
    public function __construct()
    {
        parent::__construct();
        $availableClasses = ClassInfo::subclassesFor('Broarm\\EventTickets\\UserField');
        array_shift($availableClasses);

        $this->removeComponentsByType(new GridFieldAddNewInlineButton());
        $this->removeComponentsByType(new GridFieldDeleteAction());
        $this->addComponent(new GridFieldDetailForm());
        $this->addComponent(new GridFieldEditButton());
        $this->addComponent(new GridFieldDeleteAction());
        $this->addComponent($multiClassComponent = new GridFieldAddNewMultiClass());
        $multiClassComponent->setClasses($availableClasses);
    }
}