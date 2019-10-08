<?php
namespace WebbuildersGroup\DeploymentNotes\Control;

use DateTime;
use SilverStripe\Security\Security;
use SilverStripe\Security\Permission;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\View\Requirements;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\View\ArrayData;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Core\Config\Config;
use WebbuildersGroup\DeploymentNotes\Model\DeploymentNote;


/**
 * Class DeploymentSchedule
 *
 */
class DeploymentSchedule extends Controller {
    private static $allowed_actions=array(
                                        'note'
                                    );
    
    
    /**
     * The length of the deployment cycle in weeks
     * @var int
     * @config DeploymentSchedule.deployment_cycle_length
     */
    private static $deployment_cycle_length=4;
    
    /**
     * The length of the planning period in weeks
     * @var int
     * @config DeploymentSchedule.planning_period_length
     */
    private static $planning_period_length=1;
    
    /**
     * The length of the staging period in weeks
     * @var int
     * @config DeploymentSchedule.staging_period_length
     */
    private static $staging_period_length=1;
    
    /**
     * The number of future deployments to show
     * @var int
     * @config DeploymentSchedule.number_of_future_deployments
     */
    private static $number_of_future_deployments=4;
    
    /**
     * The number of historical deployments per-page
     * @var int
     * @config DeploymentSchedule.deployment_history_page_length
     */
    private static $deployment_history_page_length=12;
    
    /**
     * The permission code to check for to allow access to the deployment schedule
     * @var string|array|bool
     * @config DeploymentSchedule.view_permission_code
     */
    private static $view_permission_code='VIEW_DRAFT_CONTENT';
    
    
    protected $upcomingDeployments=false;
    protected $currentDeployment=false;
    
    
    public function init() {
        parent::init();
    
    
        //Prevent clickjacking
        $this->getResponse()->addHeader('X-Frame-Options', 'SAMEORIGIN');
        
        
        //Block access to visitors without draft viewer perimission
        if(!$this->canView()) {
            return Security::permissionFailure($this);
        }
    }
    
    /**
     * Checks to see if a member can view the deployment schedule or not
     * @param int|Member $member Member instance or ID to check
     * @return bool
     */
    public function canView($member=null) {
        return ($this->config()->view_permission_code===false || Permission::check($this->config()->view_permission_code, 'any', $member));
    }
    
    /**
     * Gets the Relative Link to this controller
     * @param string $action Action to append to the url
     * @return string
     */
    public function Link($action=null) {
        return Controller::join_links('deployment-schedule', $action, '/');
    }
    
    /**
     * Gets the Absolute Link to this controller
     * @param string $action Action to append to the url
     * @return string
     */
    public function AbsoluteLink($action=null) {
        return Director::absoluteURL($this->Link($action));
    }
    
    /**
     * Handles rendering requests for the note
     * @return array Returns an array to allow normal rendering
     */
    public function note() {
        $deployments=DeploymentNote::get();
        
        
        if($this->getIsAdmin()===false) {
            $deployments=$deployments->filter('Visible', true);
        }
        
        
        $this->currentDeployment=$deployments->byID(intval($this->urlParams['ID']));
        
        
        //If we don't have a note 404
        if(empty($this->currentDeployment) || $this->currentDeployment===false || !$this->currentDeployment->exists()) {
            return $this->httpError(404);
        }
        
        
        //Requirements
        Requirements::javascript('https://cdn.rawgit.com/google/code-prettify/master/loader/prettify.js');
        Requirements::javascript('silverstripe/admin:thirdparty/jquery/jquery.min.js');
        Requirements::javascript('silverstripe/admin:thirdparty/jquery-entwine/dist/jquery.entwine-dist.js');
        Requirements::javascript('webbuilders-group/silverstripe-deployment-notes:javascript/DeploymentSchedule.js');
        
        
        return array();
    }
    
