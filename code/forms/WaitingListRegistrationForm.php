<?php
/**
 * CheckInForm.php
 *
 * @author Bram de Leeuw
 * Date: 07/04/17
 */

namespace Broarm\EventTickets;

use FieldList;
use Form;
use FormAction;
use RequiredFields;
use TextField;

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