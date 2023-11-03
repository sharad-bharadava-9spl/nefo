<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	$domain = $_SESSION['domain'];
	
	if (isset($_POST['numberOfMembers'])) {
		$numberOfMembers = $_POST['numberOfMembers'];
	} else {
		$numberOfMembers = 5;
	}		
		
	$deleteDonationScript = <<<EOD
	
	    $(document).ready(function() {
		    
			$('#cloneTable').width($('#mainTable').width());

		    
		  		
			
		});

var tablesToExcel = (function () {
    var uri = 'data:application/vnd.ms-excel;base64,'
    , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets>'
    , templateend = '</x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/></head>'
    , body = '<body>'
    , tablevar = '<table>{table'
    , tablevarend = '}</table>'
    , bodyend = '</body></html>'
    , worksheet = '<x:ExcelWorksheet><x:Name>'
    , worksheetend = '</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet>'
    , worksheetvar = '{worksheet'
    , worksheetvarend = '}'
    , base64 = function (s) { return window.btoa(unescape(encodeURIComponent(s))) }
    , format = function (s, c) { return s.replace(/{(\w+)}/g, function (m, p) { return c[p]; }) }
    , wstemplate = ''
    , tabletemplate = '';

    return function (table, name, filename) {
        var tables = table;

        for (var i = 0; i < tables.length; ++i) {
            wstemplate += worksheet + worksheetvar + i + worksheetvarend + worksheetend;
            tabletemplate += tablevar + i + tablevarend;
        }

        var allTemplate = template + wstemplate + templateend;
        var allWorksheet = body + tabletemplate + bodyend;
        var allOfIt = allTemplate + allWorksheet;

        var ctx = {};
        for (var j = 0; j < tables.length; ++j) {
            ctx['worksheet' + j] = name[j];
        }

        for (var k = 0; k < tables.length; ++k) {
            var exceltable;
            if (!tables[k].nodeType) exceltable = document.getElementById(tables[k]);
            ctx['table' + k] = exceltable.innerHTML;
        }

        //document.getElementById("dlink").href = uri + base64(format(template, ctx));
        //document.getElementById("dlink").download = filename;
        //document.getElementById("dlink").click();

        window.location.href = uri + base64(format(allOfIt, ctx));

    }
})();
EOD;

	pageStart($lang['bar'], NULL, $deleteDonationScript, "topspenders", "product admin", $lang['topspenderscaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$selectOpenPurchases = "SELECT id FROM categories WHERE id > 2";
		try
		{
			$results = $pdo3->prepare("$selectOpenPurchases");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$exp1 = "'t1', 't2', 't3', 't4', 't5', 't6', 't7', 't8', 't9', ";
		$tno = 10;
		while ($row = $results->fetch()) {
			
			$exp1 .= "'t$tno', ";
			$tno = $tno + 1;
			$exp1 .= "'t$tno', ";
			$tno = $tno + 1;
			$exp1 .= "'t$tno', ";
			$tno = $tno + 1;

		}

	$exp1 = substr($exp1, 0, -2);?>

<center>
<!-- <img src="images/excel-new.png" style="cursor: pointer;" onclick="tablesToExcel([<?php echo $exp1; ?>], [<?php echo $exp1; ?>], 'myfile.xls')" value="Export to Excel" /><br /> -->

<div id='filterbox'>
 <div class='boxcontent'>
  <center>
		&nbsp;<strong><?php echo $lang['number-members']; ?>:</strong><br /> 
        <form action='' method='POST'>
	     <input type='number' name='numberOfMembers' class='fourDigit defaultinput' value='<?php echo $numberOfMembers; ?>'>
	     <button class='cta1' style='margin: 0; border: 0; width: 50px; height: 30px;'>OK</button>
        </form>
  </center>
 </div>
</div>

<h3 class='title'><?php echo $lang['global-total']; ?></h3>

<br />
<div class='winnerbox'>
<?php 

$currMonth = date("F");

echo "<span class='winnerboxheader'>$currMonth</span>";

 
		// Look up this months sales
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(s.unitsTot) from b_salesdetails d JOIN b_sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(NOW()) AND YEAR(s.saletime) = YEAR(NOW()) GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
 
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$quantity = $topUser['SUM(s.unitsTot)'];
			$amount = round($amount,0);
			$quantity = round($quantity,0);
			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
				
			 	$winnerImg = "images/_$domain/members/$user_id.$photoExt";
			 	
			 	$object_exist = object_exist($google_bucket, $google_root_folder.$winnerImg);
			 
				if ($object_exist === false) {
					
					$winnerImg = "<img class='winnerimg' src='{$google_root}images/silhouette-new-big.png' />";
					
				} else {
					
			 		$winnerImg = "<img class='winnerimg' src='{$google_root}images/_$domain/members/$user_id.$photoExt' />";
			 	
				}

			echo <<<EOD
<center>
<div class='imageholder'>
 <img src='images/winner.png' class='trophyimg' />
 $winnerImg
</div>
</center>
<a href='profile.php?user_id=$user_id'><span class='firsttext'>#$memberno</span><span class='nametext2'>$first_name</span></a><br />
<div class='winnerstats'>$amount {$_SESSION['currencyoperator']} /  $quantity u</div>
		
EOD;
			} else {

			echo <<<EOD
<div class='notwinner'>
<a href='profile.php?user_id=$user_id'><span class='usergrouptext2'>$i</span> &nbsp;<span class='firsttext'>#$memberno</span><span class='nametext2'>$first_name</span></a>
<div class='notwinnerstats'>$amount {$_SESSION['currencyoperator']} /  $quantity u</div>
</div>
		
EOD;
			}
			
$i++;

}
		

?>
 </table>
</div>

<div class='winnerbox'>

<?php 

$currMonth = date("F", strtotime("first day of last month"));
echo "<span class='winnerboxheader'>$currMonth</span>";

		// Look up this months sales
		
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(s.unitsTot) from b_salesdetails d JOIN b_sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH)) GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$quantity = $topUser['SUM(s.unitsTot)'];
			$amount = round($amount,0);
			$quantity = round($quantity,0);

			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
				
			 	$winnerImg = "images/_$domain/members/$user_id.$photoExt";
			 	
			 	$object_exist = object_exist($google_bucket, $google_root_folder.$winnerImg);

				if ($object_exist === false) {
					
					$winnerImg = "<img class='winnerimg' src='{$google_root}images/silhouette-new-big.png' />";
					
				} else {
					
			 		$winnerImg = "<img class='winnerimg' src='{$google_root}images/_$domain/members/$user_id.$photoExt' />";
			 	
				}

			echo <<<EOD
