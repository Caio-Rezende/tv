<?php
define('TV_MEDIA', true);
define('MEDIA_DIR', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'medias' . DIRECTORY_SEPARATOR);
abstract class fromMedia {
    public $source = '';
    
    protected $src          = '';
    protected $itemName     = '';
    protected $isHtml       = false;
    protected $allowContent = true;
    protected $href         = array();
    protected $titulo       = array();
    protected $subtitulo    = array();
    protected $img          = array();
    protected $thumbnail    = array();
    protected $datetime     = array();
    protected $content      = array();
    
    public $patternDate = 'd/m/Y H:i';
    
    protected $lastItens  = array();
    //segundos
    public $lastTS     = 0;
    public $reloadTime = 0;
    
    protected $cacheFolder   = '';
    protected $cacheFileName = '';
    protected $cacheFile     = null;
    
    public function __construct() {
        $this->cacheFolder   = '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        $this->cacheFileName = $this->cacheFolder . preg_replace('~[^\w]~', '_', $this->source);
        
        if (file_exists($this->cacheFileName)) {
            $this->cacheFile  = json_decode(file_get_contents($this->cacheFileName), true);
            $this->lastTS     = $this->cacheFile['ts'];
            $this->lastItens  = $this->cacheFile['itens'];
            $this->reloadTime = $this->cacheFile['reloadTime'];
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
        $doc = $this->getDoc($this->src, $this->isHtml);
        $articles = $doc->getElementsByTagName($this->itemName['tagName']);
        
        $prevReloadTime   = $this->reloadTime;
        $this->lastItens  = array();
        $this->reloadTime = 0;
        $weight = 0;
        foreach($articles as $article) {
            $this->debug($article->ownerDocument->saveXML(), 'feed');
            if (array_key_exists('class', $this->itemName) && $article->getAttribute('class') != $this->itemName['class']) {
                continue;
            }
            
            /* @var $article DOMElement */
            $item = array(
                'source'       => $this->source,
                'href'         => $this->getValue($article, $this->href),
                'img'          => $this->getValue($article, $this->img),
                'titulo'       => $this->getValue($article, $this->titulo),
                'subtitulo'    => $this->getValue($article, $this->subtitulo),
                'datetime'     => $this->getValue($article, $this->datetime),
                'allowContent' => $this->allowContent
            );
            
            if ($function) {
                $function($item);
            }
            $item['ts']       = strtotime($item['datetime']);
            $item['datetime'] = date($this->patternDate, $item['ts']);
            
            if (!$item['ts']) {
                continue;
            }
            
            if (count($this->lastItens) > 0) {
                $weight += count($this->lastItens);
                $diff = ($this->lastItens[count($this->lastItens) - 1]['ts'] - $item['ts']);
                //With this sum of the elements all the times, so the first ones will be more weighted
                $this->reloadTime += $diff;
            }
            
            $this->lastItens[] = $item;
        }
        
        if ($weight > 0) {
            //With this division we make sure we have the weight average of the diffs
            $this->reloadTime = floatval($this->reloadTime / $weight);
        } else {
            $this->reloadTime = ($prevReloadTime > 0 ? $prevReloadTime * 2 : 30);
        }
        
        $this->save();
        return array_splice($this->lastItens, 0, $stop);
    }
    
    /**
     * 
     * @param string $link
     * @return string
     */
    public function getContent($link) {
        if (!$this->allowContent) {
            return '';
        }
        $doc = $this->getDoc($link);
        $html = $this->getInnerHTML($doc, $this->content);
        
        $this->debug($html, 'content');
        
        $html = strip_tags($html, '<header></header><h1></h1><div></div><img><span></span><a></a><br><article></article><p></p><em></em>');
        return $html;
    }
    
    /**
     * 
     * @param DOMDocument $doc
     * @param array $aProperty
     * @return string
     */
    protected function getInnerHTML($doc, $aProperty) { 
        $tagName   = $aProperty['tagName'];
        $class = (array_key_exists('class', $aProperty) ? $aProperty['class'] : false);
        
        $node = null;
        $tag = $doc->getElementsByTagName($tagName);
        if ($class !== false) {
            foreach ($tag as $node) {
                if ($node->getAttribute('class') != $class) {
                    continue;
                }
                break;
            }
        } else {
            if ($tag->length > 0) {
                $node = $tag[0];
            }
        }
        if ($node !== null) {
            $this->debug($node->ownerDocument->saveXML(), 'contentRaw');
            return $node->ownerDocument->saveXML($node); 
        }

        return '';
    } 
    
    /**
     * 
     * @param DOMNode $article
     * @param array $aProperty
     * @return mixed
     */
    protected function getValue($article, $aProperty) {
        if (is_array($aProperty) === false) {
            return '';
        }
        $value = null;
        $tagName   = $aProperty['tagName'];
        $attribute = (array_key_exists('attribute', $aProperty) ? $aProperty['attribute'] : false);
        $namespace = (array_key_exists('namespace', $aProperty) ? $aProperty['namespace'] : false);
        $hasAttribute = (array_key_exists('hasAttribute', $aProperty) ? $aProperty['hasAttribute'] : false);
        
        if ($namespace) {
            $tag = $article->getElementsByTagNameNS($tagName, $namespace);
        } else {
            $tag = $article->getElementsByTagName($tagName);
        }
        if ($tag->length == 0) {
            $tagName = strtolower($tagName);
            if ($namespace) {
                $tag = $article->getElementsByTagNameNS($tagName, $namespace);
            } else {
                $tag = $article->getElementsByTagName($tagName);
            }
        }
        if ($tag->length) {
            $valueTag = null;
            if ($hasAttribute) {
                foreach ($tag as $auxTag) {
                    /* @var $auxTag DOMNode */
                    if ($auxTag->attributes->length > 0) {
                        $node = $auxTag->attributes->getNamedItem($hasAttribute['name']);
                        if ($node && (!array_key_exists('value', $hasAttribute) || $node->nodeValue == $hasAttribute['value'])) {
                            $valueTag = $auxTag;
                            break;
                        }
                    }
                }
            } else {
                $valueTag = $tag[0];
            }
            if ($valueTag) {
                if ($attribute) {
                    $value = $valueTag->getAttribute($attribute);
                } else {
                    $value = $valueTag->nodeValue;
                }
            }
        }
        return $value;
    }
    
    /**
     * 
     */
    protected function save() {
        $this->lastTS = time();
        if (file_exists($this->cacheFileName) || is_writable($this->cacheFolder)) {
            file_put_contents($this->cacheFileName, json_encode(array(
                'ts'         => $this->lastTS,
                'itens'      => $this->lastItens,
                'reloadTime' => $this->reloadTime
            )));
        }
    }
    
    /**
     * 
     * @param string $content
     * @param string $name
     */
    private function debug($content, $name) {
        $debugFolder = $this->cacheFolder   = '..' . DIRECTORY_SEPARATOR . 'debug' . DIRECTORY_SEPARATOR;
        
        $cacheFileName = $this->cacheFileName;
        $cacheLastItens = $this->lastItens;
        $this->cacheFileName = $debugFolder . $name;
        $this->lastItens = $content;
        $this->save();
        $this->cacheFileName = $cacheFileName;
        $this->lastItens = $cacheLastItens;
    }
    
    /**
     * 
     * @param string $link
     * @param bool $isHTML
     * @return DOMDocument
     */
    private function getDoc($link, $isHTML = true) {
        ob_start();
        $doc = new DOMDocument('1.0', 'utf-8');
        if ($isHTML) {
            //ignorando warnings do parse
            $doc->loadHTMLFile($link);
        } else {
            //ignorando warnings do parse
            $doc->load($link);
        }
        $this->debug(ob_get_clean(), 'getDoc');
        return $doc;
    }
}