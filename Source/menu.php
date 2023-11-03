<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	$domain = $_SESSION['domain'];
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	
	// Did the member scan his ID card?
	// Get the card ID
	if ($_POST['cardid'] != '') {
		
		$cardid = $_POST['cardid'];
		
		if ($cardid == '') {
			
				$_SESSION['errorMessage'] = $lang['scan-error'];
			
		} else {
		
			// Query to look up user
			$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid = '$cardid'")->fetchColumn();
			
			if ($rowCount == 0) {
				// Query to look up user
				$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid2 = '{$cardid}'")->fetchColumn();
				
				if ($rowCount == 0) {
					// Query to look up user
					$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid3 = '{$cardid}'")->fetchColumn();
					
					if ($rowCount == 0) {
				   		handleError($lang['error-keyfob'],"");
					} else {
						$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid3 = '{$cardid}'");
					}
					
				} else {
					$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid2 = '{$cardid}'");
				}
	
				
			} else {
				$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid = '{$cardid}'");
			}
			
					
			$result->execute();
			
			$row = $result->fetch();
				$user_id = $row['user_id'];
				
			// Check if chip is registered more than once
			if ($rowCount > 1) {
				
				$_SESSION['errorMessage'] = $lang['chip-registered-more-than-once'];
				header("Location: duplicate-chip.php?cardid=$cardid");
				exit();
			
			}
		}
				
		header("Location: new-dispense-2.php?user_id={$user_id}");
		exit();
	}
	
	$disableArrowKeys = <<<EOD
var ar=new Array(33,34,35,36,37,38,39,40);

