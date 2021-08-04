<?php
function moveFolderFiles($dir){
    $ffs = scandir($dir);

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    // prevent empty ordered elements
    if (count($ffs) < 1)
        return;

    foreach($ffs as $ff){
        if (strcmp($ff, 'updates.txt') !== 0) {
                $cmd = 'mv '.$dir.'/'.$ff.' /var/www_test';
                exec($cmd);
        }
        if(is_dir($dir.'/'.$ff)) moveFolderFiles($dir.'/'.$ff);
    }
}

moveFolderFiles('/var/www/code_updates');
?>
