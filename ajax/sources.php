<?php
include '../class/fromMedia.php';

$dir = array();
if (is_dir(MEDIA_DIR))
    $dir = scandir(MEDIA_DIR);

$sources = array();
foreach ($dir as $file) {
    if (strpos($file, '.php')) {
        include MEDIA_DIR . DIRECTORY_SEPARATOR . $file;
        $className = 'from' . str_replace('.php', '', $file);
        $class = new $className();
        /* @var $class fromMedia */
        $sources[] = array(
            'name'     => str_replace('.php', '', $file),
            'source'   => $class->source,
            'lastDate' => date($class->patternDate, $class->lastTS),
            'nextDate' => date($class->patternDate, $class->lastTS + $class->reloadTime)
        );
    }
}
echo json_encode($sources);
    