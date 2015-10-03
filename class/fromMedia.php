<?php
define('TV_MEDIA', true);
abstract class fromMedia {
    protected $isHtml    = false;
    public $source    = '';
    protected $src       = '';
    protected $itemName  = '';
    protected $href      = array();
    protected $titulo    = array();
    protected $subtitulo = array();
    protected $img       = array();
    protected $datetime  = array();
    
    protected $patternDate = 'd/m/Y H:i';
    
    protected $lastTS     = 0;
    protected $lastItens  = array();
    //segundos
    protected $reloadTime = 5 * 60;
    
    protected $cacheFileName = '';
    protected $cacheFile     = null;
    
    public function __construct() {
        $this->cacheFileName = '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . preg_replace('~[^\w]~', '_', $this->source);
        
        if (file_exists($this->cacheFileName)) {
            $this->cacheFile = json_decode(file_get_contents($this->cacheFileName), true);
            $this->lastTS    = $this->cacheFile['ts'];
            $this->lastItens = $this->cacheFile['itens'];
        }
    }

    /**
     * 
     * @param int $stop
     * @param callable $function
     * @return array
     */
    protected function getItens($stop = 10, $function = null) {
        if ($this->lastTS && count($this->lastItens) > 0 && $this->lastTS + $this->reloadTime > time()) {
            return $this->lastItens;
        }
        $doc = new DOMDocument();
        if ($this->isHtml) {
            //ignorando warnings do parse
            @$doc->loadHTMLFile($this->src);
        } else {
            //ignorando warnings do parse
            @$doc->load($this->src);
        }
        $articles = $doc->getElementsByTagName($this->itemName['tagName']);
        
        $this->lastItens = array();
        foreach($articles as $article) {
            if (array_key_exists('class', $this->itemName) && $article->getAttribute('class') != $this->itemName['class']) {
                continue;
            }
            
            /* @var $article DOMElement */
            $item = array(
                'source'    => $this->source,
                'href'      => $this->getValue($article, $this->href),
                'img'       => $this->getValue($article, $this->img),
                'titulo'    => $this->getValue($article, $this->titulo),
                'subtitulo' => $this->getValue($article, $this->subtitulo),
                'datetime'  => $this->getValue($article, $this->datetime)
            );
            
            if ($function) {
                $function($item);
            }
            
            $this->lastItens[] = $item;
        }
        
        $this->save();
        return array_splice($this->lastItens, 0, $stop);
    }
    
    /**
     * 
     * @param DOMElement $article
     * @param array $aProperty
     * @return mixed
     */
    protected function getValue($article, $aProperty) {
        $value = null;
        $tagName   = $aProperty['tagName'];
        $attribute = (array_key_exists('attribute', $aProperty) ? $aProperty['attribute'] : false);
        
        $tag = $article->getElementsByTagName($tagName);
        if ($tag->length) {
            if ($attribute) {
                $value = $tag[0]->getAttribute($attribute);
            } else {
                $value = $tag[0]->nodeValue;
            }
        }
        return $value;
    }
    
    protected function save() {
        $this->lastTS = time();
        file_put_contents($this->cacheFileName, json_encode(array(
            'ts'    => $this->lastTS,
            'itens' => $this->lastItens
        )));
    }
}