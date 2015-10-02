<?php
if (defined('TV_MEDIA') === false) {
    die('Access Not Allower.');
}
class fromBBCTech extends fromMedia {
    public function __construct() {
        $this->isHtml    = false;
        $this->source    = 'bbc.com';
        $this->src       = 'http://www.bbc.com/portuguese/topicos/ciencia_e_tecnologia/index.xml';
        $this->itemName  = array('tagName' => 'entry');
        $this->href      = array('tagName' => 'id');
        $this->img       = array('tagName' => 'img', 'attribute' => 'src');
        $this->titulo    = array('tagName' => 'title');
        $this->subtitulo = array('tagName' => 'summary');
        $this->datetime  = array('tagName' => 'updated');
        parent::__construct();
    }
    
    /**
     * 
     * @param int $stop
     * @return array
     */
    public function getItens($stop = 10) {
        $fn = function(&$item) {
            $item['href']     = str_replace('tag:', 'http://', $item['href']);
            $item['ts']       = strtotime($item['datetime']);
            $item['datetime'] = date($this->patternDate, $item['ts']);
        };
        return parent::getItens($stop, $fn);
    }
}