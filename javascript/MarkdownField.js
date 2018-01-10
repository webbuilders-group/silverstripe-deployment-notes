(function($) {
    $.entwine('lenovo', function($) {
        $('.field.wbg-markdown ul.panelsNav a').entwine({
            onclick: function(e) {
                var self=$(this);
                if(self.hasClass('selected')==false) {
                    var panelWrapper=self.closest('.field').find('.panels');
                    var markdownEditor=panelWrapper.find('.wbg-markdown-editor');
                    var markdown=panelWrapper.find('.wbg-markdown-preview');
                    
                    
                    //Clear selection and select this tab
                    self.parent().siblings().find('a.selected').removeClass('selected');
                    self.addClass('selected');
                    
                    
                    if(self.attr('data-panel')=='textarea') {
                        markdown.hide().html('');
                        markdownEditor.css('visibility', 'visible');
                    }else {
                        markdownEditor.css('visibility', 'hidden');
                        markdown.show().text(markdown.attr('data-previewtext'));
                        
                        $.ajax({
                            url: markdown.attr('data-callbackurl'),
                            type: 'post',
                            data: {
                                rawtext: markdownEditor.find('textarea').val(),
                                SecurityID: self.closest('form').find('input[name=SecurityID]').val()
                            },
                            success: function(data) {
                                markdown.html(data);
                            },
                            error: function(err) {
                                console.error(err);
                            }
                        });
                    }
                }
                
                e.stopPropagation();
                return false;
            }
        });
        
        $('.field.wbg-markdown.image-support textarea').entwine({
            /**
             * Handles when the user's dragging enters the textarea
             * @param e Event Data
             * @return False
             */
            ondragenter: function(e) {
                $(this).addClass('drag-over');
                
                e.stopPropagation();
                e.preventDefault();
                
                return false;
            },
            
            /**
             * Handles when the user's dragging leaves the textarea
             * @param e Event Data
             * @return False
             */
            ondragleave: function(e) {
                $(this).removeClass('drag-over');
            },
            
            /**
             * Handles when user drags over the area
             * @param e Event Data
             * @return False
             */
            ondragover: function(e) {
                e.stopPropagation();
                e.preventDefault();
                
                return false;
            },
            
            /**
             * Handles when the drop occurs
             * @param e Event Data
             * @return False
             */
            ondrop: function(e) {
                $(this).removeClass('drag-over');
                
                e.preventDefault();
                
                var files=e.originalEvent.dataTransfer.files;
                $(this).uploadFiles(files);
                
                return false;
            },
            
            /**
             * Uploads the dropped files to the server
             * @param {array} Array of files to upload
             */
            uploadFiles: function(files) {
                var self=$(this);
                if(files.length==0) {
                    self.showMessage('No image to upload', true);
                    return;
                }
                
                var url=self.closest('.field').attr('data-image-upload-url');
                if(url && url.length>0) {
                    var data=new FormData();
                    data.append('SecurityID', self.closest('form').find('input[name=SecurityID]').val()); //Append the security token
                    data.append('image', files[0]);
                    
                    //Display loading
                    self.siblings('.wbg-markdown-loader').show();
                    
                    $.ajax({
                        url: url,
                        type: 'POST',
                        contentType: false,
                        data: data,
                        processData: false,
                        cache: false,
                        dataType: 'json',
                        success: function(uploadedFile) {
                            if(uploadedFile.errors) {
                                self.showMessage(uploadedFile.errors.join('<br/>'), true);
                            }else {
                                var position=self._getCaretPos();
                                
                                self.insertImage(uploadedFile.url, uploadedFile.alt, position);
                                
                                self.showMessage('Add images by dragging and dropping them over the field.');
                            }
                            
                            self.siblings('.wbg-markdown-loader').hide();
                        },
                        error: function() {
                            self.showMessage('Error uploading the image', true);
                            
                            self.siblings('.wbg-markdown-loader').hide();
                        },
                        xhr: function() {
                            var xhr=jQuery.ajaxSettings.xhr();
                            var progressElem=self.siblings('.wbg-markdown-loader').find('.wbg-markdown-upload-progress i');
                            
                            if(window.addEventListener && progressElem.length==0) {
                                progressElem.css('width', 0);
                                
                                xhr.upload.addEventListener('progress', function(e) {
                                    if(e.lengthComputable) {
                                        var percentComplete=e.loaded/e.total;
                                        progressElem.css('width', Math.round(percentComplete*100)+'%');
                                    }
                                }, false);
                            }
                            
                            return xhr;
                        }
                    });
                }
            },
            
            /**
             * Displays a message in the markdown message box
             * @param {string} message Message to be displayed
             * @param {bool} error Whether the message is an error or not
             */
            showMessage: function(message, error) {
                var messageBox=$(this).siblings('.wbg-markdown-message');
                if(error==true) {
                    messageBox.addClass('error');
                }
                
                messageBox.html(message);
            },
            
            /**
             * Inserts an image markdown tag into the textarea at the caret's position
             * @param {string} url URL to the file
             * @param {string} alt Alternate text for the image
             * @param {int} position Index position to insert the tag at 
             */
            insertImage: function(url, alt, position) {
                var insertText='!['+alt+']('+url+')';
                var contents=$(this).val();
                
                
                //If we're not at the begining of the string and the previous character is not a space prepend a space
                if(position>0 && contents[position-1].match(/[^\s]/)) {
                    insertText=' '+insertText;
                }
                
                //If we're not at the end of the string and the next character is not whitespace append a space to the insert text
                if(position<contents.length && contents[position].match(/[^\s]/)) {
                    insertText+=' ';
                }
                
                
                //Splice the image tag into the text
                contents=[contents.slice(0, position), insertText, contents.slice(position)].join('');
                
                $(this).val(contents);
            },
            /**
             * Gets the position of the caret in the textarea contents
             * @return {int} Numeric index of the caret in the textarea contents
             */
            _getCaretPos: function() {
                var textArea=$(this).get(0);
                
                if('selection' in document) {
                    var range=textArea.createTextRange();
                    try {
                        range.setEndPoint("EndToStart", document.selection.createRange());
                    }catch (e) {
                        // Catch IE failure here, return 0 like
                        // other browsers
                        return 0;
                    }
                    
                    return range.text.length;
                }else if(textArea.selectionStart!=null) {
                    return textArea.selectionStart;
                }
            }
        });
    });
})(jQuery);