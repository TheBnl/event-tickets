<?php

namespace Broarm\EventTickets\Model;

use Broarm\EventTickets\Controllers\CheckoutPageController;
use Broarm\EventTickets\Session\ReservationSession;
use Page;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Parsers\URLSegmentFilter;

class CheckoutPage extends Page
{
    private static $table_name = 'EventTickets_CheckoutPage';

    private static $icon_class = 'font-icon-credit-card';

    private static $defaults = [
        'ShowInMenus' => false,
        'ShowInSearch' => false,
    ];

    private static $controller_name = CheckoutPageController::class;

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
            $title = _t(__CLASS__ . '.Title', 'Checkout');
            $page = CheckoutPage::create();
            $page->Title = $title;
            $page->URLSegment = URLSegmentFilter::create()->filter($title);
            $page->write();
            $page->copyVersionToStage(Versioned::DRAFT, Versioned::LIVE);
            $page->flushCache();
            DB::alteration_message('Checkout page created', 'created');
        }
    }

    public static function inst()
    {
        return DataObject::get_one(__CLASS__);
    }
}
