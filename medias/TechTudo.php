<?php
if (defined('TV_MEDIA') === false) {
    die('Access Not Allower.');
}
class fromTechTudo extends fromMedia {
    public function __construct() {
        $this->isHtml    = true;
        $this->source    = 'techtudo.com.br';
        $this->src       = 'http://www.techtudo.com.br/plantao.html';
        $this->itemName  = array('tagName' => 'article');
        $this->href      = array('tagName' => 'a', 'attribute' => 'href');
        $this->img       = array('tagName' => 'img', 'attribute' => 'src');
        $this->titulo    = array('tagName' => 'h2');
        $this->subtitulo = array('tagName' => 'p');
        $this->datetime  = array('tagName' => 'time', 'attribute' => 'datetime');
        parent::__construct();
    }
    
    /**
     * 
     * @param int $stop
     * @return array
     */
    public function getItens($stop = 10) {
        $fn = function(&$item) {
            $item['ts']       = strtotime($item['datetime']);
            $item['datetime'] = date($this->patternDate, $item['ts']);
        };
        return parent::getItens($stop, $fn);
    }
}