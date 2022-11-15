<?php

namespace Broarm\EventTickets\Model;

use Broarm\EventTickets\Model\Buyable;
use Broarm\EventTickets\Model\Reservation;
use SilverStripe\ORM\DataObject;

class OrderItem extends DataObject
{
    private static $table_name = 'EventTickets_OrderItem';
    
    private static $db = [
        'Price' => 'Currency',
        'Total' => 'Currency',
        'Amount' => 'Int'
    ];

    private static $has_one = [
        'Reservation' => Reservation::class,
        'Buyable' => Buyable::class
    ];

    private static $summary_fields = [
        'Buyable.Title' => 'Product',
        'Price.Nice' => 'Price',
        'Amount' => 'Amount',
        'Total.Nice' => 'Total',
    ];
    
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->updateTotal();
    }

    public function updateTotal()
    {
        $this->Total = $this->Price * $this->Amount;
    }
}
