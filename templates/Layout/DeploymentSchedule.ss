<div class="current-deployment-progress">
    <h2><%t DeploymentSchedule.CURRENT_DEPLOY_STATUS "_Current Deployment Status" %></h2>
    
    <% if $CurrentDeployment %>
        <% with $CurrentDeployment %>
            <p>
                <%t DeploymentSchedule.CURRENT_DEPLOY_GO_LIVE "_Current deployment is scheduled to go live the week of {week_end_date}" week_end_date=$DeploymentWeekEnd.FormatFromSettings %>, 
                <% if $DeploymentNotes %>
                    <%t DeploymentSchedule.NOTES_AVAILABLE '_Deployment Notes are <a href="{link}">available here</a>.' link=$Link %>
                <% else %>
                    <%t DeploymentSchedule.NOTES_TBA "_Deployment Notes to be announced" %>
                <% end_if %>
            </p>
            
            <% if $OutOfCycle %>
                <div class="progress-wrap out-of-cycle">
                    <ul>
                        <li><%t DeploymentSchedule.OUT_OF_CYCLE_NOTICE "_This deployment is out of the normal cycle, progress is not available. The deployment is currently in the &quot;{status}&quot; phase." status=$StatusNice %></li>
                    </ul>
                </div>
            <% else_if $IsOddCycle %>
                <div class="progress-wrap out-of-cycle">
                    <ul>
                        <li><%t DeploymentSchedule.ABNORMAL_CYCLE_NOTICE "_This deployment has an abnormal cycle, progress is not available. The deployment is currently in the &quot;{status}&quot; phase." status=$StatusNice %></li>
                    </ul>
                </div>
            <% else %>
                <div class="progress-wrap">
                    <ul>
                        <li style="width: {$Top.PlanningPercentage.ATT}%;"><%t DeploymentSchedule.PLANNING "_Planning" %></li>
                        <li style="width: {$Top.DevPercentage.ATT}%;"><%t DeploymentSchedule.DEVELOPMENT "_Development" %></li>
                        <li style="width: {$Top.StagingPercentage.ATT}%;"><%t DeploymentSchedule.STAGING "_Staging" %></li>
                    </ul>
                    
                    <div class="progress" style="width: {$Top.CurrentCyclePercentage}%"><!-- --></div>
                </div>
            <% end_if %>
        <% end_with %>
    <% end_if %>
</div>

<div class="schedule-list">
    <% if $UpcomingDeploymentSchedule %>
        <h2><%t DeploymentSchedule.UPCOMING_DEPLOYMENTS "_Upcoming Deployments" %></h2>
        
        <ul>
            <% loop $UpcomingDeploymentSchedule %>
                <li>
                    <h4><%t DeploymentSchedule.WEEK_OF_DEPLOYMENT "_Week of {week_end_date} Deployment" week_end_date=$DeploymentWeekEnd.FormatFromSettings %></h4>
                    
                    <% if $OutOfCycle %>
                        <i><%t DeploymentSchedule.OUT_OF_CYCLE "_Out of Cycle Deployment" %></i><br/>
                    <% else_if $CycleResetter %>
                        <i><% if not $DeploymentNotes %><%t DeploymentSchedule.CYCLE_RESETTER "_This deployment resets the deployment schedule" %><% else %><%t DeploymentSchedule.CYCLE_RESETTER_NOTES "_This deployment resets the deployment schedule, see deployment notes for more information" %><% end_if %>.</i><br/>
                    <% else_if $IsOddCycle %>
                        <i><% if not $DeploymentNotes %><%t DeploymentSchedule.ABNORMAL_CYCLE "_This deployment has an abnormal cycle" %><% else %><%t DeploymentSchedule.ABNORMAL_CYCLE_NOTES "_This deployment has an abnormal cycle, see deployment notes for more information" %><% end_if %>.</i><br/>
                    <% end_if %>
                    
                    <% if $DowntimeRequired %>
                        <i><%t DeploymentSchedule.DOWNTIME_REQUIRED "_Downtime is required for this deployment, see deployment notes for more information." %></i><br />
                    <% end_if %>
                    
                    <% if $DeploymentNotes %>
                        <a href="$Link"><%t DeploymentSchedule.DEPLOYMENT_NOTES "_Deployment Notes" %></a>
                    <% else %>
                        <i><%t DeploymentSchedule.NOTES_TBA "_Deployment Notes to be announced" %></i>
                    <% end_if %>
                </li>
            <% end_loop %>
        </ul>
    <% end_if %>
</div>

<div class="history-list" id="deployment-history">
    <% if $DeploymentHistory %>
        <h2><%t DeploymentSchedule.DEPLOYMENT_HISTORY "_Deployment History" %></h2>
        
        <ul>
            <% loop $DeploymentHistory %>
                <li>
                    <h4><%t DeploymentSchedule.WEEK_OF_DEPLOYMENT "_Week of {week_end_date} Deployment" week_end_date=$DeploymentWeekEnd.FormatFromSettings %></h4>
                    
                    <% if $OutOfCycle %>
                        <i><%t DeploymentSchedule.OUT_OF_CYCLE "_Out of Cycle Deployment" %></i><br/>
                    <% else_if $CycleResetter %>
                        <i><%t DeploymentSchedule.CYCLE_WAS_RESET "_This deployment reset the deployment schedule" %></i><br/>
                    <% else_if $IsOddCycle %>
                        <i><%t DeploymentSchedule.HAD_ABNORMAL "_This deployment had an abnormal cycle" %></i><br/>
                    <% end_if %>
                    
                    <a href="$Link"><%t DeploymentSchedule.DEPLOYMENT_NOTES "_Deployment Notes" %></a>
                </li>
            <% end_loop %>
        </ul>
        
        <% with $DeploymentHistory %>
            <% if $MoreThanOnePage %>
                <p class="pagination"><%--
                    --%><% if $NotFirstPage %><%--
                        --%><a class="prev" href="{$PrevLink}" title="<%t DeploymentSchedule.PREVIOUS_PAGE '_Previous Page' %>"><i class="fa fa-fw fa-angle-left"><!-- --></i></a><%--
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
                        --%><a class="next" href="{$NextLink}" title="<%t DeploymentSchedule.NEXT_PAGE '_Next Page' %>"><i class="fa fa-fw fa-angle-right"><!-- --></i></a><%--
                    --%><% end_if %><%--
                --%></p>
            <% end_if %>
        <% end_with %>
    <% end_if %>
</div>