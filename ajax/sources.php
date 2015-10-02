<?php
$dirname = '..' . DIRECTORY_SEPARATOR . 'medias' . DIRECTORY_SEPARATOR;
$dir = array();
if (is_dir($dirname))
    $dir = scandir($dirname);

$sources = array();
include '../class/fromMedia.php';
foreach ($dir as $file) {
    if (strpos($file, '.php')) {
        include $dirname . DIRECTORY_SEPARATOR . $file;
        $className = 'from' . str_replace('.php', '', $file);
        $class = new $className();
        $sources[] = array(
            'name'   => str_replace('.php', '', $file),
            'source' => $class->source
        );
    }
}
echo json_encode($sources);
    