<?php

	session_start();

require 'vendor/autoload.php';
// include autoloader and other libraries

require_once 'dompdf/autoload.inc.php';//important
require_once 'dompdf/lib/html5lib/Parser.php';
require_once 'dompdf/lib/php-font-lib/src/FontLib/Autoloader.php';
require_once 'dompdf/lib/php-svg-lib/src/autoload.php';
require_once 'dompdf/src/Autoloader.php';
Dompdf\Autoloader::register();

// reference the Dompdf namespace

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('defaultFont', 'Courier');

//initialize dompdf class
$document = new Dompdf($options);

//html/css content
$contract = $_SESSION['contr1'];

$document->loadHtml($contract);

//or load html from file
//$page = file_get_contents("pagename.html");
//$document->loadHtml($page);


//set page size and orientation

$document->setPaper('A4', 'landscape');

//Render the HTML as PDF

$document->render();

//Get output of generated pdf in Browser
//Change "TestName" with the actual name of your output pdf file
$document->stream("Contract", array("Attachment"=>1));
//1  = Download
//0 = Preview


?>