<?php
	
require_once 'cOnfig/connection.php';
require_once 'cOnfig/view.php';
require_once 'cOnfig/authenticate.php';
require_once 'cOnfig/languages/common.php';

session_start();
$accessLevel = '3';

// Authenticate & authorize
authorizeUser($accessLevel);

/** Include PHPExcel */
require_once dirname(__FILE__) . '/vendor/PHPExcel/Classes/PHPExcel.php';
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
// Set document properties
$objPHPExcel->getProperties()->setCreator("Lokesh Nayak")
                                                 ->setLastModifiedBy("Lokesh Nayak")
                                                 ->setTitle("Test Document")
                                                 ->setSubject("Test Document")
                                                 ->setDescription("Test document for PHPExcel")
                                                 ->setKeywords("office")
                                                 ->setCategory("Test result file");
// Add some data
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()
            ->setCellValue('A1', 'Month to Month');
$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$startIndex = 2;
for ($a = 0; $a < 8; $a++) {
    if ($a == 0) {
            $dateOperator = "MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
            $timestamp = $lang['dispensary-thismonth'];
    } else {
            $dateOperator = "MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
            $timestamp = date("m-Y", strtotime("-$a months", strtotime("first day of this month") ));
    }

    // Look up todays sales
    $selectSales = "SELECT SUM(amount), SUM(units), SUM(realQuantity) from sales WHERE $dateOperator";
    try
    {
        $result = $pdo3->prepare("$selectSales");
        $result->execute();
    }
    catch (PDOException $e)
    {
        $error = 'Error fetching user: ' . $e->getMessage();
        echo $error;
        exit();
    }

    $row = $result->fetch();
    $sales = number_format($row['SUM(amount)'],0);
    $units = number_format($row['SUM(units)'],0);
    $quantity = number_format($row['SUM(realQuantity)'],1);
    $objPHPExcel->getActiveSheet()
                ->setCellValue('A'.$startIndex, $timestamp.':');
    $objPHPExcel->getActiveSheet()
                ->setCellValue('B'.$startIndex, $quantity.'g.');
    $objPHPExcel->getActiveSheet()
                ->setCellValue('C'.$startIndex, $units.'u.');
    $objPHPExcel->getActiveSheet()
                ->setCellValue('D'.$startIndex, $sales);
    $startIndex++;
}
// Save Excel 2007 file
ob_end_clean();
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename = "report.xlsx"');
//header('Cache-Control: max-age = 0');
$objWriter->save('php://output');
//exit();
