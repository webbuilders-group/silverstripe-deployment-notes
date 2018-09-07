<?php
namespace WebbuildersGroup\DeploymentNotes\Model;

use DateTime;
use WebbuildersGroup\DeploymentNotes\Model\FieldType\Markdown;
use SilverStripe\Security\Permission;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DateField;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\CMSPreviewable;


/**
 * Class DeploymentNote
 *
 * @property string $DeploymentStart
 * @property string $DeploymentWeekEnd
 * @property string $Date
 * @property string $DeploymentNotes
 * @property boolean $Visible
 * @property boolean $OutOfCycle
 * @property boolean $CycleResetter
 * @property boolean $DowntimeRequired
 * @property int $DowntimeEstimate
 * @property string $DowntimeReason
 * @property string $Status
 */
class DeploymentNote extends DataObject implements CMSPreviewable {
    private static $db=array(
                            'DeploymentStart'=>'Date',
                            'DeploymentWeekEnd'=>'Date',
                            'Date'=>'Date',
                            'DeploymentNotes'=>Markdown::class,
                            'Visible'=>'Boolean',
                            'OutOfCycle'=>'Boolean',
                            'CycleResetter'=>'Boolean',
                            'DowntimeRequired'=>'Boolean',
                            'DowntimeEstimate'=>'Int',
                            'DowntimeReason'=>'Varchar(400)',
                            'Status'=>"Enum(array('planning', 'dev', 'staged', 'deployed'), 'planning')"
                         );
    
    private static $defaults=array(
                                    'Visible'=>false,
                                    'OutOfCycle'=>false,
                                    'DowntimeRequired'=>false,
                                    'Status'=>'planning'
                                );
    
    private static $default_sort='DeploymentWeekEnd DESC';
    
    private static $summary_fields=array(
                                        'Visible.Nice'=>'_Visible to Users',
                                        'DeploymentStart'=>'_Cycle Start Date',
                                        'DeploymentWeekEnd'=>'_Deployment Week',
                                        'Date'=>'_Actual Deployment Date',
                                        'StatusNice'=>'_Deployment Status',
                                        'OutOfCycle.Nice'=>'_Out of Cycle'
                                    );
    
    
    /**
     * Checks to see if the member can view this deployment note or not
     * @param int|Member $member Member ID or instance to check
     * @return bool Returns boolean true if the member can view this deployment note
     */
    public function canView($member=null) {
        return (
                ($this->Visible && (DeploymentScheduleAdmin::config()->view_permission_code===false || Permission::check(DeploymentScheduleAdmin::config()->view_permission_code, 'any', $member)))
                ||
                Permission::check('CMS_ACCESS_DeploymentScheduleAdmin', 'any', $member)==true
            );
    }
    
    /**
     * Checks to see if the member can create a deployment note or not
     * @param int|Member $member Member ID or instance to check
     * @return bool Returns boolean true if the member can create a deployment note
     */
    public function canCreate($member=null) {
        if(DeploymentScheduleAdmin::config()->strict_permission_check) {
            //Get the old value for Permission.admin_implies_all
            $oldValue=Config::inst()->get(Permission::class, 'admin_implies_all');
            
            
            //Disable the Permission.admin_implies_all
            Config::inst()->update(Permission::class, 'admin_implies_all', false);
        }
        
        
        $result=(Permission::check('CMS_ACCESS_DeploymentScheduleAdmin', 'any', $member)==true);
        
        
        if(DeploymentScheduleAdmin::config()->strict_permission_check) {
            //Restore the value for Permission.admin_implies_all
            Config::inst()->update(Permission::class, 'admin_implies_all', $oldValue);
        }
        
        
        return $result;
    }
    
    /**
     * Checks to see if the member can edit this deployment note or not
     * @param int|Member $member Member ID or instance to check
     * @return bool Returns boolean true if the member can edit this deployment note
     */
    public function canEdit($member=null) {
        if(DeploymentScheduleAdmin::config()->strict_permission_check) {
            //Get the old value for Permission.admin_implies_all
            $oldValue=Config::inst()->get(Permission::class, 'admin_implies_all');
            
            
            //Disable the Permission.admin_implies_all
            Config::inst()->update(Permission::class, 'admin_implies_all', false);
        }
        
        
        $result=(Permission::check('CMS_ACCESS_DeploymentScheduleAdmin', 'any', $member)==true);
        
        
        if(DeploymentScheduleAdmin::config()->strict_permission_check) {
            //Restore the value for Permission.admin_implies_all
            Config::inst()->update(Permission::class, 'admin_implies_all', $oldValue);
        }
        
        
        return $result;
    }
    
