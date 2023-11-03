<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	
	// Did the member scan his ID card?
	// Get the card ID
	if (isset($_POST['cardid'])) {
		$cardid = $_POST['cardid'];
		
	// Query to look up user
	$userDetails = "SELECT user_id FROM users WHERE cardid = '{$cardid}'";
	
	// Does user ID exist?
	$userCheck = mysql_query($userDetails);
	if(mysql_num_rows($userCheck) == 0) {
   		handleError($lang['error-keyfob'],"");
	}
	
	$result = mysql_query($userDetails)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
	if ($result) {
	$row = mysql_fetch_array($result);
	$user_id = $row['user_id'];
}
		// On success: redirect.
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
 <input type="text" name="cardid" id="focus" maxlength="10" autofocus value="" /><br />
<button name='oneClick' type="submit">Accept</button>
</form>


<?php
		
	$selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
	$resultFlower = mysql_query($selectFlower)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());

  	$selectExtract = "SELECT h.extractid, h.name, h.extract, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
	$resultExtract = mysql_query($selectExtract)
		or handleError($lang['error-prodprices'],"Error loading extract prices from db: " . mysql_error());
		
		
		$i = 0;
		echo "<h3 class='title'>FLOWERS</h3>";
while ($flower = mysql_fetch_array($resultFlower)) {

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
	
	$result = mysql_query($growDetails)
		or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$growtype = $row['growtype'];
		

		
	$i++;

	$flower_row =	sprintf("
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='images/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice'>
	  <span class='firstline'>%s<br /><span class='yellow' style='font-size: 16px; font-weight: 600; margin-top: 3px; display: inline-block;'>%s</span></span><span class='yellow'>%s</span>
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span>
	 <div class='clearfloat'></div><br />

	</div>",
	  $flower['purchaseid'] . "." . $flower['photoExt'], $name, $growtype, $flower['flowertype'], $percentageDisplay, $i, $i, $flower['salesPrice']
	  );
	  echo $flower_row;
  }
  
		echo "<h3 class='title'>EXTRACTS</h3>";
while ($extract = mysql_fetch_array($resultExtract)) {
	$i++;

	$extract_row =	sprintf("
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='images/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice'>
	  <span class='firstline'>%s</span>
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span>
	 <div class='clearfloat'></div><br />
	</div>",
	  $extract['purchaseid'] . "." . $extract['photoExt'], $extract['name'], $extract['extract'], $i, $i, $extract['salesPrice']
	  );
	  echo $extract_row;
  }
  
  
	// Query to look up categories
	$selectCats = "SELECT id, name from categories ORDER by id ASC";

	$resultCats = mysql_query($selectCats)
		or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());

	while ($category = mysql_fetch_array($resultCats)) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
  
 		echo "<h3 class='title'>$name</h3>";
 		
	 // For each cat, look up products
  	$selectProduct = "SELECT pr.productid, pr.name, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
  	
	$resultProduct = mysql_query($selectProduct)
		or handleError($lang['error-prodprices'],"Error loading extract prices from db: " . mysql_error());
		
		

		while ($product = mysql_fetch_array($resultProduct)) {
	
	 		$i++;
			$productid = $product['productid'];
			$productName = $product['name'];
			
	 		
	
			$product_row =	sprintf("
			<div class='displaybox'>
			 <center><img src='images/purchases/%s' /></center>
			 <h3>%s</h3>
			 <center><input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span></center>
			 <div class='clearfloat'></div><br />
			</div>",
			  $product['purchaseid'] . "." . $product['photoExt'], $product['name'], $i, $i, $product['salesPrice']
			  );
			  echo $product_row;
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
	
	

<?php displayFooter(); ?>

