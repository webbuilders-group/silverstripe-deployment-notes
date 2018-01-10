<% with $CurrentDeployment %>
    <h1>$Title.XML</h1>
    
    <% if $OutOfCycle %>
        <p><i>Out of Cycle Deployment</i></p>
    <% else_if $CycleResetter %>
        <p><i>This deployment resets the deployment schedule to a new schedule</i></p>
    <% end_if %>
    
    <hr class="deploy-heading-split" />
    
    <% if $Status!='deployed' && $DowntimeRequired %>
        <p>
            <b>
                Downtime is required for this deployment<% if $DowntimeEstimate %>, we anticipate it lasting no longer than $DowntimeEstimate minutes<% end_if %>.
                <% if $DowntimeReason %>
                    $DowntimeReason.XML
                <% end_if %>
            </b>
        </p>
    <% end_if %>
    
    $DeploymentNotes
<% end_with %>

<div class="top-bar">
    <a href="$Link" class="button">Back</a>
</div>