    /**
     * Gets the title to use in the browser
     * @return string
     */
    public function getTitle() {
        switch($this->action) {
            case 'note':return _t('WebbuildersGroup\\DeploymentNotes\\Control\\DeploymentSchedule.WEEK_OF_DEPLOYMENT', '_Week of {week_end_date} Deployment', array('week_end_date'=>$this->getCurrentDeployment()->dbObject('DeploymentWeekEnd')->FormatFromSettings()));
            default:return 'Deployment Schedule';
        }
    }
    
    /**
     * Gets the historical deployments
     * @return PaginatedList|DeploymentNote[]
     */
    public function getDeploymentHistory() {
        return PaginatedList::create(DeploymentNote::get()
                                                        ->filter('Status', 'deployed')
                                                        ->filter('Visible', true)
                                        , $this->request)
                                ->setPageLength($this->config()->deployment_history_page_length);
    }
    
    /**
     * Gets the upcoming deployments
     * @return ArrayList|ArrayData[]|DeploymentNote[]
     */
    public function getUpcomingDeploymentSchedule() {
        if($this->upcomingDeployments===false) {
            //Gets the most recent deployment that is not out of cycle
            $lastDeployment=DeploymentNote::get()
                                                ->filter('OutOfCycle', false)
                                                ->filter('Visible', true)
                                                ->filter('Status', 'deployed')
                                                ->first();
            
            
            //Gets the next deployment that is not out of cycle
            $nextDeployment=DeploymentNote::get()
                                                ->filter('OutOfCycle', false)
                                                ->filter('Visible', true)
                                                ->filter('Status:not', 'deployed')
                                                ->filter('DeploymentWeekEnd:GreaterThanOrEqual', date('Y-m-d'))
                                                ->sort('"DeploymentNote"."DeploymentStart"')
                                                ->first();
            
            $schedule=array();
            
            $outOfCycleUpcoming=DeploymentNote::get()
                                                    ->filter('OutOfCycle', true)
                                                    ->filter('Visible', true)
                                                    ->filter('Status:not', 'deployed')
                                                    ->filter('DeploymentWeekEnd:GreaterThanOrEqual', date('Y-m-d'));
            if($outOfCycleUpcoming->count()>0) {
                foreach($outOfCycleUpcoming as $deploy) {
                    $schedule[]=$deploy;
                }
            }
            
            
            $resetIndex=false;
            if(!empty($nextDeployment) && $nextDeployment!==false && $nextDeployment->exists()) {
                $schedule[]=$nextDeployment;
                $lastWeekEnd=$nextDeployment->DeploymentWeekEnd;
                $cycleStartDate=$nextDeployment->DeploymentWeekEnd;
                $loopEndIndex=$this->config()->number_of_future_deployments-1;
            }else if(!empty($lastDeployment) && $lastDeployment!==false && $lastDeployment->exists()) {
                $lastWeekEnd=$lastDeployment->DeploymentWeekEnd;
                $cycleStartDate=$lastDeployment->DeploymentWeekEnd;
                $loopEndIndex=$this->config()->number_of_future_deployments;
            }else {
                $lastWeekEnd=date('Y-m-d');
                $cycleStartDate=date('Y-m-d');
                $loopEndIndex=$this->config()->number_of_future_deployments;
            }
            
            for($i=0;$i<$loopEndIndex;$i++) {
                $weekEnd=date('Y-m-d', strtotime($cycleStartDate.' +'.($this->config()->deployment_cycle_length*($i+1)).' weeks'));
                $note=DeploymentNote::get()->filter('Visible', true)->filter('Status:not', 'deployed')->filter('DeploymentWeekEnd', $weekEnd)->first();
                if(!empty($note) && $note!==false && $note->exists()) {
                    $schedule[]=$note;
                }else {
                    //Check to see if we have a resetter deployment coming soon, if we do use it and break out of the loop
                    $resetNote=DeploymentNote::get()
                                                    ->filter('Visible', true)
                                                    ->filter('CycleResetter', true)
                                                    ->filter('DeploymentWeekEnd:GreaterThan', $lastWeekEnd)
                                                    ->first();
                    if(!empty($resetNote) && $resetNote!==false && $resetNote->exists()) {
                        //Fill in the in-between deployments
                        if(strtotime($resetNote->DeploymentStart)>strtotime($lastWeekEnd)) {
                            $startDate=DateTime::createFromFormat('Y-m-d', $lastWeekEnd);
                            $endDate=DateTime::createFromFormat('Y-m-d', $resetNote->DeploymentStart);
                            
                            //Make sure we have two dates after each other if we do populate between the points
                            if(strtotime($lastWeekEnd)<strtotime($resetNote->DeploymentStart)) {
                                $i=$this->populateScheduleBetween($schedule, $i, round(($startDate->diff($endDate)->days/7)/$this->config()->deployment_cycle_length), $lastWeekEnd);
                            }
                        }
                        
                        //If we have room in the schedule add the reset
                        if($i+1<$this->config()->deployment_cycle_length) {
                            $schedule[]=$resetNote;
                            $resetIndex=$i+1;
                            break;
                        }
                    }else {
                        $schedule[]=new ArrayData(array(
                                                        'DeploymentWeekEnd'=>DBField::create_field(DBDate::class, $weekEnd),
                                                        'DeploymentNotes'=>false
                                                    ));
                    }
                }
                
                $lastWeekEnd=$weekEnd;
            }
            
            
            //If we had a resetter deployment fill out the future deployments
            if($resetIndex!==false && $resetIndex<$this->config()->number_of_future_deployments-1 && !empty($resetNote) && $resetNote!==false && $resetNote->exists()) {
                $this->populateScheduleBetween($schedule, $resetIndex, $this->config()->number_of_future_deployments-1, $resetNote->DeploymentWeekEnd);
            }
            
            $this->upcomingDeployments=new ArrayList($schedule);
        }
        
        return $this->upcomingDeployments;
    }
    
