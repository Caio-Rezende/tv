<?php
if (defined('TV_MEDIA') === false) {
    die('Access Not Allower.');
}
class fromUOLEconomia extends fromMedia {
    public function __construct() {
        $this->isHtml       = false;
        $this->source       = 'uol.com.br/economia';
        $this->src          = 'http://rss.uol.com.br/feed/economia.xml';
        $this->itemName     = array('tagName' => 'item');
        $this->href         = array('tagName' => 'link');
        $this->img          = null;
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
        $months = array(
            'Fev' => 'Feb',
            'Abr' => 'Apr',
            'Mai' => 'May',
            'Ago' => 'Aug',
            'Set' => 'Sep',
            'Out' => 'Oct',
            'Dez' => 'Dec'
        );
        $function = function(&$item) use ($months) {
            $item['subtitulo'] = strip_tags($item['subtitulo']);
            $aux = explode(',', $item['datetime']);
            $aux = str_replace(array_keys($months), array_values($months), $aux[1]);
            $item['datetime'] = $aux;
        };
         return parent::getItens($stop, $function);
    }
}