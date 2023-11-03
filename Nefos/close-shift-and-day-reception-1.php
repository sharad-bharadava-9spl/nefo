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
          var a = $('#oneCent').val();
          var b = $('#twoCent').val();
          var c = $('#fiveCent').val();
          var d = $('#tenCent').val();
          var e = $('#twentyCent').val();
          var f = $('#fiftyCent').val();
          var g = $('#oneEuro').val();
          var h = $('#twoEuro').val();
          var i = $('#fiveEuro').val();
          var j = $('#tenEuro').val();
          var k = $('#twentyEuro').val();
          var l = $('#fiftyEuro').val();
          var m = $('#hundredEuro').val();
          var total = (+a * 0.01) + (+b * 0.02) + (+c * 0.05) + (+d * 0.1) + (+e * 0.2) + (+f * 0.5) + (+g) + (+h * 2);
          var total2 = (+i * 5) + (+j * 10) + (+k * 20) + (+l * 50) + (+m * 100);
          var total3 = (+a * 0.01) + (+b * 0.02) + (+c * 0.05) + (+d * 0.1) + (+e * 0.2) + (+f * 0.5) + (+g) + (+h * 2) + (+i * 5) + (+j * 10) + (+k * 20) + (+l * 50) + (+m * 100);
          $('#coinsTot').val(total);
          $('#notesTot').val(total2);
          $('#tillTot').val(total3);
          
          var aFull = $('#oneCentFull').val();
          var bFull = $('#twoCentFull').val();
          var cFull = $('#fiveCentFull').val();
          var dFull = $('#tenCentFull').val();
          var eFull = $('#twentyCentFull').val();
          var fFull = $('#fiftyCentFull').val();
          var gFull = $('#oneEuroFull').val();
          var hFull = $('#twoEuroFull').val();
          var iFull = $('#fiveEuroFull').val();
          var jFull = $('#tenEuroFull').val();
          var kFull = $('#twentyEuroFull').val();
          var lFull = $('#fiftyEuroFull').val();
          var mFull = $('#hundredEuroFull').val();
          var totalFull = (+aFull * 0.01) + (+bFull * 0.02) + (+cFull * 0.05) + (+dFull * 0.1) + (+eFull * 0.2) + (+fFull * 0.5) + (+gFull) + (+hFull * 2);
          var total2Full = (+iFull * 5) + (+jFull * 10) + (+kFull * 20) + (+lFull * 50) + (+mFull * 100);
          var total3Full = (+aFull * 0.01) + (+bFull * 0.02) + (+cFull * 0.05) + (+dFull * 0.1) + (+eFull * 0.2) + (+fFull * 0.5) + (+gFull) + (+hFull * 2) + (+iFull * 5) + (+jFull * 10) + (+kFull * 20) + (+lFull * 50) + (+mFull * 100);
          $('#coinsTotFull').val(totalFull);
          $('#notesTotFull').val(total2Full);
          $('#tillTotFull').val(total3Full);

          var bnkcalc = total3Full - total3;
          
          $('#TotCounted').val(total3Full);
          $('#tillTotCounted').val(total3);
          $('#banked').val(bnkcalc);

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
	

	pageStart($lang['close-shift-and-day'], NULL, $validationScript, "pcloseday", "step2", $lang['closeday-rec-two'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo $_SESSION['pageHeader'];
	
?>
   <script>
    $(document).ready(function() {

   function compute() {
          var a = $('#oneCent').val();
          var b = $('#twoCent').val();
          var c = $('#fiveCent').val();
          var d = $('#tenCent').val();
          var e = $('#twentyCent').val();
          var f = $('#fiftyCent').val();
          var g = $('#oneEuro').val();
          var h = $('#twoEuro').val();
          var i = $('#fiveEuro').val();
          var j = $('#tenEuro').val();
          var k = $('#twentyEuro').val();
          var l = $('#fiftyEuro').val();
          var m = $('#hundredEuro').val();
          var total = (+a * 0.01) + (+b * 0.02) + (+c * 0.05) + (+d * 0.1) + (+e * 0.2) + (+f * 0.5) + (+g) + (+h * 2);
          var total2 = (+i * 5) + (+j * 10) + (+k * 20) + (+l * 50) + (+m * 100);
          var total3 = (+a * 0.01) + (+b * 0.02) + (+c * 0.05) + (+d * 0.1) + (+e * 0.2) + (+f * 0.5) + (+g) + (+h * 2) + (+i * 5) + (+j * 10) + (+k * 20) + (+l * 50) + (+m * 100);
          $('#coinsTot').val(total);
          $('#notesTot').val(total2);
          $('#tillTot').val(total3);
          
          var aFull = $('#oneCentFull').val();
          var bFull = $('#twoCentFull').val();
          var cFull = $('#fiveCentFull').val();
          var dFull = $('#tenCentFull').val();
          var eFull = $('#twentyCentFull').val();
          var fFull = $('#fiftyCentFull').val();
          var gFull = $('#oneEuroFull').val();
          var hFull = $('#twoEuroFull').val();
          var iFull = $('#fiveEuroFull').val();
          var jFull = $('#tenEuroFull').val();
          var kFull = $('#twentyEuroFull').val();
          var lFull = $('#fiftyEuroFull').val();
          var mFull = $('#hundredEuroFull').val();
          var totalFull = (+aFull * 0.01) + (+bFull * 0.02) + (+cFull * 0.05) + (+dFull * 0.1) + (+eFull * 0.2) + (+fFull * 0.5) + (+gFull) + (+hFull * 2);
          var total2Full = (+iFull * 5) + (+jFull * 10) + (+kFull * 20) + (+lFull * 50) + (+mFull * 100);
          var total3Full = (+aFull * 0.01) + (+bFull * 0.02) + (+cFull * 0.05) + (+dFull * 0.1) + (+eFull * 0.2) + (+fFull * 0.5) + (+gFull) + (+hFull * 2) + (+iFull * 5) + (+jFull * 10) + (+kFull * 20) + (+lFull * 50) + (+mFull * 100);
          $('#coinsTotFull').val(totalFull);
          $('#notesTotFull').val(total2Full);
          $('#tillTotFull').val(total3Full);

          var bnkcalc = total3Full - total3;
          
          $('#TotCounted').val(total3Full);
          $('#tillTotCounted').val(total3);
          $('#banked').val(bnkcalc);

        }
        
		$(document).on('click keypress keyup blur', function () {
		    compute();
		});
		
		$('#aboveValues').click(function(e) {
			
			$('#oneCent').val($('#oneCentFull').val());
			$('#twoCent').val($('#twoCentFull').val());
			$('#fiveCent').val($('#fiveCentFull').val());
			$('#tenCent').val($('#tenCentFull').val());
			$('#twentyCent').val($('#twentyCentFull').val());
			$('#fiftyCent').val($('#fiftyCentFull').val());
			$('#oneEuro').val($('#oneEuroFull').val());
			$('#twoEuro').val($('#twoEuroFull').val());
			$('#fiveEuro').val($('#fiveEuroFull').val());
			$('#tenEuro').val($('#tenEuroFull').val());
			$('#twentyEuro').val($('#twentyEuroFull').val());
			$('#fiftyEuro').val($('#fiftyEuroFull').val());
			$('#hundredEuro').val($('#hundredEuroFull').val());

  			e.preventDefault();
		});
		
  }); // end ready

        </script>
<br />        
<br />

<h1><?php echo $lang['all-cash']; ?></h1>
<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="close-shift-and-day-reception-2.php" method="POST">

<strong><?php echo $lang['coinsC']; ?></strong><br />
<input type="number" lang="nb" name="oneCentFull" id="oneCentFull" class="fourDigit" placeholder="1c" value="<?php echo $oneCentFull; ?>" />
<input type="number" lang="nb" name="twoCentFull" id="twoCentFull" class="fourDigit" placeholder="2c" value="<?php echo $twoCentFull; ?>" /> 
<input type="number" lang="nb" name="fiveCentFull" id="fiveCentFull" class="fourDigit" placeholder="5c" value="<?php echo $fiveCentFull; ?>" /> 
<input type="number" lang="nb" name="tenCentFull" id="tenCentFull" class="fourDigit" placeholder="10c" value="<?php echo $tenCentFull; ?>" /> 
<input type="number" lang="nb" name="twentyCentFull" id="twentyCentFull" class="fourDigit" placeholder="20c" value="<?php echo $twentyCentFull; ?>" /> 
<input type="number" lang="nb" name="fiftyCentFull" id="fiftyCentFull" class="fourDigit" placeholder="50c" value="<?php echo $fiftyCentFull; ?>" /> 
<input type="number" lang="nb" name="oneEuroFull" id="oneEuroFull" class="fourDigit" placeholder="1&euro;" value="<?php echo $oneEuroFull; ?>" /> 
<input type="number" lang="nb" name="twoEuroFull" id="twoEuroFull" class="fourDigit" placeholder="2&euro;" value="<?php echo $twoEuroFull; ?>" /><br /><br />
<strong><?php echo $lang['notesC']; ?></strong><br />
<input type="number" lang="nb" name="fiveEuroFull" id="fiveEuroFull" class="fourDigit" placeholder="5&euro;" value="<?php echo $fiveEuroFull; ?>" />
<input type="number" lang="nb" name="tenEuroFull" id="tenEuroFull" class="fourDigit" placeholder="10&euro;" value="<?php echo $tenEuroFull; ?>" /> 
<input type="number" lang="nb" name="twentyEuroFull" id="twentyEuroFull" class="fourDigit" placeholder="20&euro;" value="<?php echo $twentyEuroFull; ?>" /> 
<input type="number" lang="nb" name="fiftyEuroFull" id="fiftyEuroFull" class="fourDigit" placeholder="50&euro;" value="<?php echo $fiftyEuroFull; ?>" /> 
<input type="number" lang="nb" name="hundredEuroFull" id="hundredEuroFull" class="fourDigit" placeholder="100&euro;" value="<?php echo $hundredEuroFull; ?>" /> <br /><br />

<table>
 <tr>
  <td><?php echo $lang['coins']; ?>:</td>
  <td><input type="number" lang="nb" name="coinsTotFull" id="coinsTotFull" class="fourDigit" value="<?php echo $coinsTotFull; ?>" readonly /></td>
 </tr>
 <tr>
  <td><?php echo $lang['notes']; ?>:</td>
  <td><input type="number" lang="nb" name="notesTotFull" id="notesTotFull" class="fourDigit" value="<?php echo $notesTotFull; ?>" readonly /></td>
 </tr>
 <tr>
  <td><?php echo $lang['add-total']; ?>:</td>
  <td><input type="number" lang="nb" name="tillTotFull" id="tillTotFull" class="fourDigit" value="<?php echo $tillTotFull; ?>" readonly /></td>
 </tr>
</table>

<br /><br />

<h1><?php echo $lang['till-cash']; ?></h1>
<a href="#" class="cta" id="aboveValues" style="width: 220px; margin-top: -1px;"><?php echo $lang['use-above-values']; ?></a><br /><br />

<input type="hidden" name="step2" value="complete" />
<strong><?php echo $lang['coinsC']; ?></strong><br />
<input type="number" lang="nb" name="oneCent" id="oneCent" class="fourDigit" placeholder="1c" value="<?php echo $oneCent; ?>" />
<input type="number" lang="nb" name="twoCent" id="twoCent" class="fourDigit" placeholder="2c" value="<?php echo $twoCent; ?>" /> 
<input type="number" lang="nb" name="fiveCent" id="fiveCent" class="fourDigit" placeholder="5c" value="<?php echo $fiveCent; ?>" /> 
<input type="number" lang="nb" name="tenCent" id="tenCent" class="fourDigit" placeholder="10c" value="<?php echo $tenCent; ?>" /> 
<input type="number" lang="nb" name="twentyCent" id="twentyCent" class="fourDigit" placeholder="20c" value="<?php echo $twentyCent; ?>" /> 
<input type="number" lang="nb" name="fiftyCent" id="fiftyCent" class="fourDigit" placeholder="50c" value="<?php echo $fiftyCent; ?>" /> 
<input type="number" lang="nb" name="oneEuro" id="oneEuro" class="fourDigit" placeholder="1&euro;" value="<?php echo $oneEuro; ?>" /> 
<input type="number" lang="nb" name="twoEuro" id="twoEuro" class="fourDigit" placeholder="2&euro;" value="<?php echo $twoEuro; ?>" /><br /><br />
<strong><?php echo $lang['notesC']; ?></strong><br />
<input type="number" lang="nb" name="fiveEuro" id="fiveEuro" class="fourDigit" placeholder="5&euro;" value="<?php echo $fiveEuro; ?>" />
<input type="number" lang="nb" name="tenEuro" id="tenEuro" class="fourDigit" placeholder="10&euro;" value="<?php echo $tenEuro; ?>" /> 
<input type="number" lang="nb" name="twentyEuro" id="twentyEuro" class="fourDigit" placeholder="20&euro;" value="<?php echo $twentyEuro; ?>" /> 
<input type="number" lang="nb" name="fiftyEuro" id="fiftyEuro" class="fourDigit" placeholder="50&euro;" value="<?php echo $fiftyEuro; ?>" /> 
<input type="number" lang="nb" name="hundredEuro" id="hundredEuro" class="fourDigit" placeholder="100&euro;" value="<?php echo $hundredEuro; ?>" /> <br /><br />

<table>
 <tr>
  <td><?php echo $lang['coins']; ?>:</td>
  <td><input type="number" lang="nb" name="coinsTot" id="coinsTot" class="fourDigit" value="<?php echo $coinsTot; ?>" readonly /></td>
 </tr>
 <tr>
  <td><?php echo $lang['notes']; ?>:</td>
  <td><input type="number" lang="nb" name="notesTot" id="notesTot" class="fourDigit" value="<?php echo $notesTot; ?>" readonly /></td>
 </tr>
 <tr>
  <td><?php echo $lang['add-total']; ?>:</td>
  <td><input type="number" lang="nb" name="tillTot" id="tillTot" class="fourDigit" value="<?php echo $tillTot; ?>" readonly /></td>
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