    /**
     * Populates the schedule between two dates
     * @param array $schedule Current schedule array
     * @param int $startIndex Current position in the schedule
     * @param int $endIndex Index to stop at
     * @param string $weekEnd Current week ending in the deployment schedule
     * @return int Returns the position in the schedule after iteration
     */
    protected function populateScheduleBetween(&$schedule, $startIndex, $endIndex, $weekEnd) {
        if($startIndex+$endIndex>$this->config()->number_of_future_deployments-1) {
            $endIndex=$this->config()->number_of_future_deployments-1;
        }
        
        for($i=$startIndex;$i<$endIndex;$i++) {
            $weekEnd=date('Y-m-d', strtotime($weekEnd.' +'.($this->config()->deployment_cycle_length*(($i-$startIndex)+1)).' weeks'));
            $note=DeploymentNote::get()->filter('Visible', true)->filter('DeploymentWeekEnd', $weekEnd)->first();
            if(!empty($note) && $note!==false && $note->exists()) {
                $schedule[]=$note;
            }else {
                $schedule[]=new ArrayData(array(
                                                'DeploymentWeekEnd'=>DBField::create_field(DBDate::class, $weekEnd),
                                                'DeploymentNotes'=>false
                                            ));
            }
        }
        
        return $i;
    }
    
