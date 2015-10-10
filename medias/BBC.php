<?php
if (defined('TV_MEDIA') === false) {
    die('Access Not Allower.');
}
class fromBBC extends fromMedia {
    public function __construct() {
        $this->isHtml    = false;
        $this->source    = 'bbc.com';
        $this->src       = 'http://www.bbc.com/portuguese/index.xml';
        $this->itemName  = array('tagName' => 'entry');
        $this->href      = array('tagName' => 'link', 'attribute' => 'href');
        $this->img       = array('tagName' => 'img', 'attribute' => 'src');
        $this->titulo    = array('tagName' => 'title');
        $this->subtitulo = array('tagName' => 'summary');
        $this->datetime  = array('tagName' => 'updated');
        $this->content   = array('tagName' => 'div', 'class' => 'column--primary');
        parent::__construct();
    }
    
    /**
     * 
     * @param int $stop
     * @return array
     */
    public function getItens($stop = 10, $function = null) {
        $function = function(&$item) {
            $item['href']     = str_replace('tag:', 'http://', $item['href']);
        };
        return parent::getItens($stop, $function);
    }
}