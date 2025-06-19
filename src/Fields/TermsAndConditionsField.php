<?php

namespace Broarm\EventTickets\Fields;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\ORM\FieldType\DBHTMLText;

class TermsAndConditionsField extends CheckboxField
{
    protected $fieldHolderTemplate = 'TermsAndConditionsField_holder';

    public function __construct($name, SiteTree $termsPage)
    {
        parent::__construct($name, DBHTMLText::create()->setValue(_t(
            __CLASS__ . '.TermsAndConditions',
            "I agree to the terms and conditions stated on the <a href='{link}' target='new' title='Read the shop terms and conditions for this site'>{title}</a> page",
            null,
            ['link' => $termsPage->Link(), 'title' => $termsPage->Title]
        )), null);
    }

    public function getCustomValidationMessage()
    {
        return _t(__CLASS__ . '.Agree', 'You must agree to the terms and conditions');
    }
}
