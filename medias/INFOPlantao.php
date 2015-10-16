<?php
if (defined('TV_MEDIA') === false) {
    die('Access Not Allower.');
}
class fromINFOPlantao extends fromMedia {
    public function __construct() {
        $this->isHtml    = false;
        $this->source    = 'info.abril.com.br';
        $this->src       = 'http://feeds.feedburner.com/Plantao-INFO?format=xml';
        $this->itemName  = array('tagName' => 'entry');
        $this->href      = array('tagName' => 'id');
        $this->img       = array('tagName' => 'link', 'attribute' => 'href', 'hasAttribute' => array('name' => 'rel', 'value' => 'enclosure'));
        $this->titulo    = array('tagName' => 'title');
        $this->subtitulo = array('tagName' => 'summary');
        $this->datetime  = array('tagName' => 'updated');
        $this->content   = array('tagName' => 'div', 'class' => 'main-content');
        parent::__construct();
    }
    
    /**
     * 
     * @param int $stop
     * @return array
     */
    public function getItens($stop = 10, $function = null) {
        return parent::getItens($stop, $function);
    }
}