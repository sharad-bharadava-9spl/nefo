<?php

		
		require 'pdfcrowd.php';

    // create the API client instance
    $client = new \Pdfcrowd\HtmlToPdfClient("Berrern", "f9ad67996030f478ade7c6295a0b533d");

	$html = "<html><body><h1>Hello World!</h1></body></html>";
    $pdf = $client->convertString($html);
    header('Content-Type: application/pdf');
    header('Cache-Control: no-cache');
    header('Accept-Ranges: none');
    header("Content-Disposition: attachment; filename=\"contrato.pdf\"");
    // return the final PDF in the response
    echo $pdf;