    /**
     * Checks to see if the member can delete this deployment note or not
     * @param int|Member $member Member ID or instance to check
     * @return bool Returns boolean true if the member can delete this deployment note
     */
    public function canDelete($member=null) {
        if(DeploymentScheduleAdmin::config()->strict_permission_check) {
            //Get the old value for Permission.admin_implies_all
            $oldValue=Config::inst()->get(Permission::class, 'admin_implies_all');
            
            
            //Disable the Permission.admin_implies_all
            Config::inst()->update(Permission::class, 'admin_implies_all', false);
        }
        
        
        $result=(Permission::check('CMS_ACCESS_DeploymentScheduleAdmin', 'any', $member)==true);
        
        
        if(DeploymentScheduleAdmin::config()->strict_permission_check) {
            //Restore the value for Permission.admin_implies_all
            Config::inst()->update(Permission::class, 'admin_implies_all', $oldValue);
        }
        
        
        return $result;
    }
    
    /**
     * Gets fields used in the cms
     * @return FieldList Fields to be used
     */
    public function getCMSFields() {
        $startDate=null;
        $endDate=null;
        if(!$this->exists()) {
            $prevDeploy=DeploymentNote::get()->first();
            if(!empty($prevDeploy) && $prevDeploy!==false && $prevDeploy->exists()) {
                $startDate=date('Y-m-d', strtotime($prevDeploy->DeploymentWeekEnd.' -4 days'));
                $endDate=date('Y-m-d', strtotime($startDate.' +'.DeploymentSchedule::config()->deployment_cycle_length.' weeks friday'));
            }else {
                $startDate=date('Y-m-d', strtotime('monday this week'));
                $endDate=date('Y-m-d', strtotime($startDate.' +'.DeploymentSchedule::config()->deployment_cycle_length.' weeks friday'));
            }
        }
        
        $fields=new FieldList(
                            new CheckboxField('Visible', _t('DeploymentNote.db_Visible_Nice', '_Visible to Users?')),
                            DateField::create('DeploymentStart', _t('DeploymentNote.db_DeploymentStart', '_Cycle Start Date'), $startDate)->setConfig('showcalendar', true),
                            DateField::create('DeploymentWeekEnd', _t('DeploymentNote.WEEK_END', '_Deployment Week End Date'), $endDate)->setConfig('showcalendar', true),
                            DateField::create(DBDate::class, _t('DeploymentNote.db_Date', '_Actual Deployment Date'))->setConfig('showcalendar', true),
                            new MarkdownField('DeploymentNotes', _t('DeploymentNote.DEPLOYMENT_NOTES', '_Deployment Notes'), '### '._t('DeploymentNote.PLANNED_TITLE', '_Planned Changes').":\n\n".
                                                                                    '_'._t('DeploymentNote.PLANNED_DESC', '_Planned Changes are scheduled to be included in this deployment however they maybe pushed to a future deployment.')."_\n\n".
                                                                                    '* '._t('DeploymentNote.TBA', '_TBA')."\n\n\n".
                                                                                    '### '._t('DeploymentNote.HIGH_LEVEL', '_High Level').":\n\n".
                                                                                    '* '._t('DeploymentNote.TBA', '_TBA')."\n\n\n".
                                                                                    '### '._t('DeploymentNote.BUGS_BEHIND_MINOR', '_Bug Fixes/Behind the Scenes/Minor').":\n\n".
                                                                                    '* '._t('DeploymentNote.TBA', '_TBA')."\n\n\n".
                                                                                    '### '._t('DeploymentNote.KNOWN_ISSUES', '_Known Issues').":\n\n".
                                                                                    '* '._t('DeploymentNote.NONE', '_None')."\n\n\n".
                                                                                    '### '._t('DeploymentNote.KEY_TESTING', '_Key Testing Areas').":\n\n".
                                                                                    '* '._t('DeploymentNote.TBA', '_TBA')."\n\n\n".
                                                                                    '### '._t('DeploymentNote.POST_STAGING', '_Post Staging Changes').":\n\n".
                                                                                    '* '._t('DeploymentNote.TBA', '_TBA')."\n"),
                            new OptionsetField('Status', 'Deployment Status', array(
                                                                                    'planning'=>_t('DeploymentNote.PLANNING', '_Planning'),
                                                                                    'dev'=>_t('DeploymentNote.IN_DEV', '_In Development'),
                                                                                    'staged'=>_t('DeploymentNote.STAGING_TESTING', '_On Staging/In Testing'),
                                                                                    'deployed'=>_t('DeploymentNote.DEPLOYED', '_Deployed to Production')
                                                                                ), 'planning'),
                            new CheckboxField('DowntimeRequired', _t('DeploymentNote.DOWNTIME_REQUIRED', '_Is Downtime Required?')),
                            NumericField::create('DowntimeEstimate', _t('DeploymentNote.DOWNTIME_EST_LENGTH', '_Estimated downtime length in minutes'))->displayIf('DowntimeRequired')->isChecked()->end(),
                            TextField::create('DowntimeReason', _t('DeploymentNote.DOWNTIME_REASON', '_Reason for Downtime'), null, 400)->displayIf('DowntimeRequired')->isChecked()->end(),
                            new CheckboxField('OutOfCycle', _t('DeploymentNote.OUT_OF_CYCLE', '_Out of Cycle Deployment?')),
                            new CheckboxField('CycleResetter', _t('DeploymentNote.RESETS_CYCLE', '_Resets the Deployment Cycle?'))
                        );
        
        
        return $fields;
    }
    
