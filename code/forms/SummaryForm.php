<?php
/**
 * SummaryForm.php
 *
 * @author Bram de Leeuw
 * Date: 10/03/17
 */

namespace Broarm\EventTickets;

use FieldList;
use FormAction;
use GatewayErrorMessage;
use Payment;
use RequiredFields;
use SilverStripe\Omnipay\GatewayInfo;
use TextareaField;

/**
 * Class SummaryForm
 *
 * @package Broarm\EventTickets
 */
class SummaryForm extends FormStep
{
    /**
     * @var Reservation
     */
    protected $reservation;

    public function __construct($controller, $name, Reservation $reservation)
    {
        $fields = FieldList::create(
            SummaryField::create('Summary', '', $this->reservation = $reservation, true),
            TextareaField::create('Comments', _t('SummaryForm.COMMENTS', 'Comments')),
            PaymentGatewayField::create(),
            TermsAndConditionsField::create('AgreeToTermsAndConditions')
        );

        $paymentLabel = $reservation->Total === 0
            ? _t('ReservationForm.RESERVE', 'Reserve tickets')
            : _t('ReservationForm.PAYMENT', 'Continue to payment');

        $actions = FieldList::create(
            FormAction::create('makePayment', $paymentLabel)
        );

        $validator = RequiredFields::create(array(
            'AgreeToTermsAndConditions'
        ));

        parent::__construct($controller, $name, $fields, $actions, $validator);

        // check if there is an error message and show it
        if ($error = $this->getPaymentErrorMessage()) {
            $this->setMessage($error, 'error');
        }

        // Update the summary form with extra fields
        $this->extend('updateSummaryForm');
    }

    /**
     * Handle the ticket form registration
     *
     * @param array       $data
     * @param SummaryForm $form
     *
     * @return string
     */
    public function makePayment(array $data, SummaryForm $form)
    {
        // If the summary is changed and email receivers are set
        if (isset($data['Summary']) && is_array($data['Summary'])) {
            foreach ($data['Summary'] as $attendeeID => $fields) {
                $attendee = $form->reservation->Attendees()->find('ID', $attendeeID);
                foreach ($fields as $field => $value) {
                    $attendee->setField($field, $value);
                }
                $attendee->write();
            }
        }

        $form->saveInto($form->reservation);
        // If comments are added
        //if (isset($data['Comments'])) {
        //    $form->reservation->Comments = $data['Comments'];
        //}

        // Hook trough where optional extra field data can be saved on the reservation
        $this->extend('updateReservationBeforePayment', $form->reservation, $data, $form);

        // Check if there is a payment to process otherwise continue with manual processing
        $gateway = $form->reservation->Total > 0
            ? $data['Gateway']
            : 'Manual';

        $form->reservation->changeState('PENDING');
        $form->reservation->Gateway = $gateway;
        $form->reservation->write();

        $paymentProcessor = PaymentProcessor::create($this->reservation);
        $paymentProcessor
            ->createPayment($gateway)
            ->setSuccessUrl($this->getController()->Link($this->nextStep))
            ->setFailureUrl($this->getController()->Link())
            ->write();

        $paymentProcessor->setGateWayData(array(
            'transactionId' => $this->reservation->ReservationCode
        ));

        $response = $paymentProcessor->createServiceFactory();
        $this->extend('beforeNextStep', $data, $form, $response);
        return $response->redirectOrRespond();
    }


    /**
     * Get the last error message from the payment attempts
     *
     * @return bool|string
     */
    public function getPaymentErrorMessage()
    {
        /** @var Payment $lastPayment */
        // Get the last payment
        if (!$lastPayment = $this->reservation->Payments()->first()) {
            return false;
        }

        // Find the gateway error
        $lastErrorMessage = null;
        $errorMessages = $lastPayment->Messages()->exclude('Message', '')->sort('Created', 'DESC');
        foreach ($errorMessages as $errorMessage) {
            if ($errorMessage instanceof GatewayErrorMessage) {
                $lastErrorMessage = $errorMessage;
                break;
            }
        }

        // If no error is found return
        if (!$lastErrorMessage) {
            return false;
        }

        return _t("{$lastErrorMessage->Gateway}.{$lastErrorMessage->Code}", $lastErrorMessage->Message);
    }

}