<?php
/**
 * TermsAndConditionsField
 *
 * @author Bram de Leeuw
 */

namespace Broarm\EventTickets;

use CheckboxField;
use SiteConfig;

class TermsAndConditionsField extends CheckboxField
{
    protected $fieldHolderTemplate = 'TermsAndConditionsField_holder';

    /**
     * @var \Page
     */
    protected $termsPage;

    public function __construct($name)
    {
        if (($this->termsPage = SiteConfig::current_site_config()->TermsPage()) && $this->termsPage->exists()) {
            parent::__construct($name, _t(
                'TermsAndConditionsField.TERMS_CONDITIONS',
                "I agree to the terms and conditions stated on the <a href='{link}' target='new' title='Read the shop terms and conditions for this site'>{title}</a> page",
                null,
                array('link' => $this->termsPage->Link(), 'title' => $this->termsPage->Title)
            ), $value = null);
        }
    }

    public function getCustomValidationMessage()
    {
        return _t('TermsAndConditionsField.AGREE_TO_TERMS', 'You must agree to the terms and conditions');
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