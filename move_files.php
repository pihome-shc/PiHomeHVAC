<?php
function moveFolderFiles($dir){
    $ffs = scandir($dir);

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    // prevent empty ordered elements
    if (count($ffs) < 1)
        return;

    foreach($ffs as $ff){
        // Move everything in the directory except the placeholder file
        if (strcmp($ff, 'updates.txt') !== 0) {
                // Check if db_config.ini has been updated and if so update the version and build values in the 'system' table
                if (strcmp($ff, 'db_config.ini') === 0) {
                        $settings = parse_ini_file($dir.'/db_config.ini');
                        foreach ($settings as $key => $setting) {
                                // Notice the double $$, this tells php to create a variable with the same name as key
                                $$key = $setting;
                        }
                        $conn = new mysqli($hostname, $dbusername, $dbpassword);
                        $db_selected = mysqli_select_db($conn, $dbname);
                        $query = "UPDATE `system` SET `version`='".$version."',`build`='".$build."';";
                        $conn->query($query);
                }
                // Move updated files propper locations
                $cmd = 'mv '.$dir.'/'.$ff.' /var/www';
                exec($cmd);
        }
        if(is_dir($dir.'/'.$ff)) moveFolderFiles($dir.'/'.$ff);
    }
}

moveFolderFiles('/var/www/code_updates');
?>
