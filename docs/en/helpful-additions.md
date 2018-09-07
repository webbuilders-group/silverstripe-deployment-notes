Helpful Additions
=================
There are a couple of helpful additions that we've used on our projects that make using the deployment schedule a bit more friendly for the users.

## Better Navigator Addon
If you are using the [jonom/silverstripe-betternavigator](https://github.com/jonom/silverstripe-betternavigator) module you can create the following as a template named ``BetterNavigatorExtraContent.ss`` in your theme or application/project folder to add a button linking to the deployment schedule.

```silverstripe
<div class="bn-links">
    <a href="deployment-schedule"><span class="bn-icon-db"></span>Deployment Schedule</a>
</div>
```


## Adding a Deployment Notice Bar
You can add a deployment notice bar to the front end of the site by adding the following class.
```php
<?php
class DeploymentScheduleTemplateGlobals implements TemplateGlobalProvider {
    /**
     * Called by SSViewer to get a list of global variables to expose to the template, the static method to call on
     * this class to get the value for those variables, and the class to use for casting the returned value for use
     * in a template
     *
     * @abstract
     * @return array Returns an array of items. Each key => value pair is one of three forms:
     * - template name (no key)
     * - template name => method name
     * - template name => array(), where the array can contain these key => value pairs
     * - "method" => method name
     * - "casting" => casting class to use (i.e., Varchar, HTMLText, etc)
     */
    public static function get_template_global_variables() {
        return array(
                    'IsWeekBeforeDeploy'=>array(
                                                'method'=>'getIsWeekBeforeDeploy',
                                                'casting'=>'Boolean'
                                            ),
                    'IsPlanningWeek'=>array(
                                            'method'=>'getIsPlanningWeek',
                                            'casting'=>'Boolean'
                                        ),
                    'NextDeployment'=>array(
                                            'method'=>'getNextDeployment'
                                        ),
                    'ThisFriday'=>array(
                                        'method'=>'getThisFriday',
                                        'casting'=>'Date'
                                    )
                );
    }

    /**
     * Detects if we're in the week before the deployment
     * @return bool
     */
    public static function getIsWeekBeforeDeploy() {
        return (DeploymentNote::get()
                                    ->filter('OutOfCycle', false)
                                    ->filter('Visible', true)
                                    ->filter('Status', array('staged', 'dev'))
                                    ->filter('DeploymentWeekEnd', date('Y-m-d', strtotime('friday next week')))
                                ->count()>0);
    }

    /**
     * Detects if we're in a planning week(s)
     * @return bool
     */
    public static function getIsPlanningWeek() {
        return (DeploymentNote::get()
                                    ->filter('OutOfCycle', false)
                                    ->filter('Visible', true)
                                    ->whereAny(array(
                                                    //Attempt to find notes for this cycle
                                                    '"DeploymentNote"."DeploymentStart"= ?'=>date('Y-m-d', strtotime('monday this week')),

                                                    //Attempt to find the previous deployment cycle notes
                                                    '('.
                                                        '"DeploymentNote"."DeploymentStart"= ? AND '.
                                                        'DATE_SUB(DATE_ADD("DeploymentNote"."DeploymentStart", INTERVAL '.intval(DeploymentSchedule::config()->planning_period_length).' WEEK), INTERVAL 2 DAY)<= ? AND'.
                                                        '"DeploymentNote"."Status" != \'planning\' AND '.
                                                        '"DeploymentNote"."DeploymentWeekEnd"<=\''.date('Y-m-d', strtotime('friday this week')).'\''.
                                                    ')'=>array(
                                                                date('Y-m-d', strtotime(DeploymentSchedule::config()->deployment_cycle_length.' weeks ago', strtotime('monday this week'))),
                                                                date('Y-m-d')
                                                            )
                                                ))
                                ->count()>0);
    }

    /**
     * Gets the next deployment
     * @return DeploymentNote
     */
    public static function getNextDeployment() {
        return DeploymentNote::get()
                                    ->filter('OutOfCycle', false)
                                    ->filter('Visible', true)
                                    ->filter('Status', 'staged')
                                    ->filter('DeploymentWeekEnd', date('Y-m-d', strtotime('friday next week')))
                                ->first();
    }

    /**
     * Gets the date of the friday for this week
     * @return string
     */
    public static function getThisFriday() {
        return date('Y-m-d', strtotime('friday this week'));
    }
}
?>
```

Then in your template you could do something like the below (styling accordingly):
```html
<% if $IsWeekBeforeDeploy %>
    <div id="deploy-cycle-bar">
        There is a deployment coming next week<% if $NextDeployment %>, <a href="$NextDeployment.Link" target="_blank">click here</a> to view the deployment notes<% end_if %>.
    </div>
<% else_if $IsPlanningWeek %>
    <div id="deploy-cycle-bar">
        It is currently a planning week for the next deployment, please make any feature requests before $ThisFriday.Format('F j, Y') at 4pm Eastern to have them evaluated for the next deployment.
    </div>
<% end_if %>
```

You may also want to wrap this in a check to make sure the current user can actually view the schedule. Simply add the code below to the class from above then in your template check the ``$CanViewDeployments`` variable in a condition around the template code from above.

```php
<?php
class DeploymentScheduleTemplateGlobals implements TemplateGlobalProvider {
    /** ... **/
    public static function get_template_global_variables() {
        return array(
                    'CanViewDeployments'=>array(
                                                'method'=>'getCanViewDeployments',
                                                'casting'=>'Boolean'
                                            ),
                    /** ... **/
                );
    }

    /**
     * Gets whether the user can view the deployment schedule or not
     * @return bool True if the user can view the deployment schedule or not
     */
    public static function getCanViewDeployments() {
        return (DeploymentSchedule::config()->view_permission_code===false || Permission::check(DeploymentSchedule::config()->view_permission_code));
    }

    /** ... **/
}
?>
```
