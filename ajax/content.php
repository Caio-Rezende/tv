<?php
include '../class/fromMedia.php';

$response = '';
if (isset($_REQUEST) 
    && count($_REQUEST) > 0
    && array_key_exists('media', $_REQUEST)
    && $_REQUEST['media'] != ''
    && array_key_exists('link', $_REQUEST)
    && $_REQUEST['link'] != ''
) {
    $filename = MEDIA_DIR . DIRECTORY_SEPARATOR . $_REQUEST['media'] . '.php';
    if (file_exists($filename)) {
        include $filename;
        $className = 'from' . $_REQUEST['media'];
        $class = new $className();
        /* @var $class fromMedia */
        $response = $class->getContent($_REQUEST['link']);
    }
}
echo $response;