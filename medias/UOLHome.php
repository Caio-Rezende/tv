<?php
if (defined('TV_MEDIA') === false) {
    die('Access Not Allower.');
}
class fromUOLHome extends fromMedia {
    public function __construct() {
        $this->isHtml       = false;
        $this->source       = 'home.uol.com.br';
        $this->src          = 'http://rss.home.uol.com.br/index.xml';
        $this->itemName     = array('tagName' => 'item');
        $this->href         = array('tagName' => 'link');
        $this->img          = array(
            'tagName' => 'content', 
            'attribute' => 'url', 
            'namespace' => 'http://search.yahoo.com/mrss/'
        );
        $this->titulo       = array('tagName' => 'title');
        $this->subtitulo    = array(
            'tagName' => 'encoded',
            'namespace' => 'http://purl.org/rss/1.0/modules/content/'
        );
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
            $item['subtitulo'] = html_entity_decode(strip_tags($item['subtitulo']));
        };
        return parent::getItens($stop, $function);
    }
}