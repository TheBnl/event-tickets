<?php

namespace Broarm\EventTickets\Fields;

use Broarm\EventTickets\Extensions\SiteConfigExtension;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\SiteConfig\SiteConfig;

class TermsAndConditionsField extends CheckboxField
{
    protected $fieldHolderTemplate = 'TermsAndConditionsField_holder';

    /**
     * @var SiteTree
     */
    protected $termsPage;

    public function __construct($name)
    {
        /** @var SiteConfigExtension $config */
        $config = SiteConfig::current_site_config();
        if (($this->termsPage = $config->TermsPage()) && $this->termsPage->exists()) {
            parent::__construct($name, DBHTMLText::create()->setValue(_t(
                __CLASS__ . '.TermsAndConditions',
                "I agree to the terms and conditions stated on the <a href='{link}' target='new' title='Read the shop terms and conditions for this site'>{title}</a> page",
                null,
                ['link' => $this->termsPage->Link(), 'title' => $this->termsPage->Title]
            )), $value = null);
        }
    }

    public function getCustomValidationMessage()
    {
        return _t(__CLASS__ . '.Agree', 'You must agree to the terms and conditions');
    }

    /**
     * If a therms page is set render the checkbox
     *
     * @param array $properties
     *
     * @return null|string
     */
    public function FieldHolder($properties = array()) {
        if ($this->termsPage->exists()) {
            return parent::FieldHolder($properties);
        } else {
            return null;
        }
    }
}
