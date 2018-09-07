<div class="panels">
    <div class="deployment-notes-markdown-editor">
        <textarea $AttributesHTML>$ValueEntities.RAW</textarea>
        
        <% if $ImageUploadEnabled %>
            <div class="deployment-notes-markdown-message">
                <%t WebbuildersGroup\\DeploymentNotes\\Forms\\MarkdownField.ADD_IMAGES_BY "_Add images by dragging and dropping them over the field." %>
            </div>
            
            <div class="deployment-notes-markdown-loader">
                <div class="deployment-notes-markdown-upload-progress"><i><!-- --></i></div>
            </div>
        <% end_if %>
    </div>
    
    <div class="deployment-notes-markdown-preview" data-previewtext="<%t WebbuildersGroup\\DeploymentNotes\\Forms\\MarkdownField.LOADING_PREVIEW '_Loading Preview...' %>" data-callbackurl="$Link('markdown-preview')"><%t WebbuildersGroup\\DeploymentNotes\\Forms\\MarkdownField.LOADING_PREVIEW "_Loading Preview..." %></div>
</div>
