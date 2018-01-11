<?php
/**
 * Class DeploymentScheduleAdmin
 *
 */
class DeploymentScheduleAdmin extends ModelAdmin {
    private static $menu_icon='deployment-notes/images/menu-icons/deployment-schedule-admin.png';
    private static $url_segment='deployment-schedule';
    private static $managed_models=array(
                                        'DeploymentNote'
                                    );
    
    public $showImportForm=false;
    
    
    /**
     * Enables strict permission checking for the admin
     * @var bool
     * @config DeploymentScheduleAdmin.strict_permission_check
     */
    private static $strict_permission_check=true;
    
    
    /**
     * Checks to see if the member has the correct permission ignoring admin implies all
     * @param Member|int $member Member instance or id
     * @return bool
     */
    public function canView($member=null) {
        if($this->config()->strict_permission_check) {
            //Get the old value for Permission.admin_implies_all
            $oldValue=Config::inst()->get('Permission', 'admin_implies_all');
            
            
            //Disable the Permission.admin_implies_all
            Config::inst()->update('Permission', 'admin_implies_all', false);
            
            
            //Look to the normal permission checking
            $result=parent::canView($member);
            
            
            //Restore the value for Permission.admin_implies_all
            Config::inst()->update('Permission', 'admin_implies_all', $oldValue);
            
            
            return $result;
        }
        
        
        return parent::canView($member);
    }
    
    /**
     * Gets the edit form
     * @param string $id Form ID
     * @param FieldList $fields Fields to use in the form
     * @return Form
     */
    public function getEditForm($id=null, $fields=null) {
        $form=parent::getEditForm();
        
        
        $form->Fields()->dataFieldByName('DeploymentNote')
                                                        ->getConfig()
                                                            ->getComponentByType('GridFieldDetailForm')
                                                                ->setItemRequestClass('DeploymentGridField_ItemRequest');
        
        
        return $form;
    }
}

class DeploymentGridField_ItemRequest extends GridFieldDetailForm_ItemRequest {
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
        if(!$form->Fields()->fieldByName('SilverStripeNavigator') && $this->record->exists()) {
            $navField=LiteralField::create('SilverStripeNavigator', $this->getSilverStripeNavigator())->setForm($form)->setAllowHTML(true);
            $form->Fields()->push($navField);
             
            $form->addExtraClass('cms-previewable');
            $form->setTemplate('PreviewItemEditForm');
        }


        return $form;
    }

    /**
     * Used for preview controls, mainly links which switch between different states of the page.
     * @return ArrayData
     */
    protected function getSilverStripeNavigator($segment=null) {
        if($this->record) {
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
}

class DeploymentNavigatorItem_LiveLink extends SilverStripeNavigatorItem {
    /**
     * Checks to see if the record is an instance of a deployment note or not
     * @return bool
     */
    public function canView($member=null) {
        return ($this->record instanceof DeploymentNote);
    }
    
    /**
     * Returns the title for this link
     * @return string
     */
    public function getTitle() {
        return _t('DeploymentScheduleAdmin.PREVIEW', '_Preview');
    }
    
    /**
     * Gets the html for rendering this link
     * @return string
     */
    public function getHTML() {
        $this->recordLink = Controller::join_links($this->record->AbsoluteLink());
        return '<a '.($this->isActive() ? 'class="current" ':'').' href="'.$this->recordLink.'">'._t('DeploymentScheduleAdmin.PREVIEW', '_Preview').'</a>';
    }
    
    /**
     * Gets the relative link to preview the record
     * @return string
     */
    public function getLink() {
        return Controller::join_links($this->record->PreviewLink());
    }
    
    /**
     * Determins whether or not this link is active or not
     * @return bool
     */
    public function isActive() {
        return true;
    }
}
?>