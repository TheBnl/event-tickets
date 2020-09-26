<?php

namespace Broarm\EventTickets\Forms\GridField;

use Broarm\EventTickets\Model\UserFields\UserField;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldAddNewMultiClass;

/**
 * Class UserFieldsGridFieldConfig
 * @package Broarm\EventTickets\Forms\GridField
 */
class UserFieldsGridFieldConfig extends UserOptionSetFieldGridFieldConfig
{
    public function __construct()
    {
        parent::__construct();
        $availableClasses = ClassInfo::subclassesFor(UserField::class);
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
