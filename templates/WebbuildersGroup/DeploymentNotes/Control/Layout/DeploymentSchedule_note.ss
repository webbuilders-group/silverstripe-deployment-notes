<% with $CurrentDeployment %>
    <h1>$Title.XML</h1>
    
    <% if $OutOfCycle %>
        <p><i><%t WebbuildersGroup\\DeploymentNotes\\Control\\DeploymentSchedule.OUT_OF_CYCLE "_Out of Cycle Deployment" %></i></p>
    <% else_if $CycleResetter %>
        <p><i><%t WebbuildersGroup\\DeploymentNotes\\Control\\DeploymentSchedule.CYCLE_RESETTER_TO_NEW "_This deployment resets the deployment schedule to a new schedule" %></i></p>
    <% end_if %>
    
    <hr class="deploy-heading-split" />
    
    <% if $Status!='deployed' && $DowntimeRequired %>
        <p>
            <b>
                <% if not $DowntimeEstimate %><%t WebbuildersGroup\\DeploymentNotes\\Control\\DeploymentSchedule.DOWNTIME_REQUIRED_NO_EST "_Downtime is required for this deployment" %><% else %><%t WebbuildersGroup\\DeploymentNotes\\Control\\DeploymentSchedule.DOWNTIME_REQUIRED_EST "_Downtime is required for this deployment, we anticipate it lasting no longer than {time} minutes" time=$DowntimeEstimate %><% end_if %>.
                <% if $DowntimeReason %>
                    $DowntimeReason.XML
                <% end_if %>
            </b>
        </p>
    <% end_if %>
    
    $DeploymentNotes
<% end_with %>

<div class="top-bar">
    <a href="$Link" class="button"><%t WebbuildersGroup\\DeploymentNotes\\Control\\DeploymentSchedule.BACK "_Back" %></a>
</div>