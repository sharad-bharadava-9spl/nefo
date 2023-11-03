
<?php
session_start();
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
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
$options->set('isRemoteEnabled', TRUE);
$options->set('debugKeepTemp', TRUE);
$options->set('isHtml5ParserEnabled', TRUE);

//initialize dompdf class
$document = new Dompdf($options,array('enable_remote' => true));

//html/css content
$contract = $_SESSION['contr1'];


$dompdf = new DOMPDF();

$dompdf->loadHtml($contract);

//or load html from file
//$page = file_get_contents("pagename.html");
//$document->loadHtml($page);


//set page size and orientation

$dompdf->setPaper('A4', 'landscape');

//Render the HTML as PDF

$dompdf->render();

//Get output of generated pdf in Browser
//Change "TestName" with the actual name of your output pdf file
$dompdf->stream("Contract", array("Attachment"=>1));
//1  = Download
//0 = Preview


?>