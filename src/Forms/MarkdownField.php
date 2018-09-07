<?php
namespace WebbuildersGroup\DeploymentNotes\Forms;

use ParsedownExtra;
use SilverStripe\View\Requirements;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Director;
use SilverStripe\Security\SecurityToken;
use SilverStripe\Core\Convert;
use SilverStripe\Assets\Upload;
use SilverStripe\Assets\File;
use SilverStripe\Forms\TextareaField;


class MarkdownField extends TextareaField {
    private static $allowed_actions=array(
                                        'markdown_preview',
                                        'image_upload'
                                    );
    
    
    private $_imageSupportEnabled=false;
    private $_uploadDir='Uploads';
    private $_maxUploadSize=0;
    
    protected $rows=30;
    
    
    public function FieldHolder($properties=array()) {
        $this->extraClasses[]='stacked';
        $this->extraClasses[]='textarea';
        
        Requirements::css('webbuilders-group/silverstripe-deployment-notes:css/MarkdownField.css');
        
        Requirements::javascript('webbuilders-group/silverstripe-deployment-notes:javascript/MarkdownField.js');
        
        return parent::FieldHolder($properties);
    }
    
    /**
     * Generates markdown for previewing
     * @param {SS_HTTPRequest} $request HTTP Request Object
     * @return {string} HTML Response
     */
    public function markdown_preview(HTTPRequest $request) {
        //Verify the request is ajax and the security token is good
        if(Director::is_ajax()==false || SecurityToken::inst()->checkRequest($request)==false) {
            return $this->httpError(403);
        }
        
        
        //If the markdown is empty just return
        $markdown=$this->request->postVar('rawtext');
        if(empty($markdown)) {
            return '';
        }
        
        
        //Get rendered HTML
        $parser=new \ParsedownExtra();
        $parser->setBreaksEnabled(true);
        
        return $parser->text(Convert::raw2xml($markdown));
    }
    
    /**
     * Handles uploading of images for the editor
     * @param {SS_HTTPRequest} $request HTTP Request
     * @return {mixed} Response
     */
    public function image_upload(HTTPRequest $request) {
        //If image support is not enabled block the request
        if($this->_imageSupportEnabled==false) {
            return $this->httpError(403);
        }
        
        //Validate the security token
        if(!SecurityToken::inst()->checkRequest($request)) {
            return $this->httpError(403);
        }
        
        
        //Initialize the upload handler
        $upload=new Upload();
        
        //Restrict to images
        $upload->getValidator()->setAllowedExtensions(array('jpg', 'gif', 'png'));
        
        //Set the max file size
        if($this->_maxUploadSize<=0) {
            // get the lower max size
            $maxUpload=File::ini2bytes(ini_get('upload_max_filesize'));
            $maxPost=File::ini2bytes(ini_get('post_max_size'));
            $upload->getValidator()->setAllowedMaxFileSize(min($maxUpload, $maxPost));
        }else {
            $upload->getValidator()->setAllowedMaxFileSize($this->_maxUploadSize);
        }
        
        //Attempt to load the uploaded files, note that Upload::load() handles validation of the upload
        if(array_key_exists('image', $_FILES)) {
            if($upload->load($_FILES['image'], $this->_uploadDir)) {
                $response=array(
                                'url'=>$upload->getFile()->getURL(),
                                'alt'=>$upload->getFile()->Title
                            );
            }else {
                $response=array(
                            'errors'=>$upload->getErrors()
                        );
            }
        }else {
            $response=array(
                            'errors'=>array(
                                            'No image was uploaded'
                                        )
                        );
        }
        
        
        //Set the response to json and encode the response
        header('Content-Type: application/json');
        return json_encode($response);
    }
    
    /**
     * Adds the image-support class to the classes if it is supported
     * @return {string} CSS class names
     * @see FormField::extraClass()
     */
    public function extraClass() {
        $classes=parent::extraClass();
        
        $classes=str_replace(preg_replace('/field$/', '', strtolower($this->class)), 'wbg-markdown', $classes);
        
        if($this->_imageSupportEnabled) {
            $classes.=' image-support';
        }
        
        return $classes;
    }
    
    /**
     * Enables or disables uploading of images
     * @param {bool} $value Boolean true for enabled false otherwise
     * @return {MarkdownField} Returns self
     */
    public function setImageUploadEnabled($value) {
        $this->_imageSupportEnabled=$value;
        return $this;
    }
    
    /**
     * Gets whether uploading of images is supported or not
     * @return {bool} Boolean true for enabled false otherwise
     */
    public function getImageUploadEnabled() {
        return $this->_imageSupportEnabled;
    }
    
    /**
     * Sets the upload destination folder for images
     * @param {string} $value Destination folder for images relative to /assets
     * @return {MarkdownField} Returns self
     */
    public function setUploadFolder($value) {
        $this->_uploadDir=$value;
        return $this;
    }
    
    /**
     * Gets the upload destination folder for images
     * @return {string} Destination folder for images relative to /assets
     */
    public function getUploadFolder() {
        return $this->_uploadDir;
    }
    
    /**
     * Sets the upload max file size for images
     * @param {int|array} $value Maximum upload file size for images in bytes, could be an array of file extensions to sizes
     * @return {MarkdownField} Returns self
     */
    public function setUploadMaxSize($value) {
        $this->_maxUploadSize=$value;
        return $this;
    }
    
    /**
     * Gets the upload max file size for images
     * @return {int|array} Maximum upload file size for images in bytes, could be an array of file extensions to sizes
     */
    public function getUploadMaxSize() {
        return $this->_maxUploadSize;
    }
}
?>