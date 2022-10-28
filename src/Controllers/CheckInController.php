<?php

namespace Broarm\EventTickets\Controllers;

use Broarm\EventTickets\Extensions\TicketExtension;
use Broarm\EventTickets\Forms\CheckInForm;
use Broarm\EventTickets\Forms\CheckInValidator;
use Broarm\EventTickets\Model\Attendee;
use Exception;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse as HTTPResponseAlias;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;

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

    private static $checkin_table_fields = [
        'TicketCode' => 'Ticket',
        'Name' =>'Name',
        'CheckedIn.Nice' => 'Checked In',
        'CheckinLink' => '',
    ];

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

        $tableFields = self::config()->get('checkin_table_fields') ?? [];
        array_walk($tableFields, function(&$value, $key) {
            $value = [
                'key' => lcfirst(str_replace('.', '', $key)),
                'label' => $value
            ];
        });
        $tableFields = json_encode(array_values($tableFields));
        
        Requirements::insertHeadTags("<script>window.tableFields=$tableFields</script>");

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

        $attendees =  $eventPage->getGuestList()->Sort('Title ASC');
        if ($filter = $request->getVar('filter')) {
            $filterFields = [];
            $tableFields = self::config()->get('checkin_table_fields') ?? [];
            foreach ($tableFields as $key => $value) {
                if (strpos($key, '.') !== false) {
                    $key = explode('.', $key)[0];
                }

                if (Attendee::singleton()->hasDatabaseField($key)) {
                    $filterFields["$key:PartialMatch"] = $filter;
                }
            }

            $attendees = $attendees->filterAny($filterFields);
        }

        $tableFields = self::config()->get('checkin_table_fields');
        $attendees = array_map(function(Attendee $attendee) use ($tableFields) {
            $data = [];
            foreach ($tableFields as $field => $label) {
                $key = lcfirst(str_replace('.', '', $field));
                if (strpos($field, '.') !== false) {
                    $fieldParts = explode('.', $field);
                    $field = $fieldParts[0];
                    $method = $fieldParts[1];
                    $data[$key] = $attendee->dbObject($field)->{$method}();
                } else {
                    $data[$key] = $attendee->{$field};
                }
            }

            $data['allowCheckout'] = CheckInValidator::config()->get('allow_checkout');
            $data['checkedIn'] = $attendee->CheckedIn;
            $data['_rowVariant'] = $attendee->CheckedIn ? 'success' : '';
            return $data;
        }, $attendees->toArray());

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
