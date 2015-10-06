<?php
if (defined('TV_MEDIA') === false) {
    die('Access Not Allower.');
}
class fromIGNTech extends fromMedia {
    public function __construct() {
        $this->isHtml    = true;
        $this->source    = 'br.ign.com';
        $this->src       = 'http://br.ign.com/';
        $this->itemName  = array('tagName' => 'li', 'class' => 'NEWS');
        $this->href      = array('tagName' => 'a', 'attribute' => 'href');
        $this->img       = array('tagName' => 'style');
        $this->titulo    = array('tagName' => 'h3');
        $this->subtitulo = array('tagName' => 'p');
        $this->datetime  = array('tagName' => 'time', 'attribute' => 'datetime');
        $this->content   = array('tagName' => 'article');
        parent::__construct();
    }
    
    /**
     * 
     * @param int $stop
     * @return array
     */
    public function getItens($stop = 10, $function = null) {
        $function = function(&$item) {
            preg_match('~url\(\'([^\']+)\'\)~', $item['img'], $aux);
            $img = $aux[1];
            $img300 = str_replace('.150.', '.1280.', $img);
            $item['thumbnail'] = $img;
            $item['img'] = $img300;
            
            $item['titulo'] = strip_tags($item['titulo']);
            
            $subtitulo = str_replace('Leia Mais', '', $item['subtitulo']);
            $subtitulo = explode("\n", $subtitulo);
            $item['subtitulo'] = $subtitulo[1];
        };
        return parent::getItens($stop, $function);
    }
}