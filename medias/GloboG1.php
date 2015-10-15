<?php
if (defined('TV_MEDIA') === false) {
    die('Access Not Allower.');
}
class fromGloboG1 extends fromMedia {
    public function __construct() {
        $this->isHtml    = false;
        $this->source    = 'g1.globo.com';
        $this->src       = 'http://g1.globo.com/dynamo/rss2.xml';
        $this->itemName  = array('tagName' => 'item');
        $this->href      = array('tagName' => 'link');
        $this->img       = false;
        $this->titulo    = array('tagName' => 'title');
        $this->subtitulo = array('tagName' => 'description');
        $this->datetime  = array('tagName' => 'pubDate');
        $this->content   = array('tagName' => 'div', 'class' => 'hfeed hentry');
        parent::__construct();
    }
    
    /**
     * 
     * @param int $stop
     * @return array
     */
    public function getItens($stop = 10, $function = null) {
        $function = function(&$item) {
            preg_match('~src=(["\'])([^\'"]+)\1~', $item['subtitulo'], $aux);
            if (count($aux) > 0) {
                $item['img']       = $aux[2];
            }
            $item['subtitulo'] = strip_tags($item['subtitulo'], '');
        };
        return parent::getItens($stop, $function);
    }
}