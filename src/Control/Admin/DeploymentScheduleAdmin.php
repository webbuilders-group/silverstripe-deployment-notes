<?php
namespace WebbuildersGroup\DeploymentNotes\Control\Admin;

use WebbuildersGroup\DeploymentNotes\Forms\DeploymentGridFieldItemRequest;
use WebbuildersGroup\DeploymentNotes\Model\DeploymentNote;
use SilverStripe\Core\Config\Config;
use SilverStripe\Security\Permission;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Admin\ModelAdmin;


/**
 * Class DeploymentScheduleAdmin
 *
 */
class DeploymentScheduleAdmin extends ModelAdmin {
    private static $menu_icon='deployment-notes/images/menu-icons/deployment-schedule-admin.png';
    private static $url_segment='deployment-schedule';
    private static $managed_models=array(
                                        DeploymentNote::class
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
            $oldValue=Config::inst()->get(Permission::class, 'admin_implies_all');
            
            
            //Disable the Permission.admin_implies_all
            Config::inst()->update(Permission::class, 'admin_implies_all', false);
            
            
            //Look to the normal permission checking
            $result=parent::canView($member);
            
            
            //Restore the value for Permission.admin_implies_all
            Config::inst()->update(Permission::class, 'admin_implies_all', $oldValue);
            
            
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
        
        
        $form->Fields()->dataFieldByName(str_replace('\\', '-', DeploymentNote::class))
                                                        ->getConfig()
                                                            ->getComponentByType(GridFieldDetailForm::class)
                                                                ->setItemRequestClass(DeploymentGridFieldItemRequest::class);
        
        
        return $form;
    }
}
?>