<?php

namespace Broarm\EventTickets\Forms;

use Broarm\EventTickets\Controllers\CheckInController;
use Broarm\EventTickets\Model\CheckInValidatorResult;
use Exception;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ValidationResult;

class CheckInForm extends Form
{
    public function __construct($controller, $name = 'CheckInForm')
    {
        $fields = FieldList::create(
            TextField::create('TicketCode', _t(__CLASS__ . '.TicketCode', 'Ticket code'))
                ->setAttribute('autofocus', true)
        );

        $actions = FieldList::create(
            FormAction::create('doCheckIn', _t(__CLASS__ . '.CheckIn', 'Check in'))
        );

        $required = new RequiredFields(array('TicketCode'));
        parent::__construct($controller, $name, $fields, $actions, $required);
        $this->extend('onAfterConstruct');

    }

    /**
     * Do the check in, if all checks pass return a success
     *
     * @param             $data
     * @param CheckInForm $form
     *
     * @return HTTPResponse
     */
    public function doCheckIn($data, CheckInForm $form)
    {
        /** @var CheckInController $controller */
        $controller = $form->getController();
        $validator = new CheckInValidator();
        $result = $validator->validate($data['TicketCode']);
        
        try {
            CheckInValidatorResult::createFromValidatorResult($result)->write();
        } catch (Exception $e) {
            // soft fail
        }

        switch ($result['Code']) {
            case CheckInValidator::MESSAGE_CHECK_OUT_SUCCESS:
                $validator->getAttendee()->checkOut();
                break;
            case CheckInValidator::MESSAGE_CHECK_IN_SUCCESS:
                $validator->getAttendee()->checkIn();
                break;
        }

        if (Director::is_ajax()) {
            $response = new HTTPResponse(json_encode($result), 200);
            $response->addHeader('Content-Type', 'application/json');
            return $response;
        } else {
            $form->sessionMessage($result['Message'], $result['Type'], ValidationResult::CAST_TEXT);
            $this->extend('onAfterCheckIn', $response, $form, $result);
            return $response ? $response : $controller->redirectBack();
        }
    }
}
