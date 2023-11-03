<?php

	

	require_once 'cOnfig/connection.php';

	require_once 'cOnfig/view.php';

	require_once 'cOnfig/authenticate.php';

	require_once 'cOnfig/languages/common.php';

	

	session_start();

	$accessLevel = '3';

	

	// Authenticate & authorize

	authorizeUser($accessLevel);

	

	// Did this page re-submit with a form? If so, check & store details

	if (isset($_POST['salesPrice'])) {



	$category = $_POST['category'];

	$productID = $_POST['productID'];

	$purchaseQuantity = $_POST['purchaseQuantity'];

	$purchasePrice = $_POST['purchaseppg'];

	$salesPrice = $_POST['salesppg'];

	$adminComment = $_POST['adminComment'];

	$closingComment = $_POST['closingComment'];

	$purchaseid = $_POST['purchaseid'];

	$inMenu = $_POST['inMenu'];

	

	$sample = $_POST['sample'];

	$displayjar = $_POST['displayjar'];

	$intstash = $_POST['intstash'];

	$extstash = $_POST['extstash'];

	$sampleID = $_POST['sampleID'];

	$displayjarID = $_POST['displayjarID'];

	$intstashID = $_POST['intstashID'];

	$extstashID = $_POST['extstashID'];

	$growtype = $_POST['growtype'];

	$purchaseDate = $_POST['purchaseDate'];

	$provider = $_POST['provider'];

	$barCode = $_POST['barCode'];

	

	$tupperWeight = $_POST['tupperWeight'];

	                // KONSTANT CODE UPDATE BEGIN

        	$volumeDiscounts = "DELETE FROM b_volume_discounts WHERE purchaseid = $purchaseid";

		try

		{

			$result = $pdo3->prepare("$volumeDiscounts")->execute();

		}

		catch (PDOException $e)

		{

                    $error = 'Error fetching user: ' . $e->getMessage();

                    echo $error;

                    exit();

		}

        



                for($i = 0; $i < count($_POST['volume_unit']); $i++) {

                    if(!empty($_POST['volume_unit'][$i])){

                        $volume_unit = $_POST['volume_unit'][$i];

                        $volume_unit_price = $_POST['volume_unit_price'][$i];

                        $addDiscountB = sprintf("INSERT INTO b_volume_discounts (purchaseid, units, amount) VALUES ('%d', '%d', '%d');",

                          $purchaseid, $volume_unit, $volume_unit_price);

                        try

                        {

                            $result = $pdo3->prepare("$addDiscountB")->execute();

                        }

                        catch (PDOException $e)

                        {

                            $error = 'Error fetching user: ' . $e->getMessage();

                            echo $error;

                            exit();

                        }

                    }

                }    

                // KONSTANT CODE UPDATE END

		// Update/add/remove initial product movements

		

		// sample taste

		if (($sample == 0 || $sample == '') && $sampleID != '') {

			

			$deleteMovement = "DELETE FROM b_productmovements WHERE movementid = $sampleID";

	

		try

		{

			$result = $pdo3->prepare("$deleteMovement")->execute();

		}

		catch (PDOException $e)

		{

				$error = 'Error fetching user: ' . $e->getMessage();

				echo $error;

				exit();

		}

			

		} else if ($sample > 0) {

			

			if ($sampleID == '') {

				

			  $updateMovement = sprintf("INSERT INTO b_productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",

			  $purchaseDate, '2', $purchaseid, $sample, '8', '1');

		  

			} else {

				

		$updateMovement = sprintf("UPDATE b_productmovements SET quantity = '%f' WHERE movementid = $sampleID;",

			$sample

);

			}

		try

		{

			$result = $pdo3->prepare("$updateMovement")->execute();

		}

		catch (PDOException $e)

		{

				$error = 'Error fetching user: ' . $e->getMessage();

				echo $error;

				exit();

		}

		}



		// display jar

		if (($displayjar == 0 || $displayjar == '') && $displayjarID != '') {

			

			$deleteMovement = "DELETE FROM b_productmovements WHERE movementid = $displayjarID";

		try

		{

			$result = $pdo3->prepare("$deleteMovement")->execute();

		}

		catch (PDOException $e)

		{

				$error = 'Error fetching user: ' . $e->getMessage();

				echo $error;

				exit();

		}

			

		} else if ($displayjar > 0) {

			

			if ($displayjarID == '') {

				

			  $updateMovement = sprintf("INSERT INTO b_productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",

			  $purchaseDate, '2', $purchaseid, $displayjar, '9', '1');

		  

			} else {

				

		$updateMovement = sprintf("UPDATE b_productmovements SET quantity = '%f' WHERE movementid = $displayjarID;",

			$displayjar

);

			}

		try

		{

			$result = $pdo3->prepare("$updateMovement")->execute();

		}

		catch (PDOException $e)

		{

				$error = 'Error fetching user: ' . $e->getMessage();

				echo $error;

				exit();

		}

		}



		// internal stash

		if (($intstash == 0 || $intstash == '') && $intstashID != '') {

			

			$deleteMovement = "DELETE FROM b_productmovements WHERE movementid = $intstashID";

		try

		{

			$result = $pdo3->prepare("$deleteMovement")->execute();

		}

		catch (PDOException $e)

		{

				$error = 'Error fetching user: ' . $e->getMessage();

				echo $error;

				exit();

		}

			

		} else if ($intstash > 0) {

			

			if ($intstashID == '') {

				

			  $updateMovement = sprintf("INSERT INTO b_productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",

			  $purchaseDate, '2', $purchaseid, $intstash, '5', '1');

		  

			} else {

				

		$updateMovement = sprintf("UPDATE b_productmovements SET quantity = '%f' WHERE movementid = $intstashID;",

			$intstash

);

			}

		try

		{

			$result = $pdo3->prepare("$updateMovement")->execute();

		}

		catch (PDOException $e)

		{

				$error = 'Error fetching user: ' . $e->getMessage();

				echo $error;

				exit();

		}

		}



		// external stash

		if (($extstash == 0 || $extstash == '') && $extstashID != '') {

			

			$deleteMovement = "DELETE FROM b_productmovements WHERE movementid = $extstashID";

		try

		{

			$result = $pdo3->prepare("$deleteMovement")->execute();

		}

		catch (PDOException $e)

		{

				$error = 'Error fetching user: ' . $e->getMessage();

				echo $error;

				exit();

		}

			

		} else if ($extstash > 0) {

			

			if ($extstashID == '') {

				

			  $updateMovement = sprintf("INSERT INTO b_productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",

			  $purchaseDate, '2', $purchaseid, $extstash, '6', '1');

		  

			} else {

				

		$updateMovement = sprintf("UPDATE b_productmovements SET quantity = '%f' WHERE movementid = $extstashID;",

			$extstash

);

			}

		try

		{

			$result = $pdo3->prepare("$updateMovement")->execute();

		}

		catch (PDOException $e)

		{

				$error = 'Error fetching user: ' . $e->getMessage();

				echo $error;

				exit();

		}

		}

		

		

		// Query to update purchase

		$updatePurchase = sprintf("UPDATE b_purchases SET purchasePrice = '%f', salesPrice = '%f', purchaseQuantity = '%f', adminComment = '%s', closingComment = '%s', inMenu = '%d', provider = '%d', barCode = '%s' WHERE purchaseid = $purchaseid;",

			$purchasePrice,

			$salesPrice,

			$purchaseQuantity,

			$adminComment,

			$closingComment,

			$inMenu,

			$provider,

			$barCode

);

			

		try

		{

			$result = $pdo3->prepare("$updatePurchase")->execute();

		}

		catch (PDOException $e)

		{

				$error = 'Error fetching user: ' . $e->getMessage();

				echo $error;

				exit();

		}

			

		// On success: redirect.

		$_SESSION['successMessage'] = $lang['purchases-updatesuccess'];

		header("Location: bar-purchase.php?purchaseid={$purchaseid}");

		exit();

	}

	/***** FORM SUBMIT END *****/

	

	

	

	// Does purchase ID exist?

	if (!$_GET['purchaseid']) {

		echo $lang['error-nopurchselected'];

		exit();

	} else  {

		$purchaseid = $_GET['purchaseid'];

	}



	

	$validationScript = <<<EOD

    $(document).ready(function() {

	    	    

	  $('#registerForm').validate({

		  rules: {

			  purchaseQuantity: {

				  required: true

			  },

			  realQuantity: {

				  required: true

			  },

			  purchaseppg: {

				  required:"#inMenu:checked"

			  },

			  salesppg: {

				  required:"#inMenu:checked"

			  }

    	}, // end rules

    	errorPlacement: function(error, element) { },

    	  submitHandler: function() {

   $(".oneClick").attr("disabled", true);

   form.submit();

	    	  }

	  }); // end validate

  }); // end ready

