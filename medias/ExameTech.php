<?php
if (defined('TV_MEDIA') === false) {
    die('Access Not Allower.');
}
class fromExameTech extends fromMedia {
    public function __construct() {
        $this->isHtml    = false;
        $this->source    = 'exame.com';
        $this->src       = 'http://feeds.feedburner.com/Exame-Tecnologia';
        $this->itemName  = array('tagName' => 'item');
        $this->href      = array('tagName' => 'link');
        $this->img       = array('tagName' => 'enclosure', 'attribute' => 'url');
        $this->titulo    = array('tagName' => 'title');
        $this->subtitulo = array('tagName' => 'description');
        $this->datetime  = array('tagName' => 'pubDate');
        $this->content   = array('tagName' => 'div', 'class' => 'main-container');
        parent::__construct();
    }
    
    /**
     * 
     * @param int $stop
     * @return array
     */
    public function getItens($stop = 10, $function = null) {
        $function = function(&$item) {
            $aux = html_entity_decode($item['subtitulo']);
            $aux = strip_tags($aux, '<p></p>');
            
            $item['subtitulo'] = str_replace(array('<p>', '</p>'), array(''), $aux);
        };
        return parent::getItens($stop, $function);
    }
}