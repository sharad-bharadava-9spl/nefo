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
	
	if ($_POST['step1'] != 'complete' && !isset($_POST['tillConfirm'])) {
		echo $lang['global-onenotcomplete'];
		exit();
	}
	
	// Is step 1 complete -- or has a recount been done?
	if ($_POST['step1'] == 'complete' || $_POST['tillConfirm'] == 'yes') {
		$oneCent = $_POST['oneCent'];
		$twoCent = $_POST['twoCent'];
		$fiveCent = $_POST['fiveCent'];
		$tenCent = $_POST['tenCent'];
		$twentyCent = $_POST['twentyCent'];
		$fiftyCent = $_POST['fiftyCent'];
		$oneEuro = $_POST['oneEuro'];
		$twoEuro = $_POST['twoEuro'];
		$fiveEuro = $_POST['fiveEuro'];
		$tenEuro = $_POST['tenEuro'];
		$twentyEuro = $_POST['twentyEuro'];
		$fiftyEuro = $_POST['fiftyEuro'];
		$hundredEuro = $_POST['hundredEuro'];
		$coinsTot = $_POST['coinsTot'];
		$notesTot = $_POST['notesTot'];
		$tillTot = $_POST['tillTot'];
		$amountOwed = $_POST['amountOwed'];
		$tillComment = $_POST['tillComment'];


		$openCount = array("oneCent"=>"$oneCent", "twoCent"=>"$twoCent", "fiveCent"=>"$fiveCent", "tenCent"=>"$tenCent", "twentyCent"=>"$twentyCent", "fiftyCent"=>"$fiftyCent", "oneEuro"=>"$oneEuro", "twoEuro"=>"$twoEuro", "fiveEuro"=>"$fiveEuro", "tenEuro"=>"$tenEuro", "twentyEuro"=>"$twentyEuro", "fiftyEuro"=>"$fiftyEuro", "hundredEuro"=>"$hundredEuro", "coinsTot"=>"$coinsTot", "notesTot"=>"$notesTot", "tillTot"=>"$tillTot", "tillDelta"=>"$tillDelta", "tillComment"=>"$tillComment");
		
		$_SESSION['openCount'] = $openCount;
	}
	
	// Query to look up previous closing balance
	$closingLookup = "SELECT oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, cashintill, bankBalance FROM shiftclose ORDER BY closingtime DESC LIMIT 1";
		

	$result = mysql_query($closingLookup)
		or handleError($lang['error-closingload'],"Error loading closing from db: " . mysql_error());

	// Retrieve yesterdays closing data
	$row = mysql_fetch_array($result);
		$oneCentYday = $row['oneCent'];
		$twoCentYday = $row['twoCent'];
		$fiveCentYday = $row['fiveCent'];
		$tenCentYday = $row['tenCent'];
		$twentyCentYday = $row['twentyCent'];
		$fiftyCentYday = $row['fiftyCent'];
		$oneEuroYday = $row['oneEuro'];
		$twoEuroYday = $row['twoEuro'];
		$fiveEuroYday = $row['fiveEuro'];
		$tenEuroYday = $row['tenEuro'];
		$twentyEuroYday = $row['twentyEuro'];
		$fiftyEuroYday = $row['fiftyEuro'];
		$hundredEuroYday = $row['hundredEuro'];
		$coinsTotYday = $row['coinsTot'];
		$notesTotYday = $row['notesTot'];
		$cashintillYday = $row['cashintill'];	
		$bankBalance = $row['bankBalance'];	
		$_SESSION['bankBalance'] =  $bankBalance;

			
	// Does yesterday closing match today's count? Or has a recount been done?
	if ($cashintillYday == $tillTot || $_POST['tillConfirm'] == 'yes' || $_SESSION['noCompare'] == 'true') {
	
		$tillDelta = $tillTot - $cashintillYday;
		
		$_SESSION['cashintillYday'] = $cashintillYday;
		$_SESSION['tillDelta'] = $tillDelta;
		$_SESSION['tillComment'] = $tillComment;
		
	$confirmLeave = <<<EOD
    $(document).ready(function() {   	    
document.querySelector('button').addEventListener("click", function(){
    window.btn_clicked = true;      //set btn_clicked to true
});
$(window).bind('beforeunload', function(){
    if(!window.btn_clicked){
        return 'Are you sure you want to leave this page?';
    }
});
  }); // end ready
EOD;

	pageStart($lang['start-shift'], NULL, $confirmLeave, "pcloseday", "step1", $lang['openday-rec-three'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
?>
		
		<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="open-shift-reception-2.php" method="POST"><br />
		 <table>
		  <tr>
		   <td><?php echo $lang['till-last-close']; ?>:</td>
		   <td><input type="number" lang="nb" name="cashintillYday" id="cashintillYday" class="fourDigit" value="<?php echo $cashintillYday; ?>" readonly /></td>
		  </tr>
		  <tr>
		   <td><?php echo $lang['tillnow']; ?>:</td>
		   <td><strong><input type="number" lang="nb" name="tillTot" id="tillTot" class="fourDigit" value="<?php echo $tillTot; ?>" readonly /></strong></td>
		  </tr>
		 </table>
		 <input type='hidden' name='oneCent' value='<?php echo $oneCent; ?>' />
		 <input type='hidden' name='twoCent' value='<?php echo $twoCent; ?>' />
		 <input type='hidden' name='fiveCent' value='<?php echo $fiveCent; ?>' />
		 <input type='hidden' name='tenCent' value='<?php echo $tenCent; ?>' />
		 <input type='hidden' name='twentyCent' value='<?php echo $twentyCent; ?>' />
		 <input type='hidden' name='fiftyCent' value='<?php echo $fiftyCent; ?>' />
		 <input type='hidden' name='oneEuro' value='<?php echo $oneEuro; ?>' />
		 <input type='hidden' name='twoEuro' value='<?php echo $twoEuro; ?>' />
		 <input type='hidden' name='fiveEuro' value='<?php echo $fiveEuro; ?>' />
		 <input type='hidden' name='tenEuro' value='<?php echo $tenEuro; ?>' />
		 <input type='hidden' name='twentyEuro' value='<?php echo $twentyEuro; ?>' />
		 <input type='hidden' name='fiftyEuro' value='<?php echo $fiftyEuro; ?>' />
		 <input type='hidden' name='hundredEuro' value='<?php echo $hundredEuro; ?>' />
		 <input type='hidden' name='coinsTot' value='<?php echo $coinsTot; ?>' />
		 <input type='hidden' name='notesTot' value='<?php echo $notesTot; ?>' />
		 <input type='hidden' name='tillTot' value='<?php echo $tillTot; ?>' />
		 <input type='hidden' name='step2' value='complete' /><br />
 		 <button name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button>
 		 </form>

<?php  
	displayFooter();
	
//if it does not match?
	} else {
		
		$testinput = <<<EOD
    $(document).ready(function() { 	    
document.querySelector('button').addEventListener("click", function(){
    window.btn_clicked = true;      //set btn_clicked to true
});

$(window).bind('beforeunload', function(){
    if(!window.btn_clicked){
        return 'Are you sure you want to leave this page?';
    }
});
  }); // end ready

	function checkMismatch() {

		var yday = Number(document.getElementById("cashintillYday").value);
		var tday = Number(document.getElementById("tillTot").value);
		
		if (yday == tday) {
    		return true;
		} else {
			var r = confirm("{$lang['mismatchconfirm']}");
			if (r == true) {
	    		return true;
			} else {
    			return false;
			} 
		}
		

	}
EOD;

			pageStart($lang['title-openday'], NULL, $testinput, "pcloseday", "step2", $lang['openday-rec-three'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

   <script>
    $(document).ready(function() {
	    
	    
          var a = $('#oneCent').val();
          if (a == '') {
	          a = 0;
	          $('#oneCent').val(a);
          }
          var b = $('#oneCentYday').val();
          var c = $('#twoCent').val();
          if (c == '') {
	          c = 0;
	          $('#twoCent').val(c);
          }
          var d = $('#twoCentYday').val();
          var e = $('#fiveCent').val();
          if (e == '') {
	          e = 0;
	          $('#fiveCent').val(e);
          }
          var f = $('#fiveCentYday').val();
          var g = $('#tenCent').val();
          if (g == '') {
	          g = 0;
	          $('#tenCent').val(g);
          }
          var h = $('#tenCentYday').val();
          var i = $('#twentyCent').val();
          if (i == '') {
	          i = 0;
	          $('#twentyCent').val(i);
          }
          var j = $('#twentyCentYday').val();
          var k = $('#fiftyCent').val();
          if (k == '') {
	          k = 0;
	          $('#fiftyCent').val(k);
          }
          var l = $('#fiftyCentYday').val();
          var m = $('#oneEuro').val();
          if (m == '') {
	          m = 0;
	          $('#oneEuro').val(m);
          }
          var n = $('#oneEuroYday').val();
          var o = $('#twoEuro').val();
          if (o == '') {
	          o = 0;
	          $('#twoEuro').val(o);
          }
          var p = $('#twoEuroYday').val();
          var q = $('#fiveEuro').val();
          if (q == '') {
	          q = 0;
	          $('#fiveEuro').val(q);
          }
          var r = $('#fiveEuroYday').val();
          var s = $('#tenEuro').val();
          if (s == '') {
	          s = 0;
	          $('#tenEuro').val(s);
          }
          var t = $('#tenEuroYday').val();
          var u = $('#twentyEuro').val();
          if (u == '') {
	          u = 0;
	          $('#twentyEuro').val(u);
          }
          var v = $('#twentyEuroYday').val();
          var w = $('#fiftyEuro').val();
          if (w == '') {
	          w = 0;
	          $('#fiftyEuro').val(w);
          }
          var x = $('#fiftyEuroYday').val();
          var y = $('#hundredEuro').val();
          if (y == '') {
	          y = 0;
	          $('#hundredEuro').val(y);
          }
          var z = $('#hundredEuroYday').val();
          var aa = Number($('#coinsTot').val());
          if (aa == '') {
	          aa = 0;
	          Number($('#coinsTot').val(aa));
          }
          var ab = Number($('#coinsTotYday').val());
          var ac = Number($('#notesTot').val());
          if (ac == '') {
	          ac = 0;
	          Number($('#notesTot').val(ac));
          }
          var ad = Number($('#notesTotYday').val());
          var ae = Number($('#tillTot').val());
          if (ae == '') {
	          ae = 0;
	          Number($('#tillTot').val(ae));
          }
          var af = Number($('#cashintillYday').val());
          
          
          if (a != b)
          {
 			$('#oneCent').css('background-color', 'red');
			};
          if (c != d)
          {
 			$('#twoCent').css('background-color', 'red');
			};
          if (e != f)
          {
 			$('#fiveCent').css('background-color', 'red');
			};
          if (g != h)
          {
 			$('#tenCent').css('background-color', 'red');
			};
          if (i != j)
          {
 			$('#twentyCent').css('background-color', 'red');
			};
          if (k != l)
          {
 			$('#fiftyCent').css('background-color', 'red');
			};
          if (m != n)
          {
 			$('#oneEuro').css('background-color', 'red');
			};
          if (o != p)
          {
 			$('#twoEuro').css('background-color', 'red');
			};
          if (q != r)
          {
 			$('#fiveEuro').css('background-color', 'red');
			};
          if (s != t)
          {
 			$('#tenEuro').css('background-color', 'red');
			};
          if (u != v)
          {
 			$('#twentyEuro').css('background-color', 'red');
			};
          if (w != x)
          {
 			$('#fiftyEuro').css('background-color', 'red');
			};
          if (y != z)
          {
 			$('#hundredEuro').css('background-color', 'red');
			};
          if (aa != ab)
          {
 			$('#coinsTot').css('background-color', 'red');
			};
          if (ac != ad)
          {
 			$('#notesTot').css('background-color', 'red');
			};
			
          if (ae != af)
          {
 			$('#tillTot').css('background-color', 'red');
			};
			
	    
	    
	    
	    function compareYday() {

          var a = $('#oneCent').val();
          var b = $('#oneCentYday').val();
          var c = $('#twoCent').val();
          var d = $('#twoCentYday').val();
          var e = $('#fiveCent').val();
          var f = $('#fiveCentYday').val();
          var g = $('#tenCent').val();
          var h = $('#tenCentYday').val();
          var i = $('#twentyCent').val();
          var j = $('#twentyCentYday').val();
          var k = $('#fiftyCent').val();
          var l = $('#fiftyCentYday').val();
          var m = $('#oneEuro').val();
          var n = $('#oneEuroYday').val();
          var o = $('#twoEuro').val();
          var p = $('#twoEuroYday').val();
          var q = $('#fiveEuro').val();
          var r = $('#fiveEuroYday').val();
          var s = $('#tenEuro').val();
          var t = $('#tenEuroYday').val();
          var u = $('#twentyEuro').val();
          var v = $('#twentyEuroYday').val();
          var w = $('#fiftyEuro').val();
          var x = $('#fiftyEuroYday').val();
          var y = $('#hundredEuro').val();
          var z = $('#hundredEuroYday').val();
          var aa = Number($('#coinsTot').val());
          var ab = Number($('#coinsTotYday').val());
          var ac = Number($('#notesTot').val());
          var ad = Number($('#notesTotYday').val());
          var ae = Number($('#tillTot').val());
          var af = Number($('#cashintillYday').val());
          
          if (a != b)
          {
 			$('#oneCent').css('background-color', 'red');
			} else {
 			$('#oneCent').css('background-color', '');
			};
			
          if (c != d)
          {
 			$('#twoCent').css('background-color', 'red');
			} else {
 			$('#twoCent').css('background-color', '');
			};
			
          if (e != f)
          {
 			$('#fiveCent').css('background-color', 'red');
			} else {
 			$('#fiveCent').css('background-color', '');
			};
			
          if (g != h)
          {
 			$('#tenCent').css('background-color', 'red');
			} else {
 			$('#tenCent').css('background-color', '');
			};
			
          if (i != j)
          {
 			$('#twentyCent').css('background-color', 'red');
			} else {
 			$('#twentyCent').css('background-color', '');
			};
			
          if (k != l)
          {
 			$('#fiftyCent').css('background-color', 'red');
			} else {
 			$('#fiftyCent').css('background-color', '');
			};
			
          if (m != n)
          {
 			$('#oneEuro').css('background-color', 'red');
			} else {
 			$('#oneEuro').css('background-color', '');
			};
			
          if (o != p)
          {
 			$('#twoEuro').css('background-color', 'red');
			} else {
 			$('#twoEuro').css('background-color', '');
			};
						
          if (q != r)
          {
 			$('#fiveEuro').css('background-color', 'red');
			} else {
 			$('#fiveEuro').css('background-color', '');
			};
			
          if (s != t)
          {
 			$('#tenEuro').css('background-color', 'red');
			} else {
 			$('#tenEuro').css('background-color', '');
			};
			
          if (u != v)
          {
 			$('#twentyEuro').css('background-color', 'red');
			} else {
 			$('#twentyEuro').css('background-color', '');
			};
			
          if (w != x)
          {
 			$('#fiftyEuro').css('background-color', 'red');
			} else {
 			$('#fiftyEuro').css('background-color', '');
			};
			
          if (y != z)
          {
 			$('#hundredEuro').css('background-color', 'red');
			} else {
 			$('#hundredEuro').css('background-color', '');
			};
			
          if (aa != ab)
          {
 			$('#coinsTot').css('background-color', 'red');
			} else {
 			$('#coinsTot').css('background-color', '');
			};
			
          if (ac != ad)
          {
 			$('#notesTot').css('background-color', 'red');
			} else {
 			$('#notesTot').css('background-color', '');
			};
			
          if (ae != af)
          {
 			$('#tillTot').css('background-color', 'red');
			} else {
 			$('#tillTot').css('background-color', '');
			};
			
		}
			
			
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
          $('#coinsTot').val(total.toFixed(2));
          $('#notesTot').val(total2.toFixed(2));
          $('#tillTot').val(total3.toFixed(2));
        }
        
$(document).on('click keypress keyup blur', function () {
    compute();
    compareYday();
});


  }); // end ready

        </script>

		<br />
		<span class='negative biggerfont'>
		 <strong><?php echo $lang['tillmismatch']; ?></strong>
		</span><br />
		<?php echo $lang['countagain']; ?>...<br />
		<form id="registerForm" action="" method="POST" onsubmit="return checkMismatch()"><br />
		 <strong><?php echo $lang['closeday-coins']; ?></strong><br />
		 <span class="fakelabel short"><?php echo $lang['last-close']; ?>:</span>
		 <input type="number" lang="nb" name="oneCentYday" id="oneCentYday" class="fourDigit" value="<?php echo $oneCentYday; ?>" readonly />
		 <input type="number" lang="nb" name="twoCentYday" id="twoCentYday" class="fourDigit" value="<?php echo $twoCentYday; ?>" readonly /> 
		 <input type="number" lang="nb" name="fiveCentYday" id="fiveCentYday" class="fourDigit" value="<?php echo $fiveCentYday; ?>" readonly /> 
		 <input type="number" lang="nb" name="tenCentYday" id="tenCentYday" class="fourDigit" value="<?php echo $tenCentYday; ?>" readonly /> 
		 <input type="number" lang="nb" name="twentyCentYday" id="twentyCentYday" class="fourDigit" value="<?php echo $twentyCentYday; ?>" readonly /> 
		 <input type="number" lang="nb" name="fiftyCentYday" id="fiftyCentYday" class="fourDigit" value="<?php echo $fiftyCentYday; ?>" readonly /> 
		 <input type="number" lang="nb" name="oneEuroYday" id="oneEuroYday" class="fourDigit" value="<?php echo $oneEuroYday; ?>" readonly /> 
		 <input type="number" lang="nb" name="twoEuroYday" id="twoEuroYday" class="fourDigit" value="<?php echo $twoEuroYday; ?>" readonly />
		 <strong><input type="number" lang="nb" name="coinsTotYday" id="coinsTotYday" class="fourDigit" value="<?php echo $coinsTotYday; ?>" readonly /></strong><br />
		 <span class="fakelabel short"><?php echo $lang['now']; ?>:</span>
		 <input type="number" lang="nb" name="oneCent" id="oneCent" class="fourDigit" value="<?php echo $oneCent; ?>" />
		 <input type="number" lang="nb" name="twoCent" id="twoCent" class="fourDigit" value="<?php echo $twoCent; ?>" /> 
		 <input type="number" lang="nb" name="fiveCent" id="fiveCent" class="fourDigit" value="<?php echo $fiveCent; ?>" /> 
		 <input type="number" lang="nb" name="tenCent" id="tenCent" class="fourDigit" value="<?php echo $tenCent; ?>" /> 
		 <input type="number" lang="nb" name="twentyCent" id="twentyCent" class="fourDigit" value="<?php echo $twentyCent; ?>" /> 
		 <input type="number" lang="nb" name="fiftyCent" id="fiftyCent" class="fourDigit" value="<?php echo $fiftyCent; ?>" /> 
		 <input type="number" lang="nb" name="oneEuro" id="oneEuro" class="fourDigit" value="<?php echo $oneEuro; ?>" /> 
		 <input type="number" lang="nb" name="twoEuro" id="twoEuro" class="fourDigit" value="<?php echo $twoEuro; ?>" />
		 <strong><input type="number" lang="nb" name="coinsTot" id="coinsTot" class="fourDigit" value="<?php echo $coinsTot; ?>" readonly /></strong><br /><br />
		 <strong><?php echo $lang['closeday-notes']; ?></strong><br />
		 <span class="fakelabel short"><?php echo $lang['last-close']; ?>:</span>
		 <input type="number" lang="nb" name="fiveEuroYday" id="fiveEuroYday" class="fourDigit" value="<?php echo $fiveEuroYday; ?>" readonly />
		 <input type="number" lang="nb" name="tenEuroYday" id="tenEuroYday" class="fourDigit" value="<?php echo $tenEuroYday; ?>" readonly /> 
		 <input type="number" lang="nb" name="twentyEuroYday" id="twentyEuroYday" class="fourDigit" value="<?php echo $twentyEuroYday; ?>" readonly /> 
		 <input type="number" lang="nb" name="fiftyEuroYday" id="fiftyEuroYday" class="fourDigit" value="<?php echo $fiftyEuroYday; ?>" readonly /> 
		 <input type="number" lang="nb" name="hundredEuroYday" id="hundredEuroYday" class="fourDigit" value="<?php echo $hundredEuroYday; ?>" readonly />
		 <strong><input type="number" lang="nb" name="notesTotYday" id="notesTotYday" class="fourDigit" value="<?php echo $notesTotYday; ?>" readonly /></strong><br />
		 <span class="fakelabel short"><?php echo $lang['now']; ?>:</span>
		 <input type="number" lang="nb" name="fiveEuro" id="fiveEuro" class="fourDigit" value="<?php echo $fiveEuro; ?>" />
		 <input type="number" lang="nb" name="tenEuro" id="tenEuro" class="fourDigit" value="<?php echo $tenEuro; ?>" /> 
		 <input type="number" lang="nb" name="twentyEuro" id="twentyEuro" class="fourDigit" value="<?php echo $twentyEuro; ?>" /> 
		 <input type="number" lang="nb" name="fiftyEuro" id="fiftyEuro" class="fourDigit" value="<?php echo $fiftyEuro; ?>" /> 
		 <input type="number" lang="nb" name="hundredEuro" id="hundredEuro" class="fourDigit" value="<?php echo $hundredEuro; ?>" />
		 <strong><input type="number" lang="nb" name="notesTot" id="notesTot" class="fourDigit" value="<?php echo $notesTot; ?>" readonly /></strong><br /><br />
		 
		 <table>
		  <tr>
		   <td><?php echo $lang['till-last-close']; ?>:</td>
		   <td><input type="number" lang="nb" name="cashintillYday" id="cashintillYday" class="fourDigit" value="<?php echo $cashintillYday; ?>" readonly /></td>
		  </tr>
		  <tr>
		   <td><?php echo $lang['tillbalnow']; ?>:</td>
		   <td><input type="number" lang="nb" name="tillTot" id="tillTot" class="fourDigit" value="<?php echo $tillTot; ?>" readonly /></td>
		  </tr>
		 </table><br />
		 <?php echo $lang['closeday-tillcomment']; ?>:<br />
		 <textarea name='tillComment' id="tillComment"></textarea>
		 <br />
		 <input type="hidden" name="tillConfirm" value="yes" />
 		 <button name='oneClick' type="submit"><?php echo $lang['confirmtillbal']; ?></button>
</form>



<?php }
	displayFooter();