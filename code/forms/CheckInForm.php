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

class CheckInForm extends Form
{
    public function __construct($controller, $name = 'CheckInForm')
    {
        $fields = FieldList::create(
            TextField::create('TicketCode', _t('CheckInForm.TICKET_CODE', 'Ticket code'))
        );

        $actions = FieldList::create(
            FormAction::create('doCheckIn', _t('CheckInForm.CheckIn', 'Check in'))
        );

        $required = new RequiredFields(array('TicketCode'));

        parent::__construct($controller, $name, $fields, $actions, $required);
    }

    /**
     * Do the check in, if all checks pass return a success
     *
     * @param             $data
     * @param CheckInForm $form
     *
     * @return bool
     */
    public function doCheckIn($data, CheckInForm $form)
    {
        /** @var CheckInController $controller */
        $controller = $form->getController();
        $validator = CheckInValidator::create();
        $result = $validator->validate($data['TicketCode']);
        switch ($result['Code']) {
            default:
                $form->sessionMessage($result['Message'], strtolower($result['Type']), false);
                $controller->redirect($controller->Link());
                return false;
            case CheckInValidator::MESSAGE_CHECK_OUT_SUCCESS:
                $validator->getAttendee()->checkOut();
                break;
            case CheckInValidator::MESSAGE_CHECK_IN_SUCCESS:
                $validator->getAttendee()->checkIn();
                break;
        }

        $form->sessionMessage($result['Message'], strtolower($result['Type']), false);
        $controller->redirect($controller->Link());
        return true;
    }

}