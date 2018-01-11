Usage
=================
## For Users
The deployment schedule system can be access by visiting ``/deployment-schedule`` on your website. This will show the user when the deployment is due to go live and what the current progress is in the cycle. It will also show the upcoming deployments with links to the notes for that cycle, plus the past deployments also with links to the notes for that cycle.

## For Admins
For administrators managing the deployment schedule can be done through the CMS using the "Deployment Schedule" section in the CMS. Deployment notes have three base fields to define the cycle, the start date (by default is Monday of the week), the deployment week end date (by default Friday 5 weeks later), and the actual deployment date. The deployment week end date is the overlap point between the deployment note being created and the next cycle. The reason for it being a full week is that it gives flexibility of what the actual date that week is that the changes will go live. When there are no deployments in the system the start date is the Monday of the current week and the cycle end date is Friday 5 weeks later. If there is a previous deployment then the system will calculate it as Monday of the deployment week for the last deployment and the end date as Friday 5 weeks later.

The actual notes for deployments support Markdown and have a preview functionality so you can see what your Markdown is rendering like. The deployment status is 4 phases planning (default), in development, on staging/in testing, and deployed to production. The stages must be changed manually as the cycle progresses otherwise the deployment schedule's progress bar will stop at that stage as time progresses.

Deployments can also be flagged as having downtime required with an optional number of minutes estimated to be required. A deployment can also be flagged as out of cycle, deployments flagged as such do not affect the upcoming deployments schedule.

Lastly deployments can also be flagged as resetting the cycle, this means when the upcoming schedule hits one of these deployments the cycle will be reset from that point. This is particularly useful if you have a deployment that straddles more than one normal cycle or to schedule over a holiday shutdown for the development team.
