<div class="current-deployment-progress">
    <h2>Current Deployment Status</h2>
    
    <% if $CurrentDeployment %>
        <% with $CurrentDeployment %>
            <p>
                Current deployment is scheduled to go live the week of $DeploymentWeekEnd.FormatFromSettings,
                <% if $DeploymentNotes %>
                    Deployment Notes are <a href="$Link">available here</a>.
                <% else %>
                    Deployment Notes are to be announced.
                <% end_if %>
            </p>
            
            <% if $OutOfCycle %>
                <div class="progress-wrap out-of-cycle">
                    <ul>
                        <li>This deployment is out of the normal cycle, progress is not available. The deployment is currently in the &quot;$StatusNice&quot; phase.</li>
                    </ul>
                </div>
            <% else_if $IsOddCycle %>
                <div class="progress-wrap out-of-cycle">
                    <ul>
                        <li>This deployment has an abnormal cycle, progress is not available. The deployment is currently in the &quot;$StatusNice&quot; phase.</li>
                    </ul>
                </div>
            <% else %>
                <div class="progress-wrap">
                    <ul>
                        <li style="width: {$Top.PlanningPercentage.ATT}%;">Planning</li>
                        <li style="width: {$Top.DevPercentage.ATT}%;">Development</li>
                        <li style="width: {$Top.StagingPercentage.ATT}%;">Staging</li>
                    </ul>
                    
                    <div class="progress" style="width: {$Top.CurrentCyclePercentage}%"><!-- --></div>
                </div>
            <% end_if %>
        <% end_with %>
    <% end_if %>
</div>

<div class="schedule-list">
    <% if $UpcomingDeploymentSchedule %>
        <h2>Upcoming Deployments</h2>
        
        <ul>
            <% loop $UpcomingDeploymentSchedule %>
                <li>
                    <h4>Week of $DeploymentWeekEnd.FormatFromSettings Deployment</h4>
                    
                    <% if $OutOfCycle %>
                        <i>Out of Cycle Deployment</i><br/>
                    <% else_if $CycleResetter %>
                        <i>This deployment resets the deployment schedule<% if $DeploymentNotes %>, see deployment notes for more information<% end_if %>.</i><br/>
                    <% else_if $IsOddCycle %>
                        <i>This deployment has an abnormal cycle<% if $DeploymentNotes %>, see deployment notes for more information<% end_if %>.</i><br/>
                    <% end_if %>
                    
                    <% if $DowntimeRequired %>
                        <i>Downtime is required for this deployment<% if $DeploymentNotes %>, see deployment notes for more information<% end_if %>.</i><br />
                    <% end_if %>
                    
                    <% if $DeploymentNotes %>
                        <a href="$Link">Deployment Notes</a>
                    <% else %>
                        <i>Deployment Notes to be announced</i>
                    <% end_if %>
                </li>
            <% end_loop %>
        </ul>
    <% end_if %>
</div>

<div class="history-list" id="deployment-history">
    <% if $DeploymentHistory %>
        <h2>Deployment History</h2>
        
        <ul>
            <% loop $DeploymentHistory %>
                <li>
                    <h4>Week of $DeploymentWeekEnd.FormatFromSettings Deployment</h4>
                    
                    <% if $OutOfCycle %>
                        <i>Out of Cycle Deployment</i><br/>
                    <% else_if $CycleResetter %>
                        <i>This deployment reset the deployment schedule</i><br/>
                    <% else_if $IsOddCycle %>
                        <i>This deployment had an abnormal cycle</i><br/>
                    <% end_if %>
                    
                    <a href="$Link">Deployment Notes</a>
                </li>
            <% end_loop %>
        </ul>
        
        <% with $DeploymentHistory %>
            <% if $MoreThanOnePage %>
                <p class="pagination"><%--
                    --%><% if $NotFirstPage %><%--
                        --%><a class="prev" href="{$PrevLink}" title="Previous Page"><i class="fa fa-fw fa-angle-left"><!-- --></i></a><%--
                    --%><% end_if %><%--
                
                    --%><% loop $PaginationSummary %><%--
                        --%><% if $CurrentBool %><%--
                            --%><strong>$PageNum</strong><%--
                        --%><% else %><%--
                            --%><% if $Link %><%--
                                --%><a href="$Link">$PageNum</a><%--
                            --%><% else %><%--
                                --%><span>...</span><%--
                            --%><% end_if %><%--
                        --%><% end_if %><%--
                    --%><% end_loop %><%--
                    
                    --%><% if $NotLastPage %><%--
                        --%><a class="next" href="{$NextLink}" title="Next Page"><i class="fa fa-fw fa-angle-right"><!-- --></i></a><%--
                    --%><% end_if %><%--
                --%></p>
            <% end_if %>
        <% end_with %>
    <% end_if %>
</div>