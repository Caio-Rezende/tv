<?php
if (defined('TV_MEDIA') === false) {
    die('Access Not Allower.');
}
class fromTecmundo extends fromMedia {
    public function __construct() {
        $this->isHtml       = false;
        $this->source       = 'tecmundo.com.br';
        $this->src          = 'http://rss.tecmundo.com.br/feed';
        $this->itemName     = array('tagName' => 'item');
        $this->href         = array('tagName' => 'link');
        $this->img          = array('tagName' => 'enclosure', 'attribute' => 'url');
        $this->titulo       = array('tagName' => 'title');
        $this->subtitulo    = array('tagName' => 'description');
        $this->datetime     = array('tagName' => 'pubDate');
        $this->allowContent = false;
        parent::__construct();
    }
    
    /**
     * 
     * @param int $stop
     * @return array
     */
    public function getItens($stop = 10, $function = null) {
        $function = function(&$item) {
            $item['subtitulo'] = html_entity_decode (strip_tags($item['subtitulo']));
        };
        return parent::getItens($stop, $function);
    }
}