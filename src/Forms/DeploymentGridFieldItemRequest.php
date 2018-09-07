<?php
namespace WebbuildersGroup\DeploymentNotes\Forms;

use SilverStripe\Forms\Form;
use SilverStripe\CMS\Controllers\SilverStripeNavigator;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Control\Director;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;


class DeploymentGridFieldItemRequest extends GridFieldDetailForm_ItemRequest {
    private static $allowed_actions=array(
                                        'ItemEditForm'
                                    );

    /**
     * Gets the form used for editing resources
     * @return ResourceForm Resource form instance
     */
    public function ItemEditForm() {
        $form=parent::ItemEditForm();

        if(!($form instanceof Form)) {
            return $form;
        }

        //Add the navigator if it doesn't exist
        if(!$form->Fields()->fieldByName('SilverStripeNavigator') && $this->record->exists() && class_exists(SilverStripeNavigator::class)) {
            $navField = new LiteralField('SilverStripeNavigator', $this->getSilverStripeNavigator());
            $navField->setAllowHTML(true);
            $form->Fields()->push($navField);
            
            $form->addExtraClass('cms-previewable');
            $form->setTemplate(array(
                                    'type'=>'Includes',
                                    'WebbuildersGroup\\DeploymentNotes\\DeploymentNoteEditForm'
                                ));
        }


        return $form;
    }

    /**
     * Used for preview controls, mainly links which switch between different states of the page.
     * @return ArrayData
     */
    protected function getSilverStripeNavigator($segment=null) {
        if($this->record && class_exists(SilverStripeNavigator::class)) {
            $navigator=new SilverStripeNavigator($this->record);
            return $navigator->renderWith($this->getToplevelController()->getTemplatesWithSuffix('_SilverStripeNavigator'));
        }else {
            return false;
        }
    }
     
    /**
     * Gets the preview link
     * @return string Link to view the record
     */
    public function LinkPreview() {
        return $this->record->Link();
    }

    /**
     * Gets the absolute link to this item request
     * @param string $action Action to be added to the url
     * @return string
     */
    public function AbsoluteLink($action=null)  {
        return Director::absoluteURL($this->Link($action));
    }
    
    /**
     * Render $PreviewPanel content
     * @return DBHTMLText
     */
    public function PreviewPanel() {
        $template = $this->getTopLevelController()->getTemplatesWithSuffix('_PreviewPanel');
        // Only render sections with preview panel
        if ($template) {
            return $this->renderWith($template);
        }
        return null;
    }
}
?>