<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	?>
<style>
table.product_table, table.product_table th, table.product_table td {
  border: 2px solid black;
  border-collapse: collapse;
    margin-left: auto;
  margin-right: auto;
}
table.product_table th, table.product_table td {
  padding: 10px;
}
#product_dialog .error{
	 border: 2px solid #eba4a2 !important;
    color: #d95350 !important;
    background-color: yellow !important;
}
</style>
	<?php

	$validationScript = <<<EOD
    $(document).ready(function() {
/*		  $('.defaultinput').each(function() {
	        $(this).rules("add", 
	            {
	                required: true,
	            });
	    });*/

	  $('#registerForm1').validate({
		  rules: {
			  remove_product: {
				  required: true
			  },
			  remove_product_unit: {
				  required: true
			  }
    	},
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		}
	  }); // end validate

	  $('#registerForm2').validate({
		  rules: {
				create_product_unit:{
					required: true
				}
    	},
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate

	 $('#registerForm3').validate({
		  rules: {
			  name: {
				  required: true
			  }
    	}, // end rules,
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},

	  }); // end validate
  }); // end ready
EOD;


  pageStart("Transform Product", NULL, $validationScript, "paddremove", "admin", "Transform Product", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	// Query to look up flowers
	$selectFlower = "SELECT a.flowerid, a.name, b.purchaseid, b.realQuantity FROM flower a, purchases b WHERE a.flowerid = b.productid AND b.category = 1 ORDER by name ASC";
		try
		{
			$result = $pdo3->prepare("$selectFlower");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$x =0;
		while ($row = $result->fetch()) {
			
			$name = $row['name'];
			$purchaseid = $row['purchaseid'];
			
			$flower_row[$x]['name'] = $name;
			$flower_row[$x]['id'] = $purchaseid;
			$flower_row[$x]['type'] = 0;

		  	$x++;
  		}
	// Query to look up extract
	$selectExtract = "SELECT a.extractid, a.name, b.purchaseid, b.realQuantity FROM extract a, purchases b WHERE a.extractid = b.productid AND b.category = 2 ORDER by name ASC";
	try
	{
		$result = $pdo3->prepare("$selectExtract");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$y =0;
	while ($extract = $result->fetch()) {
		$purchaseid = $extract['purchaseid'];


		$extract_row[$y]['name'] = $extract['name']; 
		$extract_row[$y]['id'] = $extract['purchaseid']; 
		$extract_row[$y]['type'] = 0; 
		$y++;
	}

		// Query to look up products
	$selectProduct = "SELECT a.productid, a.name, b.purchaseid, b.realQuantity, c.type FROM products a, purchases b, categories c WHERE a.productid = b.productid AND b.category > 2 AND b.category = c.id ORDER by name ASC";
	try
	{
		$resultProduct = $pdo3->prepare("$selectProduct");
		$resultProduct->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$z=0;
	while ($product = $resultProduct->fetch()) {
			$purchaseid = $product['purchaseid'];
			$name = $product['name'];

			
	  			$product_row[$z]['name'] = $name;
	  			$product_row[$z]['id'] = $purchaseid;
	  			$product_row[$z]['type'] = $product['type'];
	  			
	  	$z++;		
  	}
?>

<div class="actionbox-np2 mainbox-no-width-cls" style="min-height:200px;">
	<div class="main_box_title">Product to remove</div>
	<div class="boxcontent">
		<form id="registerForm1" action="" method="POST" novalidate="novalidate">
			<div class="field_wrapper">
				<div>
					<span class="smallgreen">Select product:</span>
					<select class="fakeInput defaultinput advancedSelect" data-id='remove_0' name="remove_product[]" style="width:260px;" required="">
						<option value="">Please Select</option>
						<optgroup label="Flowers">
						<?php foreach($flower_row as $flowers){ ?>	
					      <option value="<?php echo $flowers['id'] ?>" data-name="<?php echo $flowers['name'] ?>" data-type="<?php echo $flowers['type'] ?>"><?php echo $flowers['name'] ?></option>
					    <?php } ?>  
					    </optgroup>
					    <optgroup label="Extracts">
					   	<?php foreach($extract_row as $extracts){ ?>	
					      <option value="<?php echo $extracts['id'] ?>" data-name="<?php echo $extracts['name'] ?>" data-type="<?php echo $extracts['type'] ?>"><?php echo $extracts['name'] ?></option>
					    <?php } ?>  
					    </optgroup>					    
					    <optgroup label="Other Products">
					    <?php foreach($product_row as $products){ ?>	
					      <option value="<?php echo $products['id'] ?>" data-name="<?php echo $products['name'] ?>" data-type="<?php echo $products['type'] ?>"><?php echo $products['name'] ?></option>
					    <?php } ?>  
					    </optgroup>			
					</select>
			        <input type="number" class="defaultinput fourDigit punits" name="remove_product_unit[]" placeholder="gm/units" id='remove_0' required="" />
			        <a href="javascript:void(0);" class="add_button" title="Add field"><img style="vertical-align:middle;" src="images/plus-new.png"/></a>
			    </div> 
		    </div>   
		    <br>    
			<button class="oneClick cta4" id="remove_product_btn" name="oneClick" type="button" style="margin-top: 76px;">Remove</button>
		</form>
	</div>
</div>
<div class="actionbox-np2 mainbox-no-width-cls" style="min-height:200px;">
	<div class="main_box_title">Product to Create</div>
	<div class="boxcontent">
		<form id="registerForm2" action="transform-product-process.php" method="POST" novalidate="novalidate">
			<input type="hidden" name="remove_product_selected" id="remove_product_selected">
			<input type="hidden" name="remove_unit_selected" id="remove_unit_selected">
			<div id="selected_prdocut_info"></div><br>
		    <div class="create_products" style="display:none;">
			    <strong>You are creating:</strong><br><br>
					<div class="field_wrapper2">
				    	<div>
								<span class="smallgreen">Select product:</span>
								<select class="fakeInput defaultinput advancedSelect2" data-id="create_0" name="create_product[]" style="width:260px;">
									<option value="">Please Select</option>
									<optgroup label="Flowers">
									<?php foreach($flower_row as $flowers){ ?>	
								      <option value="<?php echo $flowers['id'] ?>" data-type="<?php echo $flowers['type'] ?>"><?php echo $flowers['name'] ?></option>
								    <?php } ?>  
								    </optgroup>
								    <optgroup label="Extracts">
								   	<?php foreach($extract_row as $extracts){ ?>	
								      <option value="<?php echo $extracts['id'] ?>" data-type="<?php echo $extracts['type'] ?>"><?php echo $extracts['name'] ?></option>
								    <?php } ?>  
								    </optgroup>					    
								    <optgroup label="Other Products">
								    <?php foreach($product_row as $products){ ?>	
								      <option value="<?php echo $products['id'] ?>" data-type="<?php echo $products['type'] ?>"><?php echo $products['name'] ?> </option>
								    <?php } ?>  
								    </optgroup>			
								</select>
								<input type="number" class="defaultinput fourDigit punits2" id="create_0" name="create_unit[]" placeholder="gm/units"  />
						        <a href="javascript:void(0);" class="add_button2" title="Add field"><img style="vertical-align:middle;" src="images/plus-new.png"/></a>
					    </div> 
			    </div>
			    Or
						<div id="create_box_0"><a href="JavaScript:void(0)" style="text-decoration: underline;" class="product_pop">Click here</a> to create a new product</div>
					<br>	 
				    <div class="products_div">
				    	
				    	<table class='product_table'>
				    	</table>
				    </div>
		    </div> 

		    <br>  

			<button class="oneClick cta4" name="oneClick2" id="create_btn" type="submit" style="margin-top: 76px; display: none;">Create</button>
		</form>
 
			 
	</div>
</div>
	<div id="product_dialog" title="Create Product" style="display:none">
		<form id="registerForm3" action="" method="POST">
			<?php		
					$selectCats = "SELECT id, name, type FROM categories ORDER by id ASC";
					
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
					?>
					<select name="category_id" class="defaultinput" required="">
						<option value="">Select category</option>
						<?php		
								while($cat_row = $resultCats->fetch()){
										$categoryid = $cat_row['id'];
										$categoryname = $cat_row['name'];
									    $catType = $cat_row['type'];

						?>
							<option value="<?php echo $categoryid ?>"><?php echo $categoryname ?></option>
						<?php  } ?>	
					</select>
					   <span class="smallgreen"><?php echo $lang['global-name']; ?></span><input type="text" name="name" id="productName" class='tenDigit defaultinput' placeholder="" />
					   <div id='extract_input' style="display:none;">
				   			<span class="smallgreen">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-type']; ?></span><input type="text" name="extracttype" class='tenDigit defaultinput' value="<?php echo $extracttype; ?>" />
					 	 </div>
					 	 <div id='other_input'>
					   		<span class="smallgreen"><?php echo $lang['extracts-secondbreed']; ?></span><input type="text" name="breed2" class='tenDigit defaultinput' value="<?php echo $breed2; ?>" />
					 		</div>
					   <div id='other_select'>
						  <select name="flowertype" class='defaultinput' >
						   <option value=""><?php echo $lang['global-type']; ?>:</option>
						   <option value="Indica">Indica</option>
						   <option value="Sativa">Sativa</option>
						   <option value="Hybrid"><?php echo $lang['global-hybrid']; ?></option>
						  </select>
						</div>
						<div id='extract_select' style="display:none;">
							  <select name="extract" class='defaultinput' >
							   <option value=""><?php echo $lang['global-extract']; ?>:</option>
							   <option value="Dry"><?php echo $lang['extracts-dry']; ?></option>
							   <option value="Ice"><?php echo $lang['extracts-ice']; ?></option>
							   <option value="Wax"><?php echo $lang['extracts-wax']; ?></option>
							   <option value="Oil"><?php echo $lang['extracts-oil']; ?></option>
							   <option value="Ethanol"><?php echo $lang['extracts-ethanol']; ?></option>
							   <option value="Glycerine"><?php echo $lang['extracts-glycerine']; ?></option>
							  </select>
						</div>
						<br>	
					  <span class="smallgreen">% Sativa</span><input type="number" lang="nb" name="sativaPercentage" class='defaultinput' /><br>
					  <span class="smallgreen">% THC</span><input type="number" lang="nb" name="THC" class="defaultinput" value="<?php echo $THC; ?>" /><br>
					  <span class="smallgreen">% CBD</span><input type="number" lang="nb" class="defaultinput" name="CBD" value="<?php echo $CBD; ?>" /><br>
					  <span class="smallgreen">% CBN</span><input type="number" lang="nb" class="defaultinput" name="CBN" value="<?php echo $CBN; ?>" />
					  <br />
					  <span class="smallgreen"><?php echo $lang['add-amountpurchased']; ?></span><input type='text' lang="nb" class="defaultinput tenDigit" id="purchaseQuantity" name="purchaseQuantity" placeholder="gr" required="" /> 
					  <span class="smallgreen">Price per gram</span><input type="text" lang="nb" class="fourDigit defaultinput" id="purchaseppg" name="purchaseppg" placeholder="€">   
					  <br>
					   <img src='images/info-new.png' style='margin-bottom: -1px;' />&nbsp;&nbsp;<span class="smallgreen"><?php echo $lang['extracts-description']; ?></span><textarea name="description" class="defaultinput"><?php echo $description; ?></textarea><br>
					   <img src='images/medical-new.png' style='margin-bottom: -1px;' />&nbsp;&nbsp;<span class="smallgreen"><?php echo $lang['extracts-medicaldesc']; ?></span><textarea name="medicaldescription" class="defaultinput"><?php echo $medicaldescription; ?></textarea>
		</form>	
					   <button class="oneClick cta4" name="new_product" id="create_new_btn" type="button" >create new product</button> 
	</div>
<?php  displayFooter(); ?>

<script type="text/javascript">
	function replaceAll(str, find, replace) {
    var escapedFind=find.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
    return str.replace(new RegExp(escapedFind, 'g'), replace);
 }

$(document).ready(function(){
	var flowers = <?php echo json_encode($flower_row) ?>;
	var extracts = <?php echo json_encode($extract_row) ?>;
	var products = <?php echo json_encode($product_row) ?>;

    var maxField = 100; //Input fields increment limitation
    var addButton = $('.add_button'); //Add button selector
    var wrapper = $('.field_wrapper'); //Input field wrapper
    var fieldHTML = "";
    fieldHTML += '<div><span class="smallgreen">Select product:</span><select class="fakeInput defaultinput advancedSelect" name="remove_product[str_index]" data-id="remove_str_index" style="width:260px;" required><option value="">Please Select</option>';
    fieldHTML += '<optgroup label="Flowers">';
    for(var i in flowers){

    	fieldHTML += '<option value="'+flowers[i].id+'" data-name="'+flowers[i].name+'" data-type="'+flowers[i].type+'">'+flowers[i].name+'</option>';
    }
    fieldHTML += '</optgroup><optgroup label="Extracts">';    
    for(var j in extracts){

    	fieldHTML += '<option value="'+extracts[j].id+'" data-name="'+extracts[j].name+'" data-type="'+extracts[j].type+'">'+extracts[j].name+'</option>';
    }    
    fieldHTML += '</optgroup><optgroup label="Other products">';    
    for(var k in products){

    	fieldHTML += '<option value="'+products[k].id+'" data-name="'+products[k].name+'" data-type="'+products[k].type+'">'+products[k].name+'</option>';
    }
    fieldHTML += '</optgroup><input type="number" class="defaultinput fourDigit punits" name="remove_product_unit[str_index]" placeholder="gm/units" id="remove_str_index" required /><a href="javascript:void(0);" class="remove_button"><img style="vertical-align:middle;" src="images/minus-new.png"/></a></div>'; //New input field html 
    var x = 0; //Initial field counter is 1
    
    //Once add button is clicked
    $(addButton).click(function(){
        //Check maximum number of input fields
        if(x < maxField && $("#registerForm1").valid()){ 
            x++; //Increment field counter
            var replaced_fieldHTML = replaceAll(fieldHTML, "str_index", x);
            $(wrapper).append(replaced_fieldHTML); //Add field html
            createProducts();
        }
    });
    
    //Once remove button is clicked
    $(wrapper).on('click', '.remove_button', function(e){
        e.preventDefault();
        $(this).parent('div').remove(); //Remove field html
        x--; //Decrement field counter
         createProducts();
    });

    // remove products
    $("#remove_product_btn").click(function(){

    	if($("#registerForm1").valid()){
 					createProducts();
    	}


    });

    // add more fields to create products

		    var addButton2 = $('.add_button2'); //Add button selector
		    var wrapper2 = $('.field_wrapper2'); //Input field wrapper
		    var fieldHTML2 = "";
		    fieldHTML2 += '<div><span class="smallgreen">Select product:</span><select class="fakeInput defaultinput advancedSelect2" data-id="create_str_index2" name="create_product[str_index2]" style="width:260px;"><option value="">Please Select</option>';
		    fieldHTML2 += '<optgroup label="Flowers">';
		    for(var i in flowers){

		    	fieldHTML2 += '<option value="'+flowers[i].id+'" data-name="'+flowers[i].name+'" data-type="'+flowers[i].type+'">'+flowers[i].name+'</option>';
		    }
		    fieldHTML2 += '</optgroup><optgroup label="Extracts">';    
		    for(var j in extracts){

		    	fieldHTML2 += '<option value="'+extracts[j].id+'" data-name="'+extracts[j].name+'" data-type="'+extracts[j].type+'">'+extracts[j].name+'</option>';
		    }    
		    fieldHTML2 += '</optgroup><optgroup label="Other products">';    
		    for(var k in products){

		    	fieldHTML2 += '<option value="'+products[k].id+'" data-name="'+products[k].name+'" data-type="'+products[k].type+'">'+products[k].name+'</option>';
		    }
		    fieldHTML2 += '</optgroup></select><input type="number" class="defaultinput fourDigit punits2" name="create_unit[str_index2]" id="create_str_index2" placeholder="gm/units" />'; //New input field html s

		    	fieldHTML2 += '<a href="javascript:void(0);" class="remove_button2"><img style="vertical-align:middle;" src="images/minus-new.png"/></a></div>'
		    var y = 0; //Initial field counter is 1
		    
		    //Once add button is clicked
		    $(addButton2).click(function(){
			      var nameArr2 = [];
	  				var unitArr2 = [];
	  				var typeArr2 = [];
		        //Check maximum number of input fields
		        if(y < maxField && $("#registerForm2").valid()){ 
		        	y++; //Increment field counter
		  		   $.each($(".advancedSelect option:selected"), function(){ 
			           var $val = $(this).data('name');
			           var $type = $(this).data('type');
			           nameArr2.push($val);
			           typeArr2.push($type);
			        });    	 
			    	 $.each($(".punits"), function(){  
			           var $val2 = $(this).val();
			           unitArr2.push($val2);
			        });
			    	 var selected_prodcuts_ids2 = $(".advancedSelect option:selected")
              .map(function(){return $(this).val();}).get();

		            var replaced_fieldHTML2 = replaceAll(fieldHTML2, "str_index2", y);
		            $(wrapper2).append(replaced_fieldHTML2); //Add field html
		          	createProductBox();
		        }
		    });
		    
		    //Once remove button is clicked
		    $(wrapper2).on('click', '.remove_button2', function(e){
		        e.preventDefault();
		        $(this).parent('div').remove(); //Remove field html
		        y--; //Decrement field counter
		        createProductBox();
		    });

       // making a function to create products

       function createProducts(){
       				var nameArr = [];                                                                       
	            var unitArr = [];
	            var typeArr = [];
       			  $(".create_products").show();
			    		$("#create_btn").show();
			    		
						 var selected_prodcuts_ids = $(".advancedSelect option:selected")
			              .map(function(){return $(this).val();}).get();
			           $("#remove_product_selected").val(selected_prodcuts_ids);
			            var selected_units = $("input.punits")
			              .map(function(){return $(this).val();}).get();
			           $("#remove_unit_selected").val(selected_units);
				    	 $.each($(".advancedSelect option:selected"), function(){ 
				           var $val = $(this).data('name');
				           var $type = $(this).data('type');
				           typeArr.push($type);
				           nameArr.push($val);
				        });    	 
				    	 var total = 0;
				    	 $.each($(".punits"), function(){  
				           var $val2 = $(this).val();
				           unitArr.push($val2);
				           total += parseInt($val2);
				        });

				    	 var selectedHTML = "<span><strong>You Have Selected</strong></span><br>";
				    	 for(i in nameArr){
				    	 	var place_text = "gm/units";
				    		if(typeArr[i] == 0){
				    			place_text = "gm";
				    		}else if(typeArr[i] == 1){
				    			place_text = "units";
				    		}
				    		 selectedHTML += "<span class='smallgreen'>"+nameArr[i]+"</span> : "+unitArr[i]+ " "+place_text+"<br>";
				    	}
				    	selectedHTML += "<strong>Total</strong>: "+total;
				    	$("#selected_prdocut_info").html(selectedHTML);

       }
       function createProductBox(name=null, unit=null){
       		 var selected_prodcuts_names = $(".advancedSelect2 option:selected")
			              .map(function(){return $(this).text();}).get();
					 if(name !=null){
					 	selected_prodcuts_names.push(name);
					 }         
					 var selected_units = $("input.punits2")
					              .map(function(){return $(this).val();}).get();
					if(unit !=null){
					 	selected_units.push(selected_units);
					 }              
					 /*var output_table = "";
					 for(i in selected_prodcuts_names){
					 	output_table += "<tr><td>"+selected_prodcuts_names[i]+"</td>"
					 					+"<td>"+selected_units[i]+"</td></tr>";
					 }
					 $(".product_table").html(output_table);*/
       }


       function getFormData($form){
		    var unindexed_array = $form.serializeArray();
		    var indexed_array = {};

		    $.map(unindexed_array, function(n, i){
		        indexed_array[n['name']] = n['value'];
		    });

		    return indexed_array;
		}
		    // create product form validations

		    $("#create_btn").click(function(){
		    	//$("#registerForm2").valid();
		    });

		    $(document).on("change","select[name='category_id']",function(){
		    	var this_selected_id = $(this).val();
		    	if(this_selected_id == 2){
		    		$("#extract_input").show();
		    		$("#extract_select").show();
		    		$("#other_input").hide();
		    		$("#other_select").hide();
		    	}else{
		    		$("#extract_input").hide();
		    		$("#extract_select").hide();
		    		$("#other_input").show();
		    		$("#other_select").show();
		    	}
		    });
		    // create ne wproduct validations and submit
				$.fn.serializeObject = function()
				{
				    var o = {};
				    var a = this.serializeArray();
				    $.each(a, function() {
				        if (o[this.name] !== undefined) {
				            if (!o[this.name].push) {
				                o[this.name] = [o[this.name]];
				            }
				            o[this.name].push(this.value || '');
				        } else {
				            o[this.name] = this.value || '';
				        }
				    });
				    return o;
				};
		    $(document).on("click","#create_new_btn",function(){
		    	if($("#registerForm3").valid()){
		    		var product_name = $("#productName").val();
		    		var product_quant = $("#purchaseQuantity").val();
		    		var $form = $("#registerForm3");
		    		var data = $form.serializeObject();
		    		// Put the object into storage
           
		    		var append_html = "<div><span class='smallgreen'>New product:</span>&nbsp;&nbsp;<span><strong>"+product_name+"</strong></span>&nbsp;&nbsp;<span>"+product_quant+"</span><input type='hidden' name='new_products[]'  value='"+JSON.stringify(data)+"'>";
		    		append_html += '<a href="javascript:void(0);" class="remove_button2">&nbsp;&nbsp;<img style="vertical-align:middle;" src="images/minus-new.png"/></a></div>';
		    		$(".field_wrapper2").append(append_html);
		    		createProductBox(product_name, product_quant);
		    		
		    		$('#product_dialog').dialog('close').effect( "highlight", "slow" );
		    	}

		    });

		  $(document).on("change", ".advancedSelect", function(){
		  		createProducts();
		  		var this_id = $(this).data('id');
		  		var cat_type = $('option:selected', this).data('type');
		  		var place_text = "gm/units";
		  		if(cat_type == 1){
		  			 place_text = "units";
		  		}else if(cat_type == 0){
		  			 place_text = "gm";
		  		}
		  		$("#"+this_id).attr("placeholder",place_text);

		  });		  

		  $(document).on("change", ".advancedSelect2", function(){
		  		createProducts();
		  		createProductBox();
		  		var this_id = $(this).data('id');
		  		var cat_type = $('option:selected', this).data('type');
		  		var place_text = "gm/units";
		  		if(cat_type == 1){
		  			 place_text = "units";
		  		}else if(cat_type == 0){
		  			 place_text = "gm";
		  		}
		  		$("#"+this_id).attr("placeholder",place_text);

		  });		  

		  $(document).on("change keyup blur", ".punits", function(){
		  		createProducts();
		  });		  

		  $(document).on("change keyup blur", ".punits2", function(){
		  		createProductBox();
		  });  

		  $(".product_pop").click(function(){
		  			$("#registerForm3")[0].reset();
			  		$("#product_dialog").dialog({
						autoOpen: false,
						autoResize: true,
					});
					$('#product_dialog').dialog('open').effect( "highlight", "slow" );
		  })


});
</script>