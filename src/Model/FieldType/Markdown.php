<?php
namespace WebbuildersGroup\DeploymentNotes\Model\FieldType;

use ParsedownExtra;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Flushable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBText;


class Markdown extends DBText implements Flushable {
    private static $casting=array(
                                'AsHTML'=>'HTMLText',
                                'Markdown'=>'Text'
                            );
    
    
    private static $escape_type='xml';
    
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
        $cache=Injector::inst()->get(CacheInterface::class . '.deployment-notes-markdown');
        
        //Check cache, if it's good use it instead
        if($cache->has($cacheKey)) {
            $this->parsedHTML=$cache->get($cacheKey);
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
        $this->parsedHTML=$parser->text(Convert::raw2xml($this->value));
        
        //Cache response to file system
        $cache->set($cacheKey, $this->parsedHTML);
        
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
    
    /**
     * Flushes the caches of markdown
     */
    public static function flush() {
        Injector::inst()->get(CacheInterface::class . '.deployment-notes-markdown')->clear();
    }
}
?>