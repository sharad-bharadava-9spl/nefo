<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if ($_GET['exp'] == 'donations') {
		
		$select = "SELECT u.memberno, u.first_name, u.last_name, d.donationTime, d.amount, d.comment, d.creditBefore, d.creditAfter, d.donatedTo FROM donations d, users u WHERE u.user_id = d.userid AND d.donatedTo < 3";
		
		$filename = "Donaciones";
		
	} else if ($_GET['exp'] == 'fees') {
		
		$select = "SELECT u.memberno, u.first_name, u.last_name, m.paymentdate, m.amountPaid, m.comment, m.oldExpiry, m.newExpiry, m.paidTo FROM memberpayments m, users u WHERE u.user_id = m.userid AND m.paidTo < 3";
		
		$filename = "Cuotas";
		
	} else if ($_GET['exp'] == 'sales') {
		
		$select = "SELECT u.memberno, u.first_name, u.last_name, s.saletime, s.amount, s.unitsTot, s.adminComment, s.creditBefore, s.creditAfter FROM b_sales s, users u WHERE u.user_id = s.userid";
		
		$filename = "Ventas-bar";
		
	} else if ($_GET['exp'] == 'dispenses') {
		
		$select = "SELECT u.memberno, u.first_name, u.last_name, s.saletime, s.amount, s.quantity, s.units, s.adminComment, s.creditBefore, s.creditAfter FROM sales s, users u WHERE u.user_id = s.userid";
		
		$filename = "Retiradas";
		
	} else if ($_GET['exp'] == 'log') {
		
		$select = "SELECT u.memberno, u.first_name, u.last_name, l.logtype, l.logtime, l.amount, l.operator, l.oldExpiry, l.newExpiry, l.oldCredit, l.newCredit FROM log l, users u WHERE u.user_id = l.user_id";
		
		$filename = "Log";
		
	}
	

mysql_query('SET NAMES utf8;');
$export = mysql_query($select); 
//$fields = mysql_num_rows($export); // thanks to Eric
$fields = mysql_num_fields($export); // by KAOSFORGE

for ($i = 0; $i < $fields; $i++) {
    $col_title .= '<Cell ss:StyleID="2"><Data ss:Type="String">'.mysql_field_name($export, $i).'</Data></Cell>';
}

$col_title = '<Row>'.$col_title.'</Row>';

while($row = mysql_fetch_row($export)) {
    $line = '';
    foreach($row as $value) {
        if ((!isset($value)) OR ($value == "")) {
            $value = '<Cell ss:StyleID="1"><Data ss:Type="String"></Data></Cell>\t';
        } else {
            $value = str_replace('"', '', $value);
            $value = '<Cell ss:StyleID="1"><Data ss:Type="String">' . $value . '</Data></Cell>\t';
        }
        $line .= $value;
    }
    $data .= trim("<Row>".$line."</Row>")."\n";
}

$data = str_replace("\r","",$data);

header("Content-Type: application/vnd.ms-excel;");
header("Content-Disposition: attachment; filename=$filename.xls");
header("Pragma: no-cache");
header("Expires: 0");

$xls_header = '<?xml version="1.0" encoding="utf-8"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">
<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
<Author></Author>
<LastAuthor></LastAuthor>
<Company></Company>
</DocumentProperties>
<Styles>
<Style ss:ID="1">
<Alignment ss:Horizontal="Left"/>
</Style>
<Style ss:ID="2">
<Alignment ss:Horizontal="Left"/>
<Font ss:Bold="1"/>
</Style>

</Styles>
<Worksheet ss:Name="$filename">
<Table>';

$xls_footer = '</Table>
<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
<Selected/>
<FreezePanes/>
<FrozenNoSplit/>
<SplitHorizontal>1</SplitHorizontal>
<TopRowBottomPane>1</TopRowBottomPane>
</WorksheetOptions>
</Worksheet>
</Workbook>';

print $xls_header.$col_title.$data.$xls_footer;
exit;