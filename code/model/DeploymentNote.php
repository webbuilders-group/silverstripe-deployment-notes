<?php
use WebbuildersGroup\DeploymentNotes\forms\MarkdownField;

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
                            'DeploymentNotes'=>'WebbuildersGroup\DeploymentNotes\model\datatypes\Markdown',
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
                                        'Visible.Nice'=>'Visible to Users',
                                        'DeploymentStart'=>'Cycle Start Date',
                                        'DeploymentWeekEnd'=>'Deployment Week',
                                        'Date'=>'Actual Deployment Date',
                                        'StatusNice'=>'Deployment Status',
                                        'OutOfCycle.Nice'=>'Out of Cycle'
                                    );
    
    
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
                            new CheckboxField('Visible', 'Visible to Users?'),
                            DateField::create('DeploymentStart', 'Cycle Start Date', $startDate)->setConfig('showcalendar', true),
                            DateField::create('DeploymentWeekEnd', 'Deployment Week End Date', $endDate)->setConfig('showcalendar', true),
                            DateField::create('Date', 'Actual Deployment Date')->setConfig('showcalendar', true),
                            new MarkdownField('DeploymentNotes', 'Deployment Notes', "### Planned Changes:\n\n".
                                                                                    "_Planned Changes are scheduled to be included in this deployment however they maybe pushed to a future deployment._\n\n".
                                                                                    "* TBA\n\n\n".
                                                                                    "### High Level:\n\n".
                                                                                    "* TBA\n\n\n".
                                                                                    "### Bug Fixes/Behind the Scenes/Minor:\n\n".
                                                                                    "* TBA\n\n\n".
                                                                                    "### Known Issues:\n\n".
                                                                                    "* None\n\n\n".
                                                                                    "### Key Testing Areas:\n\n".
                                                                                    "* TBA\n\n\n".
                                                                                    "### Post Staging Changes:\n\n".
                                                                                    "* TBA\n"),
                            new OptionsetField('Status', 'Deployment Status', array(
                                                                                    'planning'=>'Planning',
                                                                                    'dev'=>'In Development',
                                                                                    'staged'=>'On Staging/In Testing',
                                                                                    'deployed'=>'Deployed to Production'
                                                                                ), 'planning'),
                            new CheckboxField('DowntimeRequired', 'Is Downtime Required?'),
                            NumericField::create('DowntimeEstimate', 'Estimated downtime length in minutes')->displayIf('DowntimeRequired')->isChecked()->end(),
                            TextField::create('DowntimeReason', 'Reason for Downtime', null, 400)->displayIf('DowntimeRequired')->isChecked()->end(),
                            new CheckboxField('OutOfCycle', 'Out of Cycle Deployment?'),
                            new CheckboxField('CycleResetter', 'Resets the Deployment Cycle?')
                        );
        
        
        return $fields;
    }
    
    /**
     * Gets the date formatted from settings
     * @return string
     */
    public function getTitle() {
        return 'Week of '.$this->dbObject('DeploymentWeekEnd')->FormatFromSettings().' Deployment';
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
            case 'planning':return 'Planning';
            case 'dev':return 'In Development';
            case 'staged':return 'On Staging/In Testing';
            case 'deployed':return 'Deployed to Production';
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
}
?>