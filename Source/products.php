<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Query to look up flowers
	$selectFlowers = "SELECT flowerid, flowertype, registeredSince, name, description, medicaldescription, breed2, sativaPercentage, THC, CBD, CBN, flowernumber FROM flower ORDER by name ASC";
		try
		{
			$resultFlower = $pdo3->prepare("$selectFlowers");
			$resultFlower->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	// Query to look up extract
	$selectExtract = "SELECT extractid, extracttype, registeredSince, name, description, medicaldescription, extract, THC, CBD, CBN, extractnumber FROM extract ORDER by name ASC";
		try
		{
			$resultExtract = $pdo3->prepare("$selectExtract");
			$resultExtract->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
	$deleteFlowerScript = <<<EOD
	
	    $(document).ready(function() {
			$('.mainTable').tablesorter();
		});
			
function delete_flower(flowerid) {
	if (confirm("{$lang['extracts-deleteflower']}")) {
				window.location = "uTil/delete-flower.php?flowerid=" + flowerid;
				}
}
function delete_extract(extractid) {
	if (confirm("{$lang['extracts-deleteextract']}")) {
				window.location = "uTil/delete-extract.php?extractid=" + extractid;
				}
}
function delete_product(productid, category) {
	if (confirm("{$lang['confirm-deleteproduct']}")) {
				window.location = "uTil/delete-product.php?productid=" + productid + "&category=" + category;
				}
}
EOD;
	pageStart($lang['global-products'], NULL, $deleteFlowerScript, "pproducts", "admin", $lang['global-productscaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>

<center><a href="new-product.php" class="cta1"><?php echo $lang['admin-newstrain']; ?></a></center>

<?php

		echo "<h3 class='title' onClick='load1()' style='cursor: pointer;'><img src='images/icon-flower.png' height='30' style='margin-right: 10px; margin-bottom: -8px;' /> {$lang['global-flowerscaps']} <img src='images/expand.png' id='plus1' width='15' style='margin-left: 5px;' /><span id='spinner1'></span><input type='hidden' name='click1' id='click1' class='clickControl' value='0' /></h3><span id='menu1' style='display: none;'>";
		
		echo <<<EOD
<script>
function load1(){
	
  var x = document.getElementById("menu1");
  if (x.style.display === "none") {
	  
    x.style.display = "block";
	$("#plus1").attr("src","images/shrink.png");

  } else {
	  
    x.style.display = "none";
	$("#plus1").attr("src","images/expand.png");
	
  }

};
</script>
EOD;
?>

	 <table class="default mainTable">
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th>#</th>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th>Sativa %</th>
	    <th>THC</th>
	    <th>CBD</th>
	    <th>CBN</th>
	    <th colspan='2'><?php echo $lang['extracts-description']; ?></th>
	    <th><?php echo $lang['global-actions']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
while ($flower = $resultFlower->fetch()) {

	
	$breed2 = $flower['breed2'];
	$name = $flower['name'];
	$flowerid = $flower['flowerid'];
	$description = $flower['description'];
	$medicaldescription = $flower['medicaldescription'];
	$flowernumber = $flower['flowernumber'];
	$flowerid = $flower['flowerid'];
	$flowertype = $flower['flowertype'];
	$sativaPercentage = $flower['sativaPercentage'];
	$THC = $flower['THC'];
	$CBD = $flower['CBD'];
	$CBN = $flower['CBN'];
	
	
	if ($breed2) {
		$name = $name . " x " . $breed2;
	} else {
		$name = $name;
	}		
	
	
	if ($description != '') {
		
		$commentRead = "
		                <img src='images/description.png' id='comment$flowerid' /><div id='helpBox$flowerid' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$description}</div>
		                <script>
		                  	$('#comment$flowerid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$flowerid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$flowerid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}		
	if ($medicaldescription != '') {
		
		$commentReadM = "
		                <img src='images/medical.png' width='15' id='commentM$flowerid' /><div id='helpBoxM$flowerid' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$medicaldescription}</div>
		                <script>
		                  	$('#commentM$flowerid').on({
						 		'mouseover' : function() {
								 	$('#helpBoxM$flowerid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBoxM$flowerid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentReadM = "";
		
	}		

	if ($flowernumber != 0) {
		$productnumber = str_pad($flowernumber, 3, '0', STR_PAD_LEFT);
	} else {
		$productnumber = '';
	}
		
	$flower_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='edit-flower.php?flowerid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-flower.php?flowerid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-flower.php?flowerid=%d'>%s</td>
  	   <td class='clickableRow right' href='edit-flower.php?flowerid=%d'>%d<span class='smallerfont'> %%</span></td>
  	   <td class='clickableRow right' href='edit-flower.php?flowerid=%d'>%0.0f<span class='smallerfont'> %%</span></td>
  	   <td class='clickableRow right' href='edit-flower.php?flowerid=%d'>%0.1f<span class='smallerfont'> %%</span></td>
  	   <td class='clickableRow right' href='edit-flower.php?flowerid=%d'>%0.1f<span class='smallerfont'> %%</span></td>
  	   <td class='relative right' style='padding: 11px 6px 11px 0;'><span class='relativeitem'>$commentRead</span></td>
  	   <td class='relative left' style='padding: 11px 0 11px 6px;'><span class='relativeitem'>$commentReadM</span></td>
  	   <td style='text-align: center;'><a href='edit-flower.php?flowerid=%d'><img src='images/edit.png' height='15' title='{$lang['extracts-editflower']}' /></a>&nbsp;&nbsp;<a href='javascript:delete_flower(%d)'><img src='images/delete.png' height='15' title='{$lang['extracts-deleteflower']}' /></a></td>
	  </tr>",
	  $flowerid, $name, $flowerid, $productnumber , $flowerid, $flowertype, $flowerid, $sativaPercentage, $flowerid, $THC, $flowerid, $CBD, $flowerid, $CBN, $flowerid, $flowerid, $flowerid
	  );
	  echo $flower_row;
  }
?>


	 </tbody>
	 </table>
	 
	 <br /><br />
	 </span>
<?php

		echo "<h3 class='title' onClick='load2()' style='cursor: pointer;'><img src='images/icon-extract.png' height='30' style='margin-right: 10px; margin-bottom: -8px;' /> {$lang['global-extractscaps']} <img src='images/expand.png' id='plus2' width='15' style='margin-left: 5px;' /><span id='spinner2'></span><input type='hidden' name='click2' id='click2' class='clickControl' value='0' /></h3><span id='menu2' style='display: none;'>";
		
		echo <<<EOD
<script>
function load2(){
	
  var x = document.getElementById("menu2");
  if (x.style.display === "none") {
	  
    x.style.display = "block";
	$("#plus2").attr("src","images/shrink.png");

  } else {
	  
    x.style.display = "none";
	$("#plus2").attr("src","images/expand.png");
	
  }

};
</script>
EOD;
?>
	 <table class="default mainTable">
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th>#</th>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['global-extract']; ?></th>
	    <th>THC</th>
	    <th>CBD</th>
	    <th>CBN</th>
	    <th colspan='2'><?php echo $lang['extracts-description']; ?></th>
	    <th><?php echo $lang['global-actions']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

	while ($extract = $resultExtract->fetch()) {
	
	$extractid = $extract['extractid'];
	
	if ($extract['description'] != '') {
		
		$commentRead = "
		                <img src='images/description.png' id='comment$extractid' /><div id='helpBox$extractid' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$extract['description']}</div>
		                <script>
		                  	$('#comment$extractid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$extractid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$extractid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}		

	if ($extract['medicaldescription'] != '') {
		
		$commentReadM = "
		                <img src='images/medical.png' width='15' id='commentM$extractid' /><div id='helpBoxM$extractid' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$extract['medicaldescription']}</div>
		                <script>
		                  	$('#commentM$extractid').on({
						 		'mouseover' : function() {
								 	$('#helpBoxM$extractid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBoxM$extractid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentReadM = "";
		
	}		
	
	if ($extract['extractnumber'] != 0) {
		$productnumber = str_pad($extract['extractnumber'], 3, '0', STR_PAD_LEFT);
	} else {
		$productnumber = '';
	}
	
	$extract_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='edit-extract.php?extractid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-extract.php?extractid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-extract.php?extractid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-extract.php?extractid=%d'>%s</td>
  	   <td class='clickableRow right' href='edit-extract.php?extractid=%d'>%0.0f<span class='smallerfont'> %%</span></td>
  	   <td class='clickableRow right' href='edit-extract.php?extractid=%d'>%0.1f<span class='smallerfont'> %%</span></td>
  	   <td class='clickableRow right' href='edit-extract.php?extractid=%d'>%0.1f<span class='smallerfont'> %%</span></td>
  	   <td class='relative right' style='padding: 11px 6px 11px 0;'>$commentRead</td>
  	   <td class='relative left' style='padding: 11px 0 11px 6px;'>$commentReadM</td>
  	   <td style='text-align: center;'><a href='edit-extract.php?extractid=%d'><img src='images/edit.png' height='15' title='{$lang['extracts-editextract']}' /></a>&nbsp;&nbsp;<a href='javascript:delete_extract(%d)'><img src='images/delete.png' height='15' title='{$lang['extracts-deleteextract']}' /></a></td>
	  </tr>",
	  $extract['extractid'], $extract['name'], $extract['extractid'], $productnumber, $extract['extractid'], $extract['extracttype'], $extract['extractid'], $extract['extract'], $extract['extractid'], $extract['THC'], $extract['extractid'], $extract['CBD'], $extract['extractid'], $extract['CBN'], $extract['extractid'], $extract['extractid'], $extract['extractid']
	  );
	  echo $extract_row;
  }
?>


	 </tbody>
	 </table><br /><br />
	 </span>
	 
<?php

	// Query to look up categories
	$selectCats = "SELECT id, name, description, type from categories ORDER by id ASC";
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
	
		while ($category = $resultCats->fetch()) {	
		
		$categoryid = $category['id'];
		$name = $category['name'];
		$type = $category['type'];
		
	if ($type == 0) {
		$type = "u";
	} else {
		$type = "g";
	}
	

	// For each cat, look up products
	$selectProducts = "SELECT productid, name, description, medicaldescription, productnumber from products WHERE category = $categoryid ORDER BY name ASC";
		try
		{
			$result = $pdo3->prepare("$selectProducts");
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


		echo "<h3 class='title' onClick='load$categoryid()' style='cursor: pointer;'>$name ($type) <img src='images/expand.png' id='plus$categoryid' width='15' style='margin-left: 5px;' /><span id='spinner$categoryid'></span><input type='hidden' name='click$categoryid' id='click$categoryid' class='clickControl' value='0' /></h3><span id='menu$categoryid' style='display: none;'>";
		
		echo <<<EOD
<script>
function load$categoryid(){
	
  var x = document.getElementById("menu$categoryid");
  if (x.style.display === "none") {
	  
    x.style.display = "block";
	$("#plus$categoryid").attr("src","images/shrink.png");

  } else {
	  
    x.style.display = "none";
	$("#plus$categoryid").attr("src","images/expand.png");
	
  }

};
</script>
EOD;

echo "
	 <table class='default mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th>{$lang['global-name']}</th>
	    <th>#</th>
	    <th colspan='2'>{$lang['extracts-description']}</th>
	    <th>{$lang['global-actions']}</th>
	   </tr>
	  </thead>
	  <tbody>";
	  
	foreach ($data as $product) {
		$productid = $product['productid'];
		$productName = $product['name'];
		
	
	if ($product['description'] != '') {
		
		$commentRead = "
		                <img src='images/description.png' id='comment$productid' /><div id='helpBox$productid' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$product['description']}</div>
		                <script>
		                  	$('#comment$productid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$productid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$productid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}
		
	if ($product['medicaldescription'] != '') {
		
		$commentReadM = "
		                <img src='images/medical.png' width='15' id='commentM$productid' /><div id='helpBoxM$productid' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$product['medicaldescription']}</div>
		                <script>
		                  	$('#commentM$productid').on({
						 		'mouseover' : function() {
								 	$('#helpBoxM$productid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBoxM$productid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentReadM = "";
		
	}
	
	if ($product['productnumber'] != 0) {
		$productnumber = str_pad($product['productnumber'], 3, '0', STR_PAD_LEFT);
	} else {
		$productnumber = '';
	}
	
	$product_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='edit-product.php?productid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-product.php?productid=%d'>%s</td>
  	   <td class='relative right clickableRow' style='padding: 11px 6px 11px 0;' href='edit-product.php?productid=%d'>$commentRead</td>
  	   <td class='relative left clickableRow' style='padding: 11px 0 11px 6px;' href='edit-product.php?productid=%d'>$commentReadM</td>
  	   <td style='text-align: center;'><a href='edit-product.php?productid=%d'><img src='images/edit.png' height='15' title='Editar' /></a>&nbsp;&nbsp;<a href='javascript:delete_product(%d, %d)'><img src='images/delete.png' height='15' title='Delete' /></a></td>
	  </tr>",
	  $productid, $productName, $productid, $productnumber, $productid, $productid, $productid, $productid, $categoryid
	  );
	  echo $product_row;
  }
  echo "</tbody></table><br /><br /></span>";
?>

<?php
	}

 }

displayFooter(); ?>