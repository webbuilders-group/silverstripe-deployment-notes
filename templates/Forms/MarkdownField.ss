<div class="panels">
    <div class="wbg-markdown-editor">
        <textarea $AttributesHTML>$Value</textarea>
        
        <% if $ImageUploadEnabled %>
            <div class="wbg-markdown-message">
                <%t WebbuildersGroup\\DeploymentNotes\\Forms\\MarkdownField.ADD_IMAGES_BY "_Add images by dragging and dropping them over the field." %>
            </div>
            
            <div class="wbg-markdown-loader">
                <div class="wbg-markdown-upload-progress"><i><!-- --></i></div>
            </div>
        <% end_if %>
    </div>
    
    <div class="wbg-markdown-preview" data-previewtext="<%t WebbuildersGroup\\DeploymentNotes\\Forms\\MarkdownField.LOADING_PREVIEW '_Loading Preview...' %>" data-callbackurl="$Link('markdown-preview')"><%t WebbuildersGroup\\DeploymentNotes\\Forms\\MarkdownField.LOADING_PREVIEW "_Loading Preview..." %></div>
</div>