    /**
     * Gets the percentage through the current cycle
     * @return float
     */
    public function getCurrentCyclePercentage() {
        $currentDeploy=$this->getCurrentDeployment();
        $deployStartStamp=strtotime($currentDeploy->DeploymentWeekEnd.' 00:00:00 -'.$this->config()->deployment_cycle_length.' weeks -4 days');
        $currentDay=$this->networkdays(date('Y-m-d', $deployStartStamp), date('Y-m-d', strtotime('yesterday')));
        
        $cycleProgress=round(($currentDay/($this->config()->deployment_cycle_length*5))*100, 2);
        if($currentDeploy instanceof DeploymentNote) {
            if($currentDeploy->Status=='planning') {
                if(($this->config()->planning_period_length*5)>=$currentDay) {
                    return $cycleProgress;
                }else {
                    return $this->getPlanningPercentage();
                }
            }else if($currentDeploy->Status=='dev') {
                if(($this->config()->planning_period_length*5)>=$currentDay) {
                    return $this->getPlanningPercentage();
                }else if(($this->config()->deployment_cycle_length-$this->config()->staging_period_length)*5>=$currentDay) {
                    return $cycleProgress;
                }else {
                    return $this->getPlanningPercentage()+$this->getDevPercentage();
                }
            }else if($currentDeploy->Status=='staged') {
                if(($this->config()->deployment_cycle_length-$this->config()->staging_period_length)*5>=$currentDay) {
                    return $this->getPlanningPercentage()+$this->getDevPercentage();
                }else if($this->config()->deployment_cycle_length*5>=$currentDay) {
                    return $cycleProgress;
                }else {
                    return 100;
                }
            }
        }else {
            return $cycleProgress;
        }
    }
    
    /**
     * Gets the current deployment or upcoming deployment instance
     * @return ArrayData|DeploymentNote
     */
    public function getCurrentDeployment() {
        if($this->currentDeployment!==false) {
            return $this->currentDeployment;
        }
        
        return $this->getUpcomingDeploymentSchedule()->first();
    }
    
    /**
     * Gets the percentage that the planning period represents in the development cycle
     * @return int
     */
    public function getPlanningPercentage() {
        return round(($this->config()->planning_period_length/$this->config()->deployment_cycle_length)*100, 2);
    }
    
    /**
     * Gets the percentage that the development period represents in the development cycle
     * @return int
     */
    public function getDevPercentage() {
        return round((($this->config()->deployment_cycle_length-($this->config()->planning_period_length+$this->config()->staging_period_length))/$this->config()->deployment_cycle_length)*100, 2);
    }
    
    /**
     * Gets the percentage that the staging period represents in the development cycle
     * @return int
     */
    public function getStagingPercentage() {
        return round(($this->config()->staging_period_length/$this->config()->deployment_cycle_length)*100, 2);
    }
    
    /**
     * Checks to see if the current member has the admin permission
     * @return bool
     */
    public function getIsAdmin() {
        //Get the old value for Permission.admin_implies_all
        $oldValue=Config::inst()->get(Permission::class, 'admin_implies_all');
        
        
        //Disable the Permission.admin_implies_all
        Config::inst()->update(Permission::class, 'admin_implies_all', false);
        
        
        //Look to the normal permission checking
        $result=Permission::check('CMS_ACCESS_DeploymentScheduleAdmin');
        
        
        //Restore the value for Permission.admin_implies_all
        Config::inst()->update(Permission::class, 'admin_implies_all', $oldValue);
        
        
        return ($result!==false);
    }
    
    /**
     * Calculates the number of network days (business days) between two dates
     * @param string $startDate Day to start from must be strtotime compatible
     * @param string $endDate Day to end at must be strtotime compatible
     * @return int
     */
    protected function networkdays($startDate, $endDate) {
        $start_array=getdate(strtotime($startDate));
        $end_array=getdate(strtotime($endDate));
        
        
        //Find start and end sundays
        $start_sunday=mktime(0, 0, 0, $start_array['mon'], $start_array['mday']+(7-$start_array['wday']), $start_array['year']);
        $end_sunday=mktime(0, 0, 0, $end_array['mon'], $end_array['mday']-$end_array['wday'], $end_array['year']);
        
        
        //Calculate days in the whole weeks
        $week_diff=$end_sunday-$start_sunday;
        $number_of_weeks=round($week_diff /604800); // 60 seconds * 60 minutes * 24 hours * 7 days = 1 week in seconds
        $days_in_whole_weeks=$number_of_weeks * 5;
        
        
        //Calculate extra days at start and end [wday] is 0 (Sunday) to 7 (Saturday)
        $days_at_start=6-$start_array['wday'];
        $days_at_end=$end_array['wday'];
        
        
        return $days_in_whole_weeks+$days_at_start+$days_at_end;
    }
}
?>