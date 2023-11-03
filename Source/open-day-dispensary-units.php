<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-closing.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	$closingtime = $_SESSION['closingtime'];
	$closingid = $_SESSION['closingid'];
	$responsible = $_SESSION['user_id'];
	
	// Check first! And ask if it's in process
	if ($_SESSION['noCompare'] != 'true') {
		
		$checkClosing = "SELECT closingid, dis2Opened, dis2OpenedBy, dayOpenedNo FROM closing ORDER by closingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$checkClosing");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$closingid = $row['closingid'];
			$dis2Opened = $row['dis2Opened'];
			$dis2OpenedBy = $row['dis2OpenedBy'];
			$dayOpenedNo = $row['dayOpenedNo'];
			
		if ($dis2Opened == '2' && (!isset($_GET['redo']))) {
			pageStart($lang['title-openday'], NULL, $validationScript, "pcloseday", "step2", $lang['open-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
			echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>
		{$lang['dispensary-opened']}
 </div>
</div>

EOD;
			exit();
	
		} else if ($dis2Opened == '1' && (!isset($_GET['redo']))) {
			
			// Look up user details
			$userDetails = "SELECT memberno, first_name FROM users WHERE user_id = '{$dis2OpenedBy}'";
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$memberno = $row['memberno'];
				$first_name = $row['first_name'];
		
			pageStart($lang['title-openday'], NULL, $validationScript, "pcloseday", "step2", $lang['open-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		echo  <<<EOD
	<div id="scriptMsg">
	 <div class='error'>
			{$lang['dispensary-open-inprogress-1']}$memberno $first_name{$lang['dispensary-open-inprogress-2']}
	 </div>
	</div>

EOD;
			exit();
	
		} else if (isset($_GET['redo'])) {
		
			// If exist (above 0), delete from openingdetails
			if ($dayOpenedNo > 0) {
				
				$query = "DELETE from openingdetails WHERE categoryType = 1 AND openingid = '$dayOpenedNo'";
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			}
			
			if ($_SESSION['firstOpening'] == 'true') {
				
				$checkClosing = "SELECT openingid FROM opening";
		try
		{
			$result = $pdo3->prepare("$checkClosing");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$openingid = $row['openingid'];

				$query = "DELETE from openingdetails WHERE categoryType = 1 AND openingid = '$openingid'";
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
					
			}
				
			$openingtime = date('Y-m-d H:i:s');
			$_SESSION['openingtime'] = $openingtime;
			
			$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
			$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));
	
		}
		
		// Write to DB Opening table: disOpening is in process
		$updateOpening = sprintf("UPDATE closing SET dis2Opened = '1', dis2OpenedBy = '%d' WHERE closingid = '%d';",
			$responsible,
			$closingid
		);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
	} else if ($_SESSION['firstOpening'] == 'true') {
		
		$selectUsers = "SELECT COUNT(openingid) FROM opening";
		$rowCount = $pdo3->query("$selectUsers")->fetchColumn();
		
		// First ever opening, and no Closing to compare to, so let's write to new variables in Opening table (if row doesn't exist, create it!)
		$checkClosing = "SELECT openingid, firstDis2Open, firstDis2OpenBy FROM opening";
		try
		{
			$result = $pdo3->prepare("$checkClosing");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if ($rowCount == 0) {
			
			// No opening exists, so let's create a new row.
			$insertOpening = "INSERT INTO opening (firstDis2Open, firstDis2OpenBy) VALUES (1, {$_SESSION['user_id']})";
		try
		{
			$result = $pdo3->prepare("$insertOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
		$openingid = $pdo3->lastInsertId();
				
		} else {
			
			// Someone already started the first ever opening:
			$row = $result->fetch();
				$openingid = $row['openingid'];
				$dis2Opened = $row['firstDis2Open'];
				$dis2OpenedBy = $row['firstDis2OpenBy'];
				
			if ($dis2Opened == '2' && (!isset($_GET['redo']))) {
				pageStart($lang['title-openday'], NULL, $validationScript, "pcloseday", "step2", $lang['open-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
				echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>
		{$lang['dispensary-opened']}
 </div>
</div>

EOD;
				exit();
	
			} else if ($dis2Opened == '1' && (!isset($_GET['redo']))) {
			
				// Look up user details
				$userDetails = "SELECT memberno, first_name FROM users WHERE user_id = '{$dis2OpenedBy}'";
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$memberno = $row['memberno'];
					$first_name = $row['first_name'];
			
				pageStart($lang['title-openday'], NULL, $validationScript, "pcloseday", "step2", $lang['open-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
				echo  <<<EOD
	<div id="scriptMsg">
	 <div class='error'>
			{$lang['dispensary-open-inprogress-1']}$memberno $first_name{$lang['dispensary-open-inprogress-2']}
	 </div>
	</div>

EOD;
				exit();
	
			} else if (isset($_GET['redo'])) {
			
				// If exist (above 0), delete from openingdetails
				$query = "DELETE from openingdetails WHERE categoryType = 1 AND openingid = '$openingid'";
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
				// Write to DB Opening table: disOpening is in process
				$updateOpening = sprintf("UPDATE opening SET firstDis2Open = '1', firstDis2OpenBy = '%d';",
					$responsible
				);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
					
				$openingtime = date('Y-m-d H:i:s');
				$_SESSION['openingtime'] = $openingtime;
				
				$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
		
			}
		}
			
		// Write to DB Opening table: disOpening is in process
		$updateOpening = sprintf("UPDATE opening SET firstDis2Open = '1', firstDis2OpenBy = '%d';",
			$responsible
		);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
					
	}
/*
document.querySelector('button').addEventListener("click", function(){
    window.btn_clicked = true;      //set btn_clicked to true
});
$(window).bind('beforeunload', function(){
    if(!window.btn_clicked){
        return 'Are you sure you want to leave this page?';
    }
});
*/
	$confirmLeave = <<<EOD
    $(document).ready(function() {
	    
var userSubmitted=false;

$('#registerForm').submit(function() {
userSubmitted = true;
});

$('#skipCount').click(function() {
userSubmitted = true;
});

window.onbeforeunload = function() {
    if(!userSubmitted)
        return 'Are you sure that you want to leave this page?';
};
  }); // end ready
EOD;

	pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "step1", $lang['openday-dis2-one'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	if ($_SESSION['firstOpening'] != 'true') {
		echo "<a href='uTil/auto-open-day-dispensary-units.php?cid=$closingid' class='cta' style='width: 400px;' id='skipCount'>{$lang['dont-count']}</a>";
	} else {
		echo "<a href='uTil/auto-open-day-dispensary-units-first.php' class='cta' style='width: 400px;' id='skipCount'>{$lang['use-purchase-values']}</a>";
	}

?>

	<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="open-day-dispensary-units-1.php" method="POST">

<?php

	// First look up categories. For each cat, type 1, run header, then look up products.
	$selectCats = "SELECT DISTINCT category FROM purchases WHERE category > 2 AND closedAt IS NULL";
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
	
		while ($cat = $resultCats->fetch()) {
		
		$category = $cat['category'];
		
		// Look up category, and then only run the below if type = 1
		$selectCat = "SELECT name, type FROM categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$selectCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$catName = $row['name'];
			$catType = $row['type'];
			
		if ($catType == 0) {
			
			$catArray[] = $category;
			
			echo "<h3 class='title'>$catName</h3><div class='productboxwrap'>";
		
			$selectProducts = "SELECT g.productid, g.name, p.purchaseid FROM products g, purchases p WHERE p.category = $category AND p.productid = g.productid AND p.closedAt IS NULL ORDER BY g.name;";
		try
		{
			$resultProducts = $pdo3->prepare("$selectProducts");
			$resultProducts->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($product = $resultProducts->fetch()) {
	
				$i++;
			
				$flower_row = sprintf("
					<div class='productbox'>
				<h3>%s ({$product['purchaseid']})</h3>
				<input type='number' lang='nb' name='dayopenProduct[%d][weight]' class='fourDigit' placeholder='u' step='0.01' required /><br />
			  	   <input type='hidden' name='dayopenProduct[%d][name]' value='%s' />
			  	   <input type='hidden' name='dayopenProduct[%d][category]' value='%d' />
			  	   <input type='hidden' name='dayopenProduct[%d][categoryName]' value='%s' />
			  	   <input type='hidden' name='dayopenProduct[%d][productid]' value='%d' />
			  	   <input type='hidden' name='dayopenProduct[%d][purchaseid]' value='%d' /></div>",
				  $product['name'], $i, $i, $product['name'], $i, $category, $i, $catName, $i, $product['productid'], $i, $product['purchaseid']
				  );

		  		echo $flower_row;

	  		}

			echo "</div>";

		}

	}
	
	foreach($catArray as $value) {
		echo '<input type="hidden" name="catArray[]" value="'. $value. '">';
	}
		  
?>
  

 <input type='hidden' name='closingConfirm' value='yes' />
 <button name='oneClick' type="submit" class="customButton"><?php echo $lang['global-nextstep']; ?> &raquo;</button>
</form>



<?php displayFooter(); ?>
