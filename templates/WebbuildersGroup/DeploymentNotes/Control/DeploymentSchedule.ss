<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>
        <title>$Title.XML - $SiteConfig.Title.XML<% if $SiteConfig.Tagline %> - $SiteConfig.Tagline.XML<% end_if %></title>
        
        $MetaTags(false)
        <meta name="robots" content="noindex"/>
        
        <% base_tag %>
        
        <% require css("https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css") %>
        <% require css("https://fonts.googleapis.com/css?family=Raleway:400,700,700italic,400italic,500,500italic,300,300italic") %>
        <% require css("webbuilders-group/silverstripe-deployment-notes:css/DeploymentSchedule.css") %>
    </head>
    <body class="deployment-schedule $Action">
        $Layout
        
        <script type="text/javascript"></script>
    </body>
</html>