    /**
     * Gets the date formatted from settings
     * @return string
     */
    public function getTitle() {
        return _t('DeploymentNote.WEEK_OF_DEPLOYMENT', '_Week of {week_end_date} Deployment', array('week_end_date'=>$this->dbObject('DeploymentWeekEnd')->FormatFromSettings()));
    }
    
    /**
     * Gets validator used in the cms
     * @return Validator Validator to be used
     */
    public function getCMSValidator() {
        return new RequiredFields(
                                'DeploymentStart',
                                'DeploymentWeekEnd',
                                'DeploymentNotes',
                                'Status'
                            );
    }
    
    /**
     * Gets the friendly descriptions for the status enum
     * @return string
     */
    public function getStatusNice() {
        switch($this->Status) {
            case 'planning':return _t('DeploymentNote.PLANNING', '_Planning');
            case 'dev':return _t('DeploymentNote.IN_DEV', '_In Development');
            case 'staged':return _t('DeploymentNote.STAGING_TESTING', '_On Staging/In Testing');
            case 'deployed':return _t('DeploymentNote.DEPLOYED', '_Deployed to Production');
        }
    }
    
    /**
     * Gets the Relative Link to this note
     * @param string $action Action to append to the url
     * @return string
     */
    public function Link($action=null) {
        return Controller::join_links('deployment-schedule', 'note', $this->ID, $action, '/');
    }
    
    /**
     * Gets the Absolute Link to this note
     * @param string $action Action to append to the url
     * @return string
     */
    public function AbsoluteLink($action=null) {
        return Director::absoluteURL($this->Link($action));
    }
    
    /**
     * Detects if this deployment has an abnormal cycle
     * @return bool
     */
    public function getIsOddCycle() {
        return (round(DateTime::createFromFormat('Y-m-d', $this->DeploymentStart)->diff(DateTime::createFromFormat('Y-m-d', $this->DeploymentWeekEnd))->days/7)>DeploymentSchedule::config()->deployment_cycle_length+DeploymentSchedule::config()->planning_period_length);
    }
    
    /**
     * Link to view the canonical segment
     * @return string Link to view the canonical segment
     */
    public function PreviewLink() {
        if($this->hasMethod('alternatePreviewLink')) {
            return $this->alternatePreviewLink();
        }else {
            return $this->AbsoluteLink();
        }
    }
    
    /**
     * Gets the link to edit in the cms
     * @return string
     */
    public function CMSEditLink() {
        return Director::absoluteURL('admin/deployment-schedule/DeploymentNote/EditForm/field/DeploymentNote/item/'.$this->ID.'/edit');
    }
    
    /**
     * Get the default summary fields for this object.
     * @return array
     */
    public function summaryFields() {
        $fields=$this->stat('summary_fields');
        
        foreach($fields as $key=>$value) {
            $fields[$key]=_t('DeploymentNote.db_'.str_replace('.', '_', $key), $value);
        }
        
        return $fields;
    }
}
?>