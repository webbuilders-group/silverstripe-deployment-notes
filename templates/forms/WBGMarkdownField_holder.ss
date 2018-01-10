<div id="$Name" class="field<% if $extraClass %> $extraClass<% end_if %>"<% if $ImageUploadEnabled %> data-image-upload-url="$Link('image-upload')"<% end_if %>>
	<% if $Title %><label class="left" for="$ID">$Title</label><% end_if %>
    
    <ul class="panelsNav">
        <li><a href="#" class="selected" data-panel="textarea">Write</a></li>
        <li><a href="#" data-panel="wbg-markdown-preview">Preview</a></li>
    </ul>
    
	<div class="middleColumn">
		$Field
	</div>
    
	<% if $RightTitle %><label class="right" for="$ID">$RightTitle</label><% end_if %>
	<% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
	<span class="description">This field uses <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">markdown</a> for formatting.</span>
</div>
