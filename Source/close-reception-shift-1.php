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
	
	if ($_POST['step1'] != 'complete' && !isset($_GET['recount'])) {
		echo $lang['global-onenotcomplete'];
		exit();
	}
	
	if (isset($_GET['recount'])) {
		$closeCount = $_SESSION['closeCount'];
		
		$oneCent = $closeCount['oneCent'];
		$twoCent = $closeCount['twoCent'];
		$fiveCent = $closeCount['fiveCent'];
		$tenCent = $closeCount['tenCent'];
		$twentyCent = $closeCount['twentyCent'];
		$fiftyCent = $closeCount['fiftyCent'];
		$oneEuro = $closeCount['oneEuro'];
		$twoEuro = $closeCount['twoEuro'];
		$fiveEuro = $closeCount['fiveEuro'];
		$tenEuro = $closeCount['tenEuro'];
		$twentyEuro = $closeCount['twentyEuro'];
		$fiftyEuro = $closeCount['fiftyEuro'];
		$hundredEuro = $closeCount['hundredEuro'];
		$coinsTot = $closeCount['coinsTot'];
		$notesTot = $closeCount['notesTot'];
		$tillTot = $closeCount['tillTot'];
		$banked = $closeCount['banked'];
		
		$oneCentFull = $closeCount['oneCentFull'];
		$twoCentFull = $closeCount['twoCentFull'];
		$fiveCentFull = $closeCount['fiveCentFull'];
		$tenCentFull = $closeCount['tenCentFull'];
		$twentyCentFull = $closeCount['twentyCentFull'];
		$fiftyCentFull = $closeCount['fiftyCentFull'];
		$oneEuroFull = $closeCount['oneEuroFull'];
		$twoEuroFull = $closeCount['twoEuroFull'];
		$fiveEuroFull = $closeCount['fiveEuroFull'];
		$tenEuroFull = $closeCount['tenEuroFull'];
		$twentyEuroFull = $closeCount['twentyEuroFull'];
		$fiftyEuroFull = $closeCount['fiftyEuroFull'];
		$hundredEuroFull = $closeCount['hundredEuroFull'];
		$coinsTotFull = $closeCount['coinsTotFull'];
		$notesTotFull = $closeCount['notesTotFull'];
		$tillTotFull = $closeCount['tillTotFull'];
		
		$TotCounted = $tillTotFull;
        $tillTotCounted = $tillTot;


	}
	
	$validationScript = <<<EOD
	
    $(document).ready(function() {
	    
	    
   function compute() {

          var a = $('#tillTotFull').val();
          var b = $('#tillTot').val();
          var c = a - b;

          
          $('#TotCounted').val(a);
          $('#tillTotCounted').val(b);
          $('#banked').val(c);

        }
        
		$(document).on('click keypress keyup blur', function () {
		    compute();
		});

	    	    
	  
		document.querySelector('button').addEventListener("click", function(){
		    window.btn_clicked = true;      //set btn_clicked to true
		});
		
		$(window).bind('beforeunload', function(){
		    if(!window.btn_clicked){
		        return "{$lang['closeday-leavepage']}";
		    }
		});
  }); // end ready
EOD;
	


	pageStart($lang['close-shift'], NULL, $validationScript, "pcloseday", "step2", $lang['closeday-rec-two'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo $_SESSION['pageHeader'];
	
?>

<br />        
<br />

<h1><?php echo $lang['all-cash']; ?></h1>
<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="close-reception-shift-2.php" method="POST">
<input type="hidden" name="step2" value="complete" />


<table>
 <tr>
  <td><?php echo $lang['add-total']; ?>:</td>
  <td><input type="number" lang="nb" name="tillTotFull" id="tillTotFull" class="fourDigit" value="<?php echo $tillTotFull; ?>" step="0.01" /></td>
 </tr>
</table>

<br /><br />
<h1><?php echo $lang['till-cash']; ?></h1>

<table>
 <tr>
  <td><?php echo $lang['add-total']; ?>:</td>
  <td><input type="number" lang="nb" name="tillTot" id="tillTot" class="fourDigit" value="<?php echo $tillTot; ?>" step="0.01" /></td>
 </tr>
</table>

<br /><br />
<h1><?php echo $lang['summary']; ?></h1>

<table>
 <tr>
  <td><?php echo $lang['all-cash']; ?>:</td>
  <td><input type="number" lang="nb" name="TotCounted" id="TotCounted" placeholder="&euro;" class="fourDigit" value="<?php echo $TotCounted; ?>" readonly /></td>
 </tr>
 <tr>
  <td>- <?php echo $lang['till-cash']; ?>:</td>
  <td><input type="number" lang="nb" name="tillTotCounted" id="tillTotCounted" placeholder="&euro;" class="fourDigit red" value="<?php echo $tillTotCounted; ?>" readonly /></td>
 </tr>
 <tr>
  <td><strong><?php echo $lang['closeday-banked']; ?>:</strong></td>
  <td><strong><input type="number" lang="nb" name="banked" id="banked" class="fourDigit" placeholder="&euro;" value="<?php echo $banked; ?>" readonly /></strong></td>
 </tr>
</table>


<br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['closeday-calculate']; ?></button>
</form>


<?php displayFooter(); ?>

