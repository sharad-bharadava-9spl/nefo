<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	$user_id = $_GET['user_id'];
	
	$chipcost = $_SESSION['chipcost'];
	
	if ($chipcost == 0) {
		
		header("Location: new-card.php?user_id=$user_id&fee=0");
		
	} else {
		
	pageStart($lang['new-chip-card'], NULL, $deleteNoteScript, "newchip", NULL, $lang['new-chip-card'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

		echo "<center><span class='ctalinks2'>
           <a class='cta' href='new-card.php?user_id=$user_id&fee=0'><br /><br />0 €</a>
           <a class='cta' href='new-card.php?user_id=$user_id&fee=$chipcost&bank=false'><br />$chipcost €<br /> {$lang['cash']}</a>";
           
       	if ($_SESSION['bankPayments'] == 1) {
           echo "<a class='cta' href='new-card.php?user_id=$user_id&fee=$chipcost&bank=true'><br />$chipcost €<br /> {$lang['bank-card']}</a>";
       	}
       	echo "
       	<a class='cta' href='new-card.php?user_id=$user_id&fee=$chipcost&bank=saldo'><br />$chipcost €<br /> Saldo</a>
           
           </span>
          </center>";
          
     }
          	

displayFooter();