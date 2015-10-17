<?php
define('PUBLICITY_DIR', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'publicities' . DIRECTORY_SEPARATOR);

$dir = array();
if (is_dir(PUBLICITY_DIR))
    $dir = scandir(PUBLICITY_DIR);

$publicities = array();
foreach ($dir as $file) {
    //only images
    if (strpos($file, '.png') !== false 
        || strpos($file, '.gif') !== false 
        || strpos($file, '.jpeg') !== false 
        || strpos($file, '.jpg') !== false 
        || strpos($file, '.svg') !== false
    ) {
        //url to the image
        $publicities[] = './publicities/' . $file;
    }
}
echo json_encode($publicities);
    