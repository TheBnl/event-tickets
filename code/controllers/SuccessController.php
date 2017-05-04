<?php
/**
 * SuccessController.php
 *
 * @author Bram de Leeuw
 * Date: 16/03/17
 */

namespace Broarm\EventTickets;

use Config;
use Email;

/**
 * Class SuccessController
 *
 * @package Broarm\EventTickets
 */
class SuccessController extends CheckoutStepController
{
    protected $step = 'success';

    /**
     * @var Reservation
     */
    protected $reservation;

    /**
     * @config
     * @var string
     */
    private static $mail_sender;

    public function init()
    {
        parent::init();

        if ($this->reservation = ReservationSession::get()) {
            // If we get to the success controller form any state except PENDING or PAID
            // This would mean someone would be clever and change the url from summary to success bypassing the payment
            // End the session, thus removing the reservation, and redirect back
            if (!in_array($this->reservation->Status, array('PENDING', 'PAID'))) {
                ReservationSession::end();
                $this->redirect($this->Link('/'));
            } else {
                $this->reservation->createFiles();
                if ($this->sendReservation()) {
                    $this->reservation->changeState('PAID');
                    $this->sendNotification();
                    $this->extend('afterPaymentComplete', $this->reservation);
                    $this->reservation->write();
                }
            }
        }
    }

    /**
     * Send the reservation mail
     *
     * @return bool
     */
    public function sendReservation()
    {
        // State changes after the files have been sent
        // this check makes sure the files aren't sent again after a refresh
        if ($this->reservation->Status === 'PAID') {
            return false;
        }

        // Get the mail sender or fallback to the admin email
        if (empty($from = self::config()->get('ticket_mail_sender'))) {
            $from = Config::inst()->get('Email', 'admin_email');
        }

        // Get the attendees for this event that are checked as receiver
        $ticketReceivers = $this->reservation->Attendees()->filter('TicketReceiver', 1);
        if ($ticketReceivers->exists()) {
            $receivers = implode(',', $ticketReceivers->column('Email'));
        } else {
            $receivers = $this->reservation->MainContact()->Email;
        }

        // Create the email with given template and reservation data
        $email = new Email();
        $email->setSubject(_t('TicketEmail.Title', 'Your tickets'));
        $email->setFrom($from);
        $email->setTo($receivers);
        $email->setTemplate('ReservationMail');
        $email->populateTemplate($this->reservation->getViewableData());
        $this->extend('updateTicketMail', $email);
        $email->send();
        return true;
    }

    /**
     * Send a booking notification tot the ticket mail sender or the site admin
     */
    public function sendNotification()
    {
        if (empty($from = self::config()->get('ticket_mail_sender'))) {
            $from = Config::inst()->get('Email', 'admin_email');
        }

        if (empty($to = self::config()->get('mail_reciever'))) {
            $to = Config::inst()->get('Email', 'admin_email');
        }

        $email = new Email();
        $email->setSubject(_t(
            'TicketEmail.Notification',
            'New ticket order {order}',
            null, array('order' => $this->reservation->ReservationCode)
        ));
        $email->setFrom($from);
        $email->setTo($to);
        $email->setTemplate('NotificationMail');
        $email->populateTemplate($this->reservation->getViewableData());
        $this->extend('updateNotificationMail', $email);
        $email->send();
    }

    /**
     * Get the download link
     *
     * @return string
     */
    public function getDownloadLink()
    {
        return $this->reservation->TicketFile()->Link();
    }
}
