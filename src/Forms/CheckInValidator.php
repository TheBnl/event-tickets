<?php

namespace Broarm\EventTickets\Forms;

use Broarm\EventTickets\Model\Attendee;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;

/**
 * CheckInValidator.php
 *
 * @author Bram de Leeuw
 * Date: 14/06/2017
 */
class CheckInValidator
{
    use Configurable;
    use Extensible;

    const MESSAGE_ERROR = 'MESSAGE_ERROR';
    const MESSAGE_NO_CODE = 'MESSAGE_NO_CODE';
    const MESSAGE_CODE_NOT_FOUND = 'MESSAGE_CODE_NOT_FOUND';
    const MESSAGE_TICKET_CANCELLED = 'MESSAGE_TICKET_CANCELLED';
    const MESSAGE_ALREADY_CHECKED_IN = 'MESSAGE_ALREADY_CHECKED_IN';
    const MESSAGE_CHECK_OUT_SUCCESS = 'MESSAGE_CHECK_OUT_SUCCESS';
    const MESSAGE_CHECK_IN_SUCCESS = 'MESSAGE_CHECK_IN_SUCCESS';

    const MESSAGE_TYPE_GOOD = 'Good';
    const MESSAGE_TYPE_WARNING = 'Warning';
    const MESSAGE_TYPE_BAD = 'Bad';

    /**
     * Allow people to check in and out
     *
     * @config
     * @var bool
     */
    private static $allow_checkout = false;

    /**
     * @var Attendee
     */
    protected $attendee = null;

    /**
     * Validate the given ticket code
     *
     * @param null|string $ticketCode
     *
     * @return array
     */
    public function validate($ticketCode = null)
    {
        if (filter_var($ticketCode, FILTER_VALIDATE_URL)) {
            $asURL = explode('/', parse_url($ticketCode, PHP_URL_PATH));
            $ticketCode = end($asURL);
        }

        // Check if a code is given to the validator
        if (!isset($ticketCode)) {
            return array(
                'Code' => self::MESSAGE_NO_CODE,
                'Message' => self::message(self::MESSAGE_NO_CODE, $ticketCode),
                'Type' => self::MESSAGE_TYPE_BAD,
                'Ticket' => $ticketCode,
                'Attendee' => null
            );
        }

        // Check if a ticket exists with the given ticket code
        if (!$this->attendee = Attendee::get()->find('TicketCode', $ticketCode)) {
            return array(
                'Code' => self::MESSAGE_CODE_NOT_FOUND,
                'Message' => self::message(self::MESSAGE_CODE_NOT_FOUND, $ticketCode),
                'Type' => self::MESSAGE_TYPE_BAD,
                'Ticket' => $ticketCode,
                'Attendee' => null
            );
        } else {
            $name = $this->attendee->getName();
        }

        // Check if the reservation is not canceled
        if (!(bool)$this->attendee->TicketPage()->getGuestList()->find('ID', $this->attendee->ID)) {
            return array(
                'Code' => self::MESSAGE_TICKET_CANCELLED,
                'Message' => self::message(self::MESSAGE_TICKET_CANCELLED, $name),
                'Type' => self::MESSAGE_TYPE_BAD,
                'Ticket' => $ticketCode,
                'Attendee' => $this->attendee
            );
        }

        // Check if the ticket is already checked in and not allowed to check out
        elseif ((bool)$this->attendee->CheckedIn && !(bool)self::config()->get('allow_checkout')) {
            return array(
                'Code' => self::MESSAGE_ALREADY_CHECKED_IN,
                'Message' => self::message(self::MESSAGE_ALREADY_CHECKED_IN, $name),
                'Type' => self::MESSAGE_TYPE_BAD,
                'Ticket' => $ticketCode,
                'Attendee' => $this->attendee
            );
        }

        // Successfully checked out
        elseif ((bool)$this->attendee->CheckedIn && (bool)self::config()->get('allow_checkout')) {
            return array(
                'Code' => self::MESSAGE_CHECK_OUT_SUCCESS,
                'Message' => self::message(self::MESSAGE_CHECK_OUT_SUCCESS, $name),
                'Type' => self::MESSAGE_TYPE_WARNING,
                'Ticket' => $ticketCode,
                'Attendee' => $this->attendee
            );
        }

        // Successfully checked in
        else {
            return array(
                'Code' => self::MESSAGE_CHECK_IN_SUCCESS,
                'Message' => self::message(self::MESSAGE_CHECK_IN_SUCCESS, $name),
                'Type' => self::MESSAGE_TYPE_GOOD,
                'Ticket' => $ticketCode,
                'Attendee' => $this->attendee
            );
        }
    }

    /**
     * Get the attendee instance
     *
     * @return Attendee
     */
    public function getAttendee()
    {
        return $this->attendee;
    }

    /**
     * Translate the given type to a readable message
     *
     * @param $message string
     * @param $ticket string
     *
     * @return string
     */
    private static function message($message, $ticket = null) {
        return _t(__CLASS__ . ".$message", $message, null, array('ticket' => $ticket));
    }
}
