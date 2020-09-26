<?php

namespace Broarm\EventTickets\Forms;

use Broarm\EventTickets\Model\WaitingListRegistration;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextField;

class WaitingListRegistrationForm extends Form
{
    public function __construct($controller, $name = 'CheckInForm')
    {
        $fields = FieldList::create(
            TextField::create('Title', _t('WaitingListRegistration.Name', 'Name')),
            TextField::create('Email', _t('WaitingListRegistration.Email', 'Email')),
            TextField::create('Telephone', _t('WaitingListRegistration.Telephone', 'Telephone'))
        );

        $actions = FieldList::create(
            FormAction::create('doRegister', _t('WaitingListRegistrationForm.REGISTER', 'Register'))
        );

        $required = new RequiredFields(array('Title', 'Email'));

        parent::__construct($controller, $name, $fields, $actions, $required);
        $this->extend('updateWaitingListRegistrationForm');
    }

    /**
     * Sets the person on the waiting list
     *
     * @param                             $data
     * @param WaitingListRegistrationForm $form
     */
    public function doRegister($data, WaitingListRegistrationForm $form)
    {
        $form->saveInto($registration = WaitingListRegistration::create());
        $registration->EventID = $this->getController()->ID;
        $this->getController()->WaitingList()->add($registration);
        $this->getController()->redirect($this->getController()->Link('?waitinglist=1'));
    }

}
