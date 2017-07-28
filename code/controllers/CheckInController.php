<?php
/**
 * CheckInController.php
 *
 * @author Bram de Leeuw
 * Date: 07/04/17
 */

namespace Broarm\EventTickets;

use Page_Controller;
use Permission;
use PermissionProvider;
use Security;
use SSViewer;

/**
 * Class CheckInController
 * @mixin TicketExtension
 *
 * @package Broarm\EventTickets
 */
class CheckInController extends Page_Controller implements PermissionProvider
{
    const NO_CODE = -3;
    const NO_ATTENDEES = -2;
    const CODE_NOT_FOUND = -1;
    const ALREADY_CHECKED_IN = 0;
    const SUCCESS = 1;

    private static $allowed_actions = array(
        'CheckInForm'
    );

    public function init()
    {
        $params = $this->getURLParams();
        $success = $this->getRequest()->getVar('success');
        
        // Check if the current user has permissions to check in guest
        if (!Permission::check('HANDLE_CHECK_IN')) {
            Security::permissionFailure();
            parent::init();

        // check if an id is set, then validate it
        } elseif (isset($params['ID']) && !in_array($params['ID'], self::config()->get('allowed_actions'))) {
            $form = CheckInForm::create($this);
            $form->doCheckIn(array('TicketCode' => $params['ID']), $form);

        } else {
            parent::init();
        }
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
     * Get the checked in count for display in templates
     *
     * @return string
     */
    public function getCheckedInCount()
    {
        $attendees = $this->Attendees();
        $checkedIn = $attendees->filter('CheckedIn', true)->count();
        return "($checkedIn/{$attendees->count()})";
    }

    /**
     * Force the controller action
     *
     * @param string $action
     *
     * @return SSViewer
     */
    public function getViewer($action)
    {
        if ($action === 'index') {
            $action = 'checkin';
        }

        return parent::getViewer($action);
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
        if (!$action) {
            $action = 'checkin';
        }

        return $this->dataRecord->RelativeLink($action);
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
}
