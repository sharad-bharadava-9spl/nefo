<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-inv.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  ignore: [],
		  rules: {
			  'attach_files[]': {
				  required: true
			  },
              'invoices[][invno]':{
				  required: true
              }
    	}, // end rules
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

  }); // end ready
EOD;

	pageStart("CCS", NULL, $validationScript, "pprofilenew", "donations fees", "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

?>
<center>
 <form id="registerForm" action="" method="POST" enctype="multipart/form-data">

<div id="mainbox-no-width" style='text-align: left;'>
 <div id="mainboxheader">
  <?php if ($_SESSION['lang'] == 'es') { echo 'Subir justificante'; } else { echo 'Upload proof of payment'; } ?>
 </div>
 <div class='boxcontent'>

<style>
.inputfile {
	width: 0.1px;
	height: 0.1px;
	opacity: 0;
	overflow: hidden;
	position: absolute;
	z-index: -1;
}
.inputfile + label {
	display: inline-block;
	width: 170px;
	padding: 5px;
	margin: 20px;
	background-color: #00a48c;
	color: white;
	font-size: 18px;
	border-radius: 4px;
	position: relative;
	text-align: center;
	margin-bottom: 25px;
	text-transform: uppercase;
	border: 0;
	cursor: pointer;
}

.inputfile:focus + label,
.inputfile + label:hover {
	opacity: 0.8;
}
</style>
<center><input type="file" id="file" class="inputfile" name="attach_files[]" style='border: 0; margin-left: 10px;' data-multiple-caption="{count} files selected" /><label for="file" style='width: 200px;'><span><?php if ($_SESSION['lang'] == 'es') { echo 'Pincha aqui para adjuntar archivo'; } else { echo 'Click here to attach file'; } ?></span></label>
<script>
'use strict';

;( function( $, window, document, undefined )
{
	$( '.inputfile' ).each( function()
	{
		var $input	 = $( this ),
			$label	 = $input.next( 'label' ),
			labelVal = $label.html();

		$input.on( 'change', function( e )
		{
			var fileName = '';

			if( this.files && this.files.length > 1 )
				fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
			else if( e.target.value )
				fileName = e.target.value.split( '\\' ).pop();

			if( fileName )
				$label.find( 'span' ).html( fileName );
			else
				$label.html( labelVal );
		});

		// Firefox bug fix
		$input
		.on( 'focus', function(){ $input.addClass( 'has-focus' ); })
		.on( 'blur', function(){ $input.removeClass( 'has-focus' ); });
	});
})( jQuery, window, document );
</script>
<br /><br />
<strong><?php if ($_SESSION['lang'] == 'es') { echo 'Elige facturas'; } else { echo 'Choose invoices'; } ?>:</strong><br /><br />
<table class='default noborder'>
<?php

	$query = "SELECT invno, invdate, action, cutoffdate, promise, paid, amount FROM invoices WHERE paid = '' AND customer = '$customer'";
	try
	{
		$results = $pdo->prepare("$query");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$i = 0;
	while ($row = $results->fetch()) {
		
		$invno2 = $row['invno'];
		$invdate = date("d-m-Y", strtotime($row['invdate']));
		$amount = $row['amount'];
		$cutoffdate = date("d-m-Y", strtotime($row['cutoffdate']));
		$promise = date("d-m-Y", strtotime($row['promise']));
		
		if ($invno2 == $invno) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		
		echo "<tr><td><input type='checkbox' name='invoices[$i][invno]' value='$invno2' /></td><td>$invno2</td><td>$invdate</td><td class='right'>$amount &euro;</td></tr>";
		
		$i++;
		
	}

?>
</table>
<br /><br />

  <input type='hidden' name='confirmed' value='yes' />
  <input type='hidden' name='ticketid' value='<?php echo $ticketid; ?>' />
  <input type='hidden' name='number' value='<?php echo $customer; ?>' />
</div></div>
<br /><br />
<center>  <button class='oneClick okbutton2' name='oneClick' type="submit" style='margin-left: -2px; width: 286px;'><?php echo $lang['global-confirm']; ?></button></td>
</center>
 </form>
<script src="https://cdn.tiny.cloud/1/9pxfemefuncr8kvf2f5nm34xwdg8su9zxhktrj66loa5mexa/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

<?php displayFooter();