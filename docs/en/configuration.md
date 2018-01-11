Configuration
=================
## Deployment Schedule
By default the deployment schedule is made of 4 weeks, the first week is a planning week, the next two are development weeks and the last week is a testing/staging week. The deployment happens after the testing/staging week during the planning week for the next deployment. The length of each of the stages of a deployment can be customized using the following configuration options.

* __DeploymentSchedule.deployment_cycle_length:__ *(Default: 4)* The over all length of the deployment cycle in weeks
* __DeploymentSchedule.planning_period_length:__ *(Default: 1)* The length of the planning period in weeks.
* __DeploymentSchedule.staging_period_length:__ *(Default: 1)* The length of the staging period in weeks.

There are also a few configuration options to control the display of the schedule:
* __DeploymentSchedule.number_of_future_deployments:__ *(Default: 4)* The number of future deployments to show on the deployment schedule.
* __DeploymentSchedule.deployment_history_page_length:__ *(Default: 12)* The number of historical deployments per-page on the deployment schedule.
* __DeploymentSchedule.view_permission_code:__ *(Default: VIEW_DRAFT_CONTENT)* The permission code to check for to allow access to the deployment schedule, this can be set to any single permission code, an array of permission codes (which if the user has any will result in access) or boolean false to allow any unauthenticated user access.

## Deployment Schedule Admin
The deployment schedule admin has one configuration option ``DeploymentScheduleAdmin.strict_permission_check`` that can be used to require the CMS user has the ``CMS_ACCESS_DeploymentScheduleAdmin`` permission explicitly. The idea being that you can have the section be hidden from your customer but still available to yourself, of course the user could still give themselves the permission if they have the ``ADMIN`` permission but it's an attempt to prevent it. This option defaults to ``true`` but can be turned off by setting the configuration option to ``false``.
