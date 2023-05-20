<?php

namespace Broarm\EventTickets\Admin;

use Broarm\EventTickets\Model\Reservation;
use SilverStripe\Admin\ModelAdmin;

class ReservationsAdmin extends ModelAdmin
{
    private static $managed_models = [
        Reservation::class
    ];

    private static $url_segment = 'reservations';

    private static $menu_title = 'Reservations';

    private static $menu_icon_class = 'font-icon-cart';
}
