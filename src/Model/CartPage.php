<?php

namespace Broarm\EventTickets\Model;

use Broarm\EventTickets\Controllers\CartPageController;
use Broarm\EventTickets\Session\ReservationSession;
use Page;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Parsers\URLSegmentFilter;

class CartPage extends Page
{
    private static $table_name = 'EventTickets_CartPage';

    private static $icon_class = 'font-icon-p-cart';
    
    private static $defaults = [
        'ShowInMenus' => false,
        'ShowInSearch' => false,
    ];

    private static $controller_name = CartPageController::class;

    public function getReservation()
    {
        return ReservationSession::get();
    }

    public function canCreate($member = null, $context = [])
    {
        $cartMode = ReservationSession::config()->get('cart_mode');
        $page = self::inst();
        return $cartMode && (!$page || !$page->exists());
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        if ($this->canCreate()) {
            $title = _t(__CLASS__ . '.Title', 'Cart');
            $page = CartPage::create();
            $page->Title = $title;
            $page->URLSegment = URLSegmentFilter::create()->filter($title);
            $page->write();
            $page->copyVersionToStage(Versioned::DRAFT, Versioned::LIVE);
            $page->flushCache();
            DB::alteration_message('Cart page created', 'created');
        }
    }

    public static function inst()
    {
        return DataObject::get_one(__CLASS__);
    }
}
