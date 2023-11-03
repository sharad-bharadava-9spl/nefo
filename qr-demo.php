<?php
// (A) LOAD QR CODE LIBRARY
require "vendor/autoload.php";
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Label\Label;

// (B) CREATE QR CODE
$qr = QrCode::create("QRR81REJ3AY7L4XI67")
  // (B1) CORRECTION LEVEL
  ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
  // (B2) SIZE & MARGIN
  ->setSize(300)
  ->setMargin(10)
  // (B3) COLORS
  ->setForegroundColor(new Color(0, 0, 0))
  ->setBackgroundColor(new Color(255, 255, 255));

// (B4) ATTACH LOGO
$logo = Logo::create(__DIR__ . "/qr-logo.png")
  ->setResizeToWidth(120);

// (B5) ATTACH LABEL
/*$label = Label::create("CODE BOXX")
  ->setTextColor(new Color(0, 0, 0));*/

// (C) OUTPUT QR CODE
$writer = new PngWriter();
$result = $writer->write($qr, $logo);
header("Content-Type: " . $result->getMimeType());
echo $result->getString();