EOD;



	// Query to look for purchase

	$purchaseDetails = "SELECT category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, adminComment, estClosing, closingComment, closedAt, inMenu, provider, barCode FROM b_purchases WHERE purchaseid = $purchaseid";

		try

		{

			$result = $pdo3->prepare("$purchaseDetails");

			$result->execute();

		}

		catch (PDOException $e)

		{

				$error = 'Error fetching user: ' . $e->getMessage();

				echo $error;

				exit();

		}

	

		$row = $result->fetch();

			$category = $row['category'];

			$productid = $row['productid'];

			$purchaseDate = $row['purchaseDate'];

			$purchasePrice = $row['purchasePrice'];

			$salesPrice = $row['salesPrice'];

			$purchaseQuantity = $row['purchaseQuantity'];

			$adminComment = $row['adminComment']; // Purchase comment, really

			$estClosing = $row['estClosing'];

			$closingComment = $row['closingComment']; // Only active when product closed (if even then)

			$closedAt = $row['closedAt'];

			$inMenu = $row['inMenu'];

			$perGramPurchase = number_format($purchasePrice,2);

			$perGramSale = number_format($salesPrice,2);

			$purchasePriceTotal = number_format($purchasePrice * $purchaseQuantity,2);

			$salesPriceTotal = number_format($salesPrice * $purchaseQuantity,2);

	$provider = $row['provider'];

	$barCode = $row['barCode'];



	$closeDiff = $closedAt - $estClosing;

	

	$growDetails = "SELECT name FROM b_providers WHERE id = $provider";

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

		$providerName = $row['name'];

	

		// Query to look for category

		$categoryDetails = "SELECT name FROM b_categories WHERE id = $category";

		try

		{

			$result = $pdo3->prepare("$categoryDetails");

			$result->execute();

		}

		catch (PDOException $e)

		{

				$error = 'Error fetching user: ' . $e->getMessage();

				echo $error;

				exit();

		}

	

		$row = $result->fetch();

			$categoryName = $row['name'];

			

		// Query to look for product

		$selectProducts = "SELECT name from b_products WHERE productid = $productid";

		try

		{

			$result = $pdo3->prepare("$selectProducts");

			$result->execute();

		}

		catch (PDOException $e)

		{

				$error = 'Error fetching user: ' . $e->getMessage();

				echo $error;

				exit();

		}

	

		$row = $result->fetch();

				$name = $row['name'];

		



		// Look up product movements. Also remember to save the new ones!! Using UPDATE!

		// sample taste

		$sample = "SELECT movementid, quantity FROM b_productmovements WHERE purchaseid = $purchaseid AND movementTypeid = 8 AND doneAtRegistration = 1";

		try

		{

			$result = $pdo3->prepare("$sample");

			$result->execute();

		}

		catch (PDOException $e)

		{

				$error = 'Error fetching user: ' . $e->getMessage();

				echo $error;

				exit();

		}

	

		$row = $result->fetch();

				$sample = $row['quantity'];

				$sampleID = $row['movementid'];

				

		// display jar

		$displayjar = "SELECT movementid, quantity FROM b_productmovements WHERE purchaseid = $purchaseid AND movementTypeid = 9 AND doneAtRegistration = 1";

		try

		{

			$result = $pdo3->prepare("$displayjar");

			$result->execute();

		}

		catch (PDOException $e)

		{

				$error = 'Error fetching user: ' . $e->getMessage();

				echo $error;

				exit();

		}

	

		$row = $result->fetch();

				$displayjar = $row['quantity'];

				$displayjarID = $row['movementid'];

				

		// internal stash

		$intstash = "SELECT movementid, quantity FROM b_productmovements WHERE purchaseid = $purchaseid AND movementTypeid = 5 AND doneAtRegistration = 1";

		try

		{

			$result = $pdo3->prepare("$intstash");

			$result->execute();

		}

		catch (PDOException $e)

		{

				$error = 'Error fetching user: ' . $e->getMessage();

				echo $error;

				exit();

		}

	

		$row = $result->fetch();

				$intstash = $row['quantity'];

				$intstashID = $row['movementid'];

				

		// external stash

		$extstash = "SELECT movementid, quantity FROM b_productmovements WHERE purchaseid = $purchaseid AND movementTypeid = 6 AND doneAtRegistration = 1";

		try

		{

			$result = $pdo3->prepare("$extstash");

			$result->execute();

		}

		catch (PDOException $e)

		{

				$error = 'Error fetching user: ' . $e->getMessage();

				echo $error;

				exit();

		}

	

		$row = $result->fetch();

				$extstash = $row['quantity'];

				$extstashID = $row['movementid'];



								





	pageStart($lang['title-editpurchase'], NULL, $validationScript, "ppurchase", "newpurchase2 admin", $lang['admin-editpurchase'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	

	

?>

<h5><?php echo "<a href='bar-purchase.php?purchaseid=$purchaseid'>$name</a> <span class='usergrouptext' style='margin-bottom: 13px; margin-left: 10px;'>$categoryName</span>"; ?></h5>



<form id="registerForm" action="" method="POST">

    <input type="hidden" name="category" value="<?php echo $category; ?>" />

    <input type="hidden" name="productID" value="<?php echo $productID; ?>" />

    <input type="hidden" name="purchaseid" value="<?php echo $purchaseid; ?>" />

    <input type="hidden" name="sampleID" value="<?php echo $sampleID; ?>" />

    <input type="hidden" name="displayjarID" value="<?php echo $displayjarID; ?>" />

    <input type="hidden" name="intstashID" value="<?php echo $intstashID; ?>" />

    <input type="hidden" name="extstashID" value="<?php echo $extstashID; ?>" />

    <input type="hidden" name="purchaseDate" value="<?php echo $purchaseDate; ?>" />



   <script>

    $(document).ready(function() {



   function compute() {

          var a = $('#purchaseQuantity').val();

          var b = $('#purchasePrice').val();

          var total = b / a;

          var roundedtotal = total.toFixed(2);

          $('#purchaseppg').val(roundedtotal);

        }

   function compute2() {

          var a = $('#purchaseQuantity').val();

          var b = $('#purchaseppg').val();

          var total = a * b;

          var roundedtotal = total.toFixed(2);

          $('#purchasePrice').val(roundedtotal);

        }

   function compute3() {

          var a = $('#purchaseQuantity').val();

          var b = $('#salesPrice').val();

          var total = b / a;

          var roundedtotal = total.toFixed(2);

          $('#salesppg').val(roundedtotal);

        }

   function compute4() {

          var a = $('#purchaseQuantity').val();

          var b = $('#salesppg').val();

          var total = a * b;

          var roundedtotal = total.toFixed(2);

          $('#salesPrice').val(roundedtotal);

        }



        $('#purchaseQuantity').on('keypress keyup', compute2);

        $('#purchaseQuantity').on('keypress keyup', compute4);

        $('#purchaseppg').on('keypress keyup', compute2);

        $('#salesppg').on('keypress keyup', compute4);

        $('#purchasePrice').on('keypress keyup', compute);

        $('#salesPrice').on('keypress keyup', compute3);



  }); // end ready

        // KONSTANT CODE UPDATE BEGIN

    var btncount= 1;

    function addMoreDiscount(){

        if(btncount < 11){

            var unitName = "<?php echo $lang['units-grams']; ?>";

            var unitPrice = "<?php echo $lang['add-total']; ?>";

            $("#volumeDiv").after('<tr><td>'+ unitName +'<input type="number" lang="nb" class="fourDigit purchaseNumber" name="volume_unit[]" /> </td><td>'+ unitPrice + '<input type="number" lang="nb" class="fourDigit purchaseNumber" name="volume_unit_price[]" /><?php echo $_SESSION['currencyoperator'] ?></td></tr><br>');

        }else{

            $("#addMoreDiscountButton").hide();

        }

        btncount++;

    }

    // KONSTANT CODE UPDATE END

   </script>

    

  <center>

	 <div class='actionbox-np2'>

		  <div class="mainboxheader"><?php echo $lang['global-details']; ?></div>

		  <div class="boxcontent">

			  <table class="np-table">

					   <tr>

					    <td class="biggerFont">

					    	<?php echo $lang['add-amountpurchased']; ?>

					    	<input type="number" lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="purchaseQuantity" name="purchaseQuantity" value="<?php echo $purchaseQuantity; ?>"  placeholder = "u" />

					    </td>

					   </tr>

					   <tr>

						<td class="biggerFont">

							 <span><?php echo $lang['add-showinmenu']; ?>?</span>

						 	<div class="fakeboxholder">	

						 	 

							 <label class="control">

							  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

							  <input type="checkbox" name="inMenu" id="inMenu" value="1" <?php if ($inMenu == 1) { echo "checked"; } ?>>

							  <div class="fakebox" style="top: 5px;margin-left: 30px;"></div>

							 </label>

							</div>

						</td>

					   </tr>

					   <tr>

					    <td class="biggerFont left"><?php echo $lang['provider']; ?>

					   

					     <select name='provider' class="defaultinput-no-margin-smallborder floatright" style="width: 120px;">

					      <option value='<?php echo $provider; ?>'><?php echo $providerName; ?></option>

					<?php

						$PRquery = "SELECT id, name FROM b_providers WHERE id <> $provider";

							try

							{

								$results = $pdo3->prepare("$PRquery");

								$results->execute();

							}

							catch (PDOException $e)

							{

									$error = 'Error fetching user: ' . $e->getMessage();

									echo $error;

									exit();

							}

						

							while ($PRtype = $results->fetch()) {

							$id = $PRtype['id'];

							$name = $PRtype['name'];

							

							echo "<option value='$id'>$name</option>";

							

						}

							

					?>

					     </select>

					</td>

					   </tr>

			  </table>

		</div>

	 </div>

	 

	 

	

	 <div class='actionbox-np2'>

		  <div class="mainboxheader"><?php echo $lang['add-purchaseprice']; ?></div>

		  <div class="boxcontent">

			  <table class="np-table">

			   <tr>

			    <td class="left"><?php echo $lang['add-perunit']; ?>

			    	<input type='number' lang='nb' class='fourDigit defaultinput-no-margin-smallborder floatright' id='purchaseppg' name='purchaseppg' value="<?php echo $perGramPurchase; ?>" placeholder = "<?php echo $_SESSION['currencyoperator'] ?>" /> 

			    </td>

			   </tr>

			   <tr>

			    <td class="left"><?php echo $lang['add-total']; ?>

			    	<input type="number" lang="nb" id="purchasePrice" name="purchasePrice" class="fourDigit defaultinput-no-margin-smallborder floatright" value="<?php echo $purchasePriceTotal; ?>" placeholder = "<?php echo $_SESSION['currencyoperator'] ?>" />

				</td>

			   </tr>

			  </table>

		 </div> 

	 </div>

	 <br /> 

	 

	 

	 <div class="actionbox-np2" style="height:260px;">



	  <div class="mainboxheader"><?php echo $lang['add-dispenseprice']; ?></div>

		 <div class='boxcontent'>

		  <table class="np-table">

		   <tr>

		    <td class="left"><?php echo $lang['add-perunit']; ?>

		    	<input type='number' lang='nb' class='fourDigit defaultinput-no-margin-smallborder floatright' id='salesppg' name='salesppg' value="<?php echo $perGramSale; ?>" /> <?php echo $_SESSION['currencyoperator'] ?>

			</td>

		   </tr>

		   <tr>

		    <td class="left"><?php echo $lang['add-total']; ?>

		      <input type="number" lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="salesPrice" name="salesPrice" value="<?php echo $salesPriceTotal; ?>" /> <?php echo $_SESSION['currencyoperator'] ?>

		  	</td>

		   </tr>

		  </table>

		 </div>



	  </div>

	 

	 <!-- // KONSTANT CODE UPDATE BEGIN -->

 <div class='actionbox-np2'>

  <div class="mainboxheader"><?php echo $lang['volume-discounts']; ?></div>

  <div class="boxcontent">

  <table class="np-table">

<?php 

    $volumeDiscounts = "SELECT * FROM b_volume_discounts WHERE purchaseid = $purchaseid";

    $result = $pdo3->prepare("$volumeDiscounts");

    $result->execute();

    while ($rs = $result->fetch()) { ?>

    <tr>

        <td><?php echo $lang['units-grams']; ?>

         <input type='number' lang='nb' class='fourDigit purchaseNumber' name='volume_unit[]' value="<?php echo $rs['units'];?>" /></td>

        <td><?php echo $lang['add-total']; ?>

          <input type="number" lang="nb" class="fourDigit purchaseNumber" name="volume_unit_price[]" value="<?php echo $rs['amount'];?>" /><?php echo  $_SESSION['currencyoperator'] ?></td>

    </tr>

        

<?php } ?>      

   <tr id="volumeDiv">

    <td><?php echo $lang['units-grams']; ?>

       <input type='number' lang='nb' class='fourDigit purchaseNumber' name='volume_unit[]' /></td>

    <td><?php echo $lang['add-total']; ?>

   			<input type="number" lang="nb" class="fourDigit purchaseNumber" name="volume_unit_price[]" /><?php echo  $_SESSION['currencyoperator'] ?></td>

   <br>

   </tr>

    <tr>

      

        

      

    </tr>

  </table>

     <button type="button" onclick="addMoreDiscount()" class="cta1" id="addMoreDiscountButton"><?php echo $lang['add-more']; ?></button> 

	</div>

   </div>

  <br />

  <!-- // KONSTANT CODE UPDATE END -->

	  <div class='actionbox-np2'>

		  <div class="mainboxheader"><?php echo $lang['add-initialmovements']; ?></div>

		  <div class="boxcontent">

			  <table class="np-table">

			   <tr>

			    <td class="left"><?php echo $lang['add-sampletaste']; ?>

			   		<input type='number' lang='nb' class='fourDigit defaultinput-no-margin-smallborder floatright' name='sample' value='<?php echo $sample; ?>' placeholder ="u" />

			   	</td>

			   </tr>

			   <tr>

			    <td class="left"><?php echo $lang['add-displayjar']; ?>

			    	<input type='number' lang='nb' class='fourDigit defaultinput-no-margin-smallborder floatright' name='displayjar' value='<?php echo $displayjar; ?>' placeholder ="u"/>

			    </td>

			   </tr>

			   <tr>

				    <td class="left"><?php echo $lang['add-stashedint']; ?>

				    	<input type='number' lang='nb' class='fourDigit defaultinput-no-margin-smallborder floatright' name='intstash' value='<?php echo $intstash; ?>' placeholder ="u" />

				    </td>

			   </tr>

			   <tr>

			    <td  class="left"><?php echo $lang['add-stashedext']; ?>

			   	 <input type='number' lang='nb' class='fourDigit defaultinput-no-margin-smallborder floatright' name='extstash' value='<?php echo $extstash; ?>'  placeholder ="u"/> 

			   	</td>

			   </tr>

			  </table>

			</div>

	   </div>



	  <br />

	   <div class='actionbox-np2' style="height: 206px;">

		  <div class="mainboxheader"><?php echo $lang['global-comment']; ?></div>

		  <div class="boxcontent">

			<textarea name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>"><?php echo $adminComment; ?></textarea><br /><br />

		</div>

	   </div>

	   <div class='actionbox-np2' style="height: 206px;">

		  <div class="mainboxheader">Codigo de barra</div>

		   <div class="boxcontent">

				<input type='text' lang='nb' class='eightDigit defaultinput-no-margin-smallborder floatright' name='barCode' 	value='<?php echo $barCode; ?>' /><br /><br />

			</div>

	   </div>

	   



	<?php

		if ($closedAt != NULL) {

			echo "<div class='actionbox-np2'>";

			echo "<div class='mainboxheader'>Closing details</div>";

			echo "<div class='boxcontent'>";

			echo "Product closed at: $closedAt g ($closeDiff g)<br />";

			echo "Closing comment: <em>$closingComment</em><br /><br />";

			echo "<a href='close-purchase-2.php?purchaseid=$purchaseid'>Edit closing details</a><br />";

			echo "</div>";

			echo "</div>";

	}

	?>







 <center> <button class='oneClick cta1nm' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button></center>



  </form>

</center>

<?php  displayFooter(); ?>



