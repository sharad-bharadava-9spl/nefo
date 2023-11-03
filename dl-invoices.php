<?php

	session_start();
	$domain = $_SESSION['domain'];

$rootPath = realpath("_club/_$domain/invoices");
// Initialize archive object
$zip = new ZipArchive();
$zip->open("_club/_$domain/invoices/facturas.zip", ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file)
{
    // Skip directories (they would be added automatically)
    if (!$file->isDir())
    {
	    if (!strstr($file,'.html') && !strstr($file,'.zip') && !strstr($file,'.php')) {
	        // Get real and relative path for current file
	        $filePath = $file->getRealPath();
	        $relativePath = substr($filePath, strlen($rootPath) + 1);
	
	        // Add current file to archive
	        $zip->addFile($filePath, $relativePath);
        }
    }
}

// Zip archive will be created only after closing object
$zip->close();
    header('Content-Type: application/zip');
    header("Content-Disposition: attachment; filename='facturas.zip'");
    header('Content-Length: ' . filesize($zipname));
    header("Location: _club/_$domain/invoices/facturas.zip");