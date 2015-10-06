<?php
$stop = 10;
$sources = array(
    'BBCTech',
    'ExameTech',
    'TechTudo',
    'IGNTech'
);
if (isset($_REQUEST) 
    && count($_REQUEST) > 0
) {
    if (array_key_exists('sources', $_REQUEST) 
        && is_array($_REQUEST['sources'])
        && count($_REQUEST['sources']) > 0
    ) {
        $sources = $_REQUEST['sources'];
    }
    
    if (array_key_exists('stop', $_REQUEST) 
        && intval($_REQUEST['stop']) == $_REQUEST['stop']
        && $_REQUEST['stop'] > 0
        && $_REQUEST['stop'] < 100
    ) {
        $stop = $_REQUEST['stop'];
    }
}

include_once '../class/fromMedia.php';

$array = array();
foreach ($sources as $source) {
    $fileName = MEDIA_DIR . $source . '.php';
    if (file_exists($fileName) === false) {
        continue;
    }
    include_once $fileName;
    $source = 'from' . $source;
    $class = new $source();
    /** @var $class fromMedia */
    $array = array_merge($array, $class->getItens());
}

usort($array, function($a, $b){
    return $b['ts'] - $a['ts'];
});

echo json_encode(array_splice($array, 0, $stop));