<center>
<div class='imageholder'>
 <img src='images/winner.png' class='trophyimg' />
 $winnerImg
</div>
</center>
<a href='profile.php?user_id=$user_id'><span class='firsttext'>#$memberno</span><span class='nametext2'>$first_name</span></a><br />
<div class='winnerstats'>$amount {$_SESSION['currencyoperator']} /  $quantity u</div>
		
EOD;
			} else {

			echo <<<EOD
<div class='notwinner'>
<a href='profile.php?user_id=$user_id'><span class='usergrouptext2'>$i</span> &nbsp;<span class='firsttext'>#$memberno</span><span class='nametext2'>$first_name</span></a>
<div class='notwinnerstats'>$amount {$_SESSION['currencyoperator']} /  $quantity u</div>
</div>
		
EOD;
			}
			
$i++;

}
		

?>
 </table>
</div>

<div class='winnerbox'>

<?php 

$currMonth = date("F", strtotime("-1 months", strtotime("first day of last month") ));
echo "<span class='winnerboxheader'>$currMonth</span>";

		// Look up this months sales
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(s.unitsTot) from b_salesdetails d JOIN b_sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH)) GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$quantity = $topUser['SUM(s.unitsTot)'];
			$amount = round($amount,0);
			$quantity = round($quantity,0);

			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
				
			 	$winnerImg = "images/_$domain/members/$user_id.$photoExt";
			 	
			 	$object_exist = object_exist($google_bucket, $google_root_folder.$winnerImg);

				if ($object_exist === false) {
					
					$winnerImg = "<img class='winnerimg' src='{$google_root}images/silhouette-new-big.png' />";
					
				} else {
					
			 		$winnerImg = "<img class='winnerimg' src='{$google_root}images/_$domain/members/$user_id.$photoExt' />";
			 	
				}

			echo <<<EOD
