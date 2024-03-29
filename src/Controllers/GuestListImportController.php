<?php

namespace Broarm\EventTickets\Controllers;

use Broarm\EventTickets\Dev\GuestListBulkLoader;
use Broarm\EventTickets\Model\Attendee;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\FileField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\LiteralField;

class GuestListImportController extends Controller
{
    private static $url_segment = 'importguestlist';

    private static $allowed_actions = [
        'index' => 'CMS_ACCESS',
        'GuestListUploadForm' => 'CMS_ACCESS'
    ];

    protected $template = "BlankPage";

    /**
     * DiscountCodesUploadForm - create the form for the upload
     *
     * @param  int $target = null this is the target ID of the relation. Given when the form is defined for the button
     * @return Form
     */
    public function GuestListUploadForm($target = null)
    {
        $description = _t(__CLASS__ . '.Description', 'Use the fields: {fields}', null, [
            'fields' => implode(', ', GuestListBulkLoader::config()->get('required_columns') ?? [])
        ]);

        $fields = FieldList::create([
            FileField::create('File', false),
            LiteralField::create('Description', "<p>$description</p>"),
            CheckboxField::create('SendTickets', _t(__CLASS__ . '.SendTickets', 'Send tickets after import')),
            HiddenField::create(
                'Target',
                'Target'
            )->setValue($target)
        ]);

        $actions = FieldList::create([
            FormAction::create('doUpload', 'Upload')
                ->addExtraClass('btn btn-outline-secondary font-icon-upload')
        ]);

        return Form::create($this, 'GuestListUploadForm', $fields, $actions);
    }

    /**
     * doUpload - resolve the upload using the custom bulkloader
     *
     * @param  array $data the data from the form (contains the target relation)
     * @param  Form  $form the original form
     * @return HTTPResponse
     */
    public function doUpload($data, $form)
    {
        $sendTickets = isset($data['SendTickets']) ? $data['SendTickets'] : false;
        $loader = new GuestListBulkLoader(Attendee::class, $data['Target'], $sendTickets);
        $results = $loader->load($_FILES['File']['tmp_name']);

        $messages = [];
        if ($results->CreatedCount()) {
            $messages[] = sprintf('Imported %d items', $results->CreatedCount());
        }
        
        if ($results->UpdatedCount()) {
            $messages[] = sprintf('Updated %d items', $results->UpdatedCount());
        }
        
        if ($results->DeletedCount()) {
            $messages[] = sprintf('Deleted %d items', $results->DeletedCount());
        }
        
        if (!$messages) {
            $messages[] = 'No changes';
        }

        $form->sessionMessage(implode(', ', $messages), 'good');
        return $this->redirectBack();
    }
}
