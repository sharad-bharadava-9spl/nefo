<?php
header('Cache-control: private'); // IE 6 FIX

	if (isset($_REQUEST['member_id'])) {
    		$selectLangMember = "SELECT language from members WHERE id = '".$_REQUEST['member_id']."'";  
			$lang_result = $pdo->prepare("$selectLangMember");
			$lang_result->execute();
			$lang_row = $lang_result->fetch();
			$lang = $lang_row['language'];
	} else {
		$lang = 'en';
	}

	switch ($lang) {
	  case 'en':
	  $lang_file = 'english.php';
	   //echo "<pre>";print_r($lang_file);exit;
	  break;
	 
	  case 'es':
	  $lang_file = 'spanish.php';
	  break;
	 
	  default:
	  $lang_file = 'english.php';
	 
	}
 	 //echo "<pre>";print_r(HOST_ROOT . 'cOnfig/languages/' . $lang_file);exit;
	include_once HOST_ROOT . '/api/language/' . $lang_file;
?>