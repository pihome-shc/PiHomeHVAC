<?php

// The location of the PDF file
// on the server
$file = basename($_GET['file']);
$filename = '/var/www/documentation/pdf_format/'.$file;

$pdf = file_get_contents($filename);
header('Content-Type: application/pdf');
header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Content-Length: '.strlen($pdf));
header('Content-Disposition: inline; filename="'.basename($PDFfilename).'";');
ob_clean();
flush();
echo $pdf;
?>