$(document).keydown(function(e) {
     var key = e.which;
      //console.log(key);
      //if(key==35 || key == 36 || key == 37 || key == 39)
      if($.inArray(key,ar) > -1) {
          e.preventDefault();
          return false;
      }
      return true;
});
EOD;

	pageStart("CCS | Menu", NULL, $disableArrowKeys, "pdispense", "menu", "MENU", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>
<div class="clearfloat"></div>
<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="" autocomplete="off" method="POST">
 <input type="text" name="cardid" id="focus" maxlength="30" autofocus value="" /><br />
<button name='oneClick' type="submit" style='display: none;'><?php echo $lang['form-accept']; ?></button>
</form>
<center>


<?php

	// Query to look up categories
	$selectCats = "SELECT id, name, description, type from categories ORDER by sortorder ASC, id ASC";
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

	while ($sort = $resultCats->fetch()) {
		
		$categoryid = $sort['id'];
		$name = $sort['name'];
		$type = $sort['type'];
		
		if ($categoryid == 1) {

		
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "g.name ASC";
	}

	$selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY $sorting";
		try
		{
			$result = $pdo3->prepare("$selectFlower");
			$result->execute();
			$dataFlower = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		if ($dataFlower) {
		
		$i = 0;
		echo "<h3 class='title'><img src='images/icon-flower.png' height='30' style='margin-right: 10px; margin-bottom: -8px;' />{$lang['global-flowerscaps']}</h3>";
foreach ($dataFlower as $flower) {

		if ($flower['breed2'] != '') {
			$name = $flower['name'] . " x " . $flower['breed2'];
		} else {
			$name = $flower['name'];
		}
		
		if ($flower['flowertype'] == 'Hybrid' && $flower['sativaPercentage'] > 0 && $flower['sativaPercentage'] != NULL) {
			$percentageDisplay = '<br />(' . number_format($flower['sativaPercentage'],0) . '% s.)';
		} else {
			$percentageDisplay = '';
		}
		
		
	// Look up growtype
		$growtype = $flower['growType'];
	$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = $growtype";
		try
		{
			$result = $pdo3->prepare("$growDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$growtype = $row['growtype'];
		
		if ($growtype == '') {
			$growtype = "<span class='prodspecsempty'>&nbsp;</span>";
		} else {
			$growtype = "<span class='prodspecs'>$growtype</span>";
		}
		
		if ($flower['flowertype'] == '') {
			$flowertype = "<span class='prodspecsempty'>&nbsp;</span>";
		} else {
			$flowertype = "<span class='prodspecs'>{$flower['flowertype']}</span>";
		}
		
	$i++;

	$flower_row =	sprintf("
	<div class='displaybox'>
	 <div class='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 
	 <table style='width: 210px;'>
	  <tr>
	   <td style='width: 80px; padding-left: 5px; vertical-align: top; text-align: left;'>
	    %s<br />
	    %s
	   </td>
	   <td style='width: 125px; text-align: right; vertical-align: top;'>
	    <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span><br />
	    <span class='discountprice'>$normalPrice</span>
	   </td>
	  </tr>
	 </table>

	</div>",
	  $flower['purchaseid'] . "." . $flower['photoExt'], $name, $growtype, $flowertype, $percentageDisplay, $i, $i, $flower['salesPrice']
	  );
	  echo $flower_row;
  }
  
	}		
	
	} else if ($categoryid == 2) {
		
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "h.name ASC";
	}

  	$selectExtract = "SELECT h.extractid, h.name, h.extract, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY $sorting";
		try
		{
			$result = $pdo3->prepare("$selectExtract");
			$result->execute();
			$dataExtract = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		

  
		if ($dataExtract) {
			
		echo "<h3 class='title'><img src='images/icon-extract.png' height='30' style='margin-right: 10px; margin-bottom: -8px;' />{$lang['global-extractscaps']}</h3>";
foreach ($dataExtract as $extract) {
	$i++;

		if ($extract['extract'] == '') {
			$extracttype = "<span class='prodspecsempty'>&nbsp;</span>";
		} else {
			$extracttype = "<span class='prodspecs'>{$extract['extract']}</span>";
		}
		
	$extract_row =	sprintf("
	<div class='displaybox'>
	 <div class='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 
	 <table style='width: 210px;'>
	  <tr>
	   <td style='width: 80px; padding-left: 5px; vertical-align: top; text-align: left;'>
	    %s
	   </td>
	   <td style='width: 125px; text-align: right; vertical-align: top;'>
	    <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span><br />
	    <span class='discountprice'>$normalPrice</span>
	   </td>
	  </tr>
	 </table>

	</div>",
	  $extract['purchaseid'] . "." . $extract['photoExt'], $extract['name'], $extracttype, $i, $i, $extract['salesPrice']
	  );
	  echo $extract_row;
  }		
}

} else {
  
  
	// Query to look up categories
	$selectCats = "SELECT id, name, type from categories where id = $categoryid";
		try
		{
			$resultscat = $pdo3->prepare("$selectCats");
			$resultscat->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($category = $resultscat->fetch()) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
  
 		
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "pr.name ASC";
	}

	 // For each cat, look up products
  	$selectProduct = "SELECT pr.productid, pr.name, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY $sorting;";
		try
		{
			$result = $pdo3->prepare("$selectProduct");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		if ($data) {
			
 		echo "<h3 class='title'>$name</h3>";

	
		foreach ($data as $product) {
	
	 		$i++;
			$productid = $product['productid'];
			$productName = $product['name'];
			
	 		
	
			$product_row =	sprintf("
	<div class='displaybox'>
	 <div class='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 
	 <table style='width: 210px;'>
	  <tr>
	   <td style='width: 80px; padding-left: 5px; vertical-align: top; text-align: left;'>
	   </td>
	   <td style='width: 125px; text-align: right; vertical-align: top;'>
	    <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span><br />
	    <span class='discountprice'>$normalPrice</span>
	   </td>
	  </tr>
	 </table>

	</div>",
			  $product['purchaseid'] . "." . $product['photoExt'], $product['name'], $i, $i, $product['salesPrice']
			  );
			  echo $product_row;
		  }
		
 		
	}
	
}
}
}
 
  
  
echo "</center>";

?>
<script>
$(document).ready(function() {
    $("#focus").focus().bind('blur', function() {
        $(this).focus();
    }); 

    $("html").click(function() {
        $("#focus").val($("#focus").val()).focus();
    });

    //disable the tab key
    $(document).keydown(function(objEvent) {
        if (objEvent.keyCode == 9) {  //tab pressed
            objEvent.preventDefault(); // stops its action
       }
    })      
});
</script>

	  <div class="clearFloat"></div>

	 </tbody>
	 </table>
	
	
</center>
<?php displayFooter(); ?>

