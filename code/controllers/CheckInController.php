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
 *
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
        'CheckInForm',
        'ticket'
    );

    /**
     * Add a ticket action for a cleaner API
     *
     * @return \SS_HTTPResponse|void
     */
    public function ticket() {
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

    /**
     * Here for legacy app support
     *
     * @deprecated use the ticket action when checking user in trough url
     * @return \SS_HTTPResponse|void
     * @throws \Exception
     */
    public function init()
    {
        // Check if the current user has permissions to check in guest
        if (!Permission::check('HANDLE_CHECK_IN')) {
            Security::permissionFailure();
        }

        // Here for legacy support
        $params = $this->getURLParams();
        if (isset($params['ID']) && !in_array($params['ID'], self::config()->get('allowed_actions'))) {
            $form = CheckInForm::create($this);
            $form->doCheckIn(array('TicketCode' => $params['ID']), $form);
            $this->redirect($this->Link());
        }

        parent::init();
    }
}
