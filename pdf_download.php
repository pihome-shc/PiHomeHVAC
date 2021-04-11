<?php

// The location of the PDF file
// on the server
$file = basename($_GET['file']);
$filename = '/var/www/documentation/pdf_format/'.$file;

// Header content type
header("Content-type: application/pdf");

header("Content-Length: " . filesize($filename));

// Send the file to the browser.
readfile($filename);
?>
