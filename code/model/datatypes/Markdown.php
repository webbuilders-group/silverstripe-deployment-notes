<?php
namespace WebbuildersGroup\DeploymentNotes\model\datatypes;

class Markdown extends \Text {
    private static $casting=array(
                                'AsHTML'=>'HTMLText',
                                'Markdown'=>'Text'
                            );
    
    
    public static $escape_type='xml';
    
    protected $parsedHTML=false;
    
    
    /**
     * Checks cache to see if the contents of this field have already been parsed, if they haven't then Parsdown is used to render the markdown
     * @param {bool} $interactive Should any rendered inputs be marked as readonly?
     * @return {string} Markdown rendered as HTML
     */
    public function AsHTML($interactive=true) {
        if($this->parsedHTML!==false) {
            return $this->parsedHTML;
        }
        
        //Init cache stuff
        $cacheKey=md5('Markdown_'.$this->tableName.'_'.$this->name.'_'.($interactive ? 'readonly':'interactive').':'.$this->value);
        $cache=\SS_Cache::factory('Markdown');
        $cachedHTML=$cache->load($cacheKey);
        
        //Check cache, if it's good use it instead
        if($cachedHTML!==false) {
            $this->parsedHTML=$cachedHTML;
            return $this->parsedHTML;
        }
        
        //If empty save time by not attempting to render
        if(empty($this->value)) {
            return $this->value;
        }
        
        
        //Get rendered HTML
        $parser=new \ParsedownExtra();
        $parser->setBreaksEnabled(true);
        
        //Store response in memory
        $this->parsedHTML=$parser->text(\Convert::raw2xml($this->value));
        
        //Cache response to file system
        $cache->save($this->parsedHTML, $cacheKey);
        
        //Return response
        return $this->parsedHTML;
    }
    
    /**
     * Gets the raw markdown
     * @return {string}
     */
    public function getMarkdown() {
        return $this->value;
    }
    
    /**
     * Renders the field used in the template
     * @return {string} HTML to be used in the template
     */
    public function forTemplate() {
        return $this->AsHTML();
    }
}
?>