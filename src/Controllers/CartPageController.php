<?php

namespace Broarm\EventTickets\Controllers;

use Broarm\EventTickets\Forms\CartForm;
use PageController;

class CartPageController extends PageController
{   
    private static $allowed_actions = [
        'CartForm'
    ];

    public function init()
    {
        parent::init();
    }

    public function CartForm()
    {
        return CartForm::create($this, 'CartForm');
    }
}