<center>
<div class='imageholder'>
 <img src='images/winner.png' class='trophyimg' />
 $winnerImg
</div>
</center>
<a href='profile.php?user_id=$user_id'><span class='firsttext'>#$memberno</span><span class='nametext2'>$first_name</span></a><br />
<div class='winnerstats'>$amount {$_SESSION['currencyoperator']} /  $quantity u</div>
		
EOD;
			} else {

			echo <<<EOD
<div class='notwinner'>
<a href='profile.php?user_id=$user_id'><span class='usergrouptext2'>$i</span> &nbsp;<span class='firsttext'>#$memberno</span><span class='nametext2'>$first_name</span></a>
<div class='notwinnerstats'>$amount {$_SESSION['currencyoperator']} /  $quantity u</div>
</div>
		
EOD;
			}
			
$i++;

}
		

?>
 </table>
</div>
























<?php
		// Query to look up categories, then products in each category
		$selectCats = "SELECT id, name, description from b_categories ORDER by id ASC";
		try
		{
			$resultCats = $pdo3->prepare("$selectCats");
			$resultCats->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($category = $resultCats->fetch()) {
			
			$categoryname = $category['name'];
			$categoryid = $category['id'];
			
			echo "<br /><br /><h3 class='title'>$categoryname</h3><br />";
?>


<div class='winnerbox'>

<?php

$currMonth = date("F");

echo "<span class='winnerboxheader'>$currMonth</span>";

 
		// Look up this months sales
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(s.unitsTot), SUM(s.unitsTot) from b_salesdetails d JOIN b_sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(NOW()) AND YEAR(s.saletime) = YEAR(NOW()) AND d.category = $categoryid GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$amount = number_format($amount,0);
			$quantity = $topUser['SUM(s.unitsTot)'];
			$quantity = number_format($quantity,0);
			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
				
			 	$winnerImg = "images/_$domain/members/$user_id.$photoExt";
			 	
			    $object_exist = object_exist($google_bucket, $google_root_folder.$winnerImg);
				if ($object_exist === false) {
					
					$winnerImg = "<img class='winnerimg' src='{$google_root}images/silhouette-new-big.png' />";
					
				} else {
					
			 		$winnerImg = "<img class='winnerimg' src='{$google_root}images/_$domain/members/$user_id.$photoExt' />";
			 	
				}

			echo <<<EOD
<center>
<div class='imageholder'>
 <img src='images/winner.png' class='trophyimg' />
 $winnerImg
</div>
</center>
<a href='profile.php?user_id=$user_id'><span class='firsttext'>#$memberno</span><span class='nametext2'>$first_name</span></a><br />
<div class='winnerstats'>$amount {$_SESSION['currencyoperator']} /  $quantity u</div>
		
EOD;
			} else {

			echo <<<EOD
<div class='notwinner'>
<a href='profile.php?user_id=$user_id'><span class='usergrouptext2'>$i</span> &nbsp;<span class='firsttext'>#$memberno</span><span class='nametext2'>$first_name</span></a>
<div class='notwinnerstats'>$amount {$_SESSION['currencyoperator']} /  $quantity u</div>
</div>
		
EOD;
			}
			
$i++;

}
		

