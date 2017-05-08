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
use SiteConfig;

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
     * The address to whom the ticket notifications are sent
     * By default the admin email is used
     *
     * @config
     * @var string
     */
    private static $mail_sender;

    /**
     * The address from where the ticket mails are sent
     * By default the admin email is used
     *
     * @config
     * @var string
     */
    private static $mail_receiver;

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
                    $this->sendTickets();
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
        if (empty($from = self::config()->get('mail_sender'))) {
            $from = Config::inst()->get('Email', 'admin_email');
        }

        // Create the email with given template and reservation data
        $email = new Email();
        $email->setSubject(_t(
            'ReservationMail.TITLE',
            'Your order at {sitename}',
            null,
            array(
                'sitename' => SiteConfig::current_site_config()->Title
            )
        ));
        $email->setFrom($from);
        $email->setTo($this->reservation->MainContact()->Email);
        $email->setTemplate('ReservationMail');
        $email->populateTemplate($this->reservation);
        $this->extend('updateReservationMail', $email);
        $email->send();
        return true;
    }

    /**
     * Send the reservation mail
     */
    public function sendTickets()
    {
        // Get the mail sender or fallback to the admin email
        if (empty($from = self::config()->get('mail_sender'))) {
            $from = Config::inst()->get('Email', 'admin_email');
        }

        // Send the tickets to the main contact
        $email = new Email();
        $email->setSubject(_t(
            'MainContactMail.TITLE',
            'Uw tickets voor {event}',
            null,
            array(
                'event' => $this->reservation->Event()->Title
            )
        ));
        $email->setFrom($from);
        $email->setTo($this->reservation->MainContact()->Email);
        $email->setTemplate('MainContactMail');
        $email->populateTemplate($this->reservation);
        $this->extend('updateMainContactMail', $email);
        $email->send();


        // Get the attendees for this event that are checked as receiver
        $ticketReceivers = $this->reservation->Attendees()->filter('TicketReceiver', 1)->exclude('ID', $this->reservation->MainContactID);
        if ($ticketReceivers->exists()) {
            /** @var Attendee $ticketReceiver */
            foreach ($ticketReceivers as $ticketReceiver) {
                $email = new Email();
                $email->setSubject(_t(
                    'AttendeeMail.TITLE',
                    'Your ticket for {event}',
                    null,
                    array(
                        'event' => $this->reservation->Event()->Title
                    )
                ));
                $email->setFrom($from);
                $email->setTo($ticketReceiver->Email);
                $email->setTemplate('AttendeeMail');
                $email->populateTemplate($ticketReceiver);
                $this->extend('updateTicketMail', $email);
                $email->send();
            }
        }
    }


    /**
     * Send a booking notification tot the ticket mail sender or the site admin
     */
    public function sendNotification()
    {
        if (empty($from = self::config()->get('mail_sender'))) {
            $from = Config::inst()->get('Email', 'admin_email');
        }

        if (empty($to = self::config()->get('mail_receiver'))) {
            $to = Config::inst()->get('Email', 'admin_email');
        }

        $email = new Email();
        $email->setSubject(_t(
            'NotificationMail.TITLE',
            'Nieuwe reservering voor {event}',
            null, array('event' => $this->reservation->Event()->Title)
        ));

        $email->setFrom($from);
        $email->setTo($to);
        $email->setTemplate('NotificationMail');
        $email->populateTemplate($this->reservation);
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
        return $this->reservation->Attendees()->first()->TicketFile()->Link();
    }
}
