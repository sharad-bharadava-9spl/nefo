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
	
	$domain = $_SESSION['domain'];


	$selectAccess = "SELECT default_access FROM systemsettings";

      try
      {
        $access_result = $pdo3->prepare("$selectAccess");
        $access_result->execute();
      }
      catch (PDOException $e)
      {
          $error = 'Error fetching user: ' . $e->getMessage();
          echo $error;
          exit();
      }

      $result_access = $access_result->fetch();

      $default_access = $result_access['default_access'];
     


   function searchForKey($link, $array) {
   foreach ($array as $key => $val) {
       if ($val['page_link'] === $link) {
           return $key;
       }
   }
   return null;
}
		
	$deleteDonationScript = <<<EOD
		
function delete_donation(donationid,amount,userid) {
	if (confirm("Are you sure? You will lose ALL ACCESS to your system!")) {
				window.location = "delete-access.php?abyT=223";
				}
}
EOD;

$shoplinks = <<<EOD
<style>
.shopbox {
	display: inline;
	vertical-align: top;
	float: right;
	margin-top: -6px;
}
.shopform {
	display: inline-block;
	cursor: pointer;
}
.shopbutton {
	border-radius: 3px;
	text-align: center;
	line-height: 1.4em;
	color: #a80082;
	font-size: 14px;
	font-weight: 800;
	cursor: pointer;
	border: 0;
	background: transparent;
}
</style>
<div class="shopbox">
<form id="highroller" class='shopform' action="/HighRoller/index.php" method="POST">
<input type="hidden" name="domain" value="$domain" />
<button type="submit" class='shopbutton'>
<center><img src="images/shop-icon.png" /></center>
HIGH ROLLER
</button>
</form>
<form id="highroller" class='shopform' action="/CCS/index.php" method="POST">
<input type="hidden" name="domain" value="$domain" />
<button type="submit" class='shopbutton'>
<center><img src="images/shop-icon.png" /></center>
CCS SHOP
</button>
</form>
</div>
</center>
EOD;	
	pageStart($lang['title-administration'], NULL, $deleteDonationScript, "padmin", "index", $lang['global-administration'] . $shoplinks, $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$userLvl = $_SESSION['userGroup'];
	$domain = $_SESSION['domain'];
	

?>
<div id="adminboxHolder">

<?php
if($default_access == 0){
	$file = "_club/_$domain/admin.php";
	
	if (!file_exists($file)) {
		include "_club/admin.php";
	} else {
		include $file;
	}
}else{
	include "_club/admin.php";
}	
	
?>


</div>

<?php

 displayFooter();