?>
 </table>
</div>

<div class='winnerbox'>

<?php 

$currMonth = date("F", strtotime("first day of last month"));
echo "<span class='winnerboxheader'>$currMonth</span>";

		// Look up this months sales
		
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(s.unitsTot), SUM(s.unitsTot) from b_salesdetails d JOIN b_sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND d.category = $categoryid GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$amount = number_format($amount,0);
			$quantity = $topUser['SUM(s.unitsTot)'];
			$quantity = number_format($quantity,0);

			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
				
			 	$winnerImg = "images/_$domain/members/$user_id.$photoExt";
			 	
			 	$object_exist = object_exist($google_bucket, $google_root_folder.$winnerImg);
				if ($object_exist === false) {
					
					$winnerImg = "<img class='winnerimg' src='{$google_root}images/silhouette-new-big.png' />";
					
				} else {
					
			 		$winnerImg = "<img class='winnerimg' src='{$google_root}images/_$domain/members/$user_id.$photoExt' />";
			 	
				}

			echo <<<EOD
<center>
<div class='imageholder'>
 <img src='images/winner.png' class='trophyimg' />
 $winnerImg
</div>
</center>
<a href='profile.php?user_id=$user_id'><span class='firsttext'>#$memberno</span><span class='nametext2'>$first_name</span></a><br />
<div class='winnerstats'>$amount {$_SESSION['currencyoperator']} /  $quantity u</div>
		
EOD;
			} else {

			echo <<<EOD
<div class='notwinner'>
<a href='profile.php?user_id=$user_id'><span class='usergrouptext2'>$i</span> &nbsp;<span class='firsttext'>#$memberno</span><span class='nametext2'>$first_name</span></a>
<div class='notwinnerstats'>$amount {$_SESSION['currencyoperator']} /  $quantity u</div>
</div>
		
EOD;
			}
			
$i++;

}
		

?>
 </table>
</div>

<div class='winnerbox'>

<?php 

$currMonth = date("F", strtotime("-1 months", strtotime("first day of last month") ));
echo "<span class='winnerboxheader'>$currMonth</span>";

		// Look up this months sales
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(s.unitsTot), SUM(s.unitsTot) from b_salesdetails d JOIN b_sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND d.category = $categoryid GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$amount = number_format($amount,0);
			$quantity = $topUser['SUM(s.unitsTot)'];
			$quantity = number_format($quantity,0);
			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
				
			 	$winnerImg = "images/_$domain/members/$user_id.$photoExt";
			 	
			 	$object_exist = object_exist($google_bucket, $google_root_folder.$winnerImg);
				if ($object_exist === false) {
					
					$winnerImg = "<img class='winnerimg' src='{$google_root}images/silhouette-new-big.png' />";
					
				} else {
					
			 		$winnerImg = "<img class='winnerimg' src='{$google_root}images/_$domain/members/$user_id.$photoExt' />";
			 	
				}

			echo <<<EOD
<center>
<div class='imageholder'>
 <img src='images/winner.png' class='trophyimg' />
 $winnerImg
</div>
</center>
<a href='profile.php?user_id=$user_id'><span class='firsttext'>#$memberno</span><span class='nametext2'>$first_name</span></a><br />
<div class='winnerstats'>$amount {$_SESSION['currencyoperator']} /  $quantity u</div>
		
EOD;
			} else {

			echo <<<EOD
<div class='notwinner'>
<a href='profile.php?user_id=$user_id'><span class='usergrouptext2'>$i</span> &nbsp;<span class='firsttext'>#$memberno</span><span class='nametext2'>$first_name</span></a>
<div class='notwinnerstats'>$amount {$_SESSION['currencyoperator']} /  $quantity u</div>
</div>
		
EOD;
			}
			
$i++;

}
		
?>
 </table>
</div>


<?php } ?>
