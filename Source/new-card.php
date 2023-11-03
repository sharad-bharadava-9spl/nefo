<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if ($_POST['newchip'] == 'yes') {
		
		$newcard = $_POST['newcard'];
		$user_id = $_POST['user_id'];
		$price = $_POST['price'];
		
		$queryO = "UPDATE users SET cardid = '$newcard' WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$queryO")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		if ($_POST['saldo'] == 'true') {
			
			$query = "SELECT credit FROM users WHERE user_id = $user_id";
			try
			{
				$result = $pdo3->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$credit = $row['credit'];

			$newcredit = $credit - $price;
			
			$query = "UPDATE users SET credit = '$newcredit' WHERE user_id = $user_id";
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
		
		if ($_POST['staff'] == 'yes') {
			
			header("Location: new-password.php?user_id=" . $user_id);			
			
		} else {
			
			header("Location: profile.php?user_id=$user_id");
			
		}
			
		$_SESSION['successMessage'] = $lang['card-chip-added'];
		exit();
	
	}
	
	$scanner = $_SESSION['scanner'];
	
	if ($_SESSION['iPadReaders'] > 0) {
		
		try
		{
			$result = $pdo3->prepare("DELETE FROM newscan WHERE type = $scanner")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
	}

	
	if (isset($_POST['user_id'])) {
		$user_id = $_POST['user_id'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}
	
	$_SESSION['currUser'] = $user_id;
	
	$price = $_GET['fee'];
	
	
		if (isset($_GET['fee'])) {
		$insertTime = date('Y-m-d H:i:s');
		
		if ($_GET['bank'] == 'true') {
		
			$addFee = "INSERT INTO card_purchase (time, userid, amount, paidTo) VALUES ('$insertTime', $user_id, $price, 2)";
			
		} else if ($_GET['bank'] == 'saldo') {
		
			$addFee = "INSERT INTO card_purchase (time, userid, amount, paidTo) VALUES ('$insertTime', $user_id, $price, 3)";
			
		} else {
			
			$addFee = "INSERT INTO card_purchase (time, userid, amount, paidTo) VALUES ('$insertTime', $user_id, $price, 1)";
			
		}
		try
		{
			$result = $pdo3->prepare("$addFee")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
	}

if ($_SESSION['iPadReaders'] > 0) {
	
echo <<<EOD
<script>
setInterval(function()
{ 
    $.ajax({
      type:"post",
      url:"scansearch2.php",
      datatype:"text",
      success:function(data)
      {
        if( data == 'false' ) {

	    } else {
		    
	        window.location.replace("profile.php?cardadded&user_id="+data);
		    
	    }
      }
    });
}, 1000);
</script>
EOD;

}
	
	pageStart($lang['new-chip-card'], NULL, $deleteNoteScript, "pprofilenew", NULL, $lang['new-chip-card'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	if ($_GET['staff'] == 'true') {
		$staff = 'yes';
	} else {
		$staff = '';
	}

		echo <<<EOD
<center>
     <h2 style="font-size: 20px;">{$lang['scan-chip']}</h2>
     <br />
     <img src="images/tarjetascan.png" />
     <br /><br /><br />
     <form id="registerForm" action="" autocomplete="off" method="POST">
 <input type="text" name="newcard" class='defaultinput' style='width: 184px;' autofocus value="" /><br />
 <input type="hidden" name="newchip" value="yes" />
 <input type="hidden" name="user_id" value="$user_id" />
 <input type="hidden" name="price" value="$price" />
 <input type="hidden" name="staff" value="$staff" />
EOD;
if ($_GET['bank'] == 'saldo') {
	
		echo "<input type='hidden' name='saldo' value='true' />";
			
}
		echo <<<EOD

<button name='oneClick' type="submit" style="visibility: hidden;">{$lang['form-accept']}</button>
</form>
</center>
<center><a class='cta3' href='profile.php?user_id=$user_id'>{$lang['skip']}</a></center>
EOD;

displayFooter();