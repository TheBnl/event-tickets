<?php

namespace Broarm\EventTickets\Controllers;


use Broarm\EventTickets\Extensions\TicketExtension;
use Broarm\EventTickets\Forms\CheckInForm;
use Broarm\EventTickets\Forms\CheckInValidator;
use Broarm\EventTickets\Model\Attendee;
use Exception;
use PageController;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse as HTTPResponseAlias;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;

/**
 * Class CheckInController
 *
 * @mixin TicketExtension
 *
 * @package Broarm\EventTickets
 */
class CheckInController extends ContentController implements PermissionProvider
{   
    const NO_CODE = -3;
    const NO_ATTENDEES = -2;
    const CODE_NOT_FOUND = -1;
    const ALREADY_CHECKED_IN = 0;
    const SUCCESS = 1;

    private static $allowed_actions = array(
        'CheckInForm',
        'ticket',
        'event',
        'attendees'
    );

    public function event(HTTPRequest $request)
    {
        $eventId = $request->param('ID');
        if (!$eventId) {
            $this->redirect($this->Link());
        }
        
        $eventPage = SiteTree::get_by_id($eventId);
        if (!$eventPage || !$eventPage->exists()) {
            $this->httpError(404);
        }

        $eventData = json_encode([
            'title'=> $eventPage->Title,
            'id'=> $eventPage->ID
        ]);
        Requirements::insertHeadTags("<script>window.event=$eventData</script>");

        return [];
    }

    /**
     * Add a ticket action for a cleaner API
     *
     * @return HTTPResponseAlias|void
     * @throws HTTPResponse_Exception
     */
    public function ticket() 
    {
        if (!Permission::check('HANDLE_CHECK_IN')) {
            Security::permissionFailure();
        }

        $params = $this->getURLParams();
        if (isset($params['ID'])) {
            $form = CheckInForm::create($this);
            return $form->doCheckIn(array('TicketCode' => $params['ID']), $form);
        }

        return $this->httpError(404);
    }

    public function attendees(HTTPRequest $request)
    {
        $eventId = $request->param('ID');
        if (!$eventId) {
            return json_encode(['attendees' => []]);
        }
        
        $eventPage = SiteTree::get_by_id($eventId);
        if (!$eventPage || !$eventPage->exists()) {
            return json_encode(['attendees' => []]);
        }

        $attendees = array_map(function(Attendee $attendee) {
            return [
                'ticket' => $attendee->TicketCode,
                'name' => $attendee->getName(),
                'checkedIn' => $attendee->CheckedIn,
                'checkedInNice' => $attendee->dbObject('CheckedIn')->Nice(),
                'checkinLink' => $attendee->getCheckInLink(),
                'allowCheckout' => CheckInValidator::config()->get('allow_checkout'),
                '_rowVariant' => $attendee->CheckedIn ? 'success' : '',
            ];
        }, $eventPage->getGuestList()->Sort('Title ASC')->toArray());

        return json_encode(['attendees' => $attendees]);
    }

    /**
     * Get the check in form
     *
     * @return CheckInForm
     */
    public function CheckInForm()
    {
        return new CheckInForm($this);
    }

    /**
     * Get a relative link to the current controller
     * Needed to handle the form
     *
     * @param null $action
     *
     * @return string
     */
    public function Link($action = null)
    {
        $link = Controller::join_links(Director::baseURL(), "checkin", $action);
        $this->extend('updateLink', $link, $action);
        return $link;
    }

    /**
     * Provide permissions required for ticket check in
     *
     * @return array
     */
    public function providePermissions()
    {
        return array(
            'HANDLE_CHECK_IN' => array(
                'name' => _t('TicketControllerExtension.HANDLE_CHECK_IN', 'Is authorized to handle ticket check in'),
                'category' => _t('TicketControllerExtension.PERMISSIONS_CAT', 'Event tickets'),
            )
        );
    }

    /**
     * Here for legacy app support
     *
     * @return HTTPResponseAlias|void
     * @throws Exception
     * @deprecated use the ticket action when checking user in trough url
     */
    public function init()
    {
        // Check if the current user has permissions to check in guest
        if (!Permission::check('HANDLE_CHECK_IN')) {
            Security::permissionFailure();
        }
        
        // tmp test lib
        // $script = Director::baseFolder() . '/vendor/bramdeleeuw/silverstripe-event-tickets/client/dist/js/checkin.js';
        // Requirements::customScript(file_get_contents($script));

        parent::init();
    }
}
