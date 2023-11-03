<?php
	
	if (isset($_GET['genpdf'])) {

		/*
		session_start();
		require 'pdfcrowd.php';
	
		$html = $_SESSION['contr1'];
	    // echo $html; exit();
	    // create the API client instance
	    $client = new \Pdfcrowd\HtmlToPdfClient("Berrern", "f9ad67996030f478ade7c6295a0b533d");
	
	    $pdf = $client->convertString($html);
	    
	    header('Content-Type: application/pdf');
	    header('Cache-Control: no-cache');
	    header('Accept-Ranges: none');
	    header("Content-Disposition: attachment; filename=\"contrato.pdf\"");
	    // return the final PDF in the response
	    echo $pdf;
	    exit();*/
	    
	}
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];
	getSettings();

	
	// Get the user ID
	if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}

	// Query to look up user
	$userDetails = "SELECT memberno, registeredSince, first_name, last_name, email, day, month, year, nationality, gender, dni, street, streetnumber, flat, postcode, city, country, telephone, mconsumption, usageType, signupsource, cardid, photoid, docid, doorAccess, friend, friend2, paidUntil, adminComment, registeredSince, photoext, dniext1, dniext2, sigext FROM users WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user1: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$memberno = $row['memberno'];
		$registeredSince = $row['registeredSince'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		$email = $row['email'];
		$telephone = $row['telephone'];
		$day = $row['day'];
		$month = $row['month'];
		$year = $row['year'];
		$nationality = $row['nationality'];
		$dni = $row['dni'];
		$mconsumption = $row['mconsumption'];
		$usageType = $row['usageType'];
		$signupsource = $row['signupsource'];
		$cardid = $row['cardid'];
		$photoid = $row['photoid'];
		$docid = $row['docid'];
		$doorAccess = $row['doorAccess'];
		$photoext = $row['photoext'];
		$dniext1 = $row['dniext1'];
		$dniext2 = $row['dniext2'];
		$friend = $row['friend'];
		$street = $row['street'];
		$streetnumber = $row['streetnumber'];
		$flat = $row['flat'];
		$postcode = $row['postcode'];
		$city = $row['city'];
		$country = $row['country'];
		$sigext = $row['sigext'];
		
$insDate = date("d/m/Y H:i", strtotime($registeredSince . "+$offsetSec seconds"));
$regDate = date("d/m/Y", strtotime($registeredSince . "+$offsetSec seconds"));

		if ($sigext == '') {
			$sigext = 'png';
		}
		
		if ($friend != '') {
			$userDetails = "SELECT sigext FROM users WHERE user_id = $friend";
			try
			{
				$result = $pdo3->prepare("$userDetails");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user2: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$sigext2 = $row['sigext'];
				if ($sigext2 == '') {
					$sigext2 = 'png';
				}
		}

		
		
		
		// Query to look up user
		$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = '$friend'";
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$avalNo = $row['memberno'];
			$avalFN = $row['first_name'];
			$avalLN = $row['last_name'];
			
	
		if ($usageType == '1') {
			
			$userTipo = "Terapéutico";
			
		} else {
			
			$userTipo = "Lúdico";
			
		}
	
	pageStart($lang['member-contract'], NULL, $deleteNoteScript, "pprofile", "statutes", $lang['member-contract'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	$fileImg = "images/_$domain/members/" . $user_id . '.' . $photoext;

?>

<center><a href='pdfgen.php' class='cta1'>Generar PDF</a></center>
<?php
	if (file_exists($fileImg)) {
		$imginset = "<center><img class='profilepic' src='$fileImg'  width='320' /></center>";
	}
?>
 <div id='mainbox'>
  <div id='mainboxheader'>
  Solicitud admisiÃ³n soci@ libre consumidor@
  </div>
  <div id='contractholder'>

<?php echo <<<EOD
<center>
 <table>
  <tr>
   <td style='vertical-align: top;'>
$imginset
 </td>
 <td>

 <table class='contract'>
  <tr>
   <td><strong>NOMBRE COMPLETO:</strong></td>
   <td>{$first_name} {$last_name}</td>
  </tr>
  <tr>
   <td><strong>DNI/NIE:</strong></td>
   <td>{$dni}</td>
  </tr>
  <tr>
   <td><strong>FECHA DE INSCRIPCIÓN:</strong></td>
   <td>{$regDate}</td>
  </tr>
  <tr>
   <td><strong>DOMICILIO:</strong></td>
   <td>{$street} {$streetnumber} {$flat}, {$postcode} {$city}, {$country} </td>
  </tr>
  <tr>
   <td><strong>FECHA DE NACIMIENTO:</strong></td>
   <td>{$day}/{$month}/{$year}</td>
  </tr>
  <tr>
   <td><strong>EMAIL:</strong></td>
   <td>{$email}</td>
  </tr>
  <tr>
   <td><strong>TELEFONO:</strong></td>
   <td>{$telephone}</td>
  </tr>
  <tr>
   <td><strong>N<sup>o</sup> SOCIO:</strong></td>
   <td>{$memberno}</td>
  </tr>
  <tr>
   <td><strong>PREVISION DE CONSUMO MENSUAL:</strong></td>
   <td>{$mconsumption} gr</td>
  </tr>
  <tr>
   <td><strong>TIPO DE SOCIO:</strong></td>
   <td>{$userTipo}</td>
  </tr>
 </table>
 </td>
 </tr>
 </table>
 </center>
EOD;
?>
<br />
<style>
p {
	margin: 20px;
}
ol li {
	margin-top: 10px;
	padding-left: 10px;
	line-height: 1.5em;
}
</style>

  <hr style='width: 95%' />
<?php
	$file = "_club/_$domain/contract.php";
	
	if (file_exists($file)) {
		include $file;
	}
	
	$fileImg1 = "images/_$domain/sigs/" . $user_id . '.' . $sigext;
	$fileImg2 = "images/_$domain/sigs/" . $friend . '.' . $sigext2;

?>



<br />
<center>
<table width="90%" align="center">
 <tr>
  <td width="40%" align="center">FIRMA DEL AVALISTA:</td>
  <td width="40%" align="center">FIRMA DEL INTERESADO:</td>
 </tr>
 <tr>
  <td width="40%" align="center">
<?php
	if (file_exists($fileImg2)) {
		echo <<<EOD
<img src="$fileImg2" width="300" />
EOD;
}
?>
</td>
  <td width="40%" align="center">
<?php
	if (file_exists($fileImg1)) {
		echo <<<EOD
<img src="$fileImg1" width="300" />
EOD;
}
?>
</td>
 </tr>
</table>
</div></div>
</center>
</form>

<?php

// Create all HTML manually, i.e. no View!




$_SESSION['contr1'] = <<<EOD
<html>
<head>
<title></title>
</head>
<body>
<form>
<table width="100%" align="center">
EOD;


	
 $_SESSION['contr1'] .= <<<EOD
</table>
<center>
 <table>
  <tr>
   <td style='vertical-align: top;'>
$imginset
 </td>
 <td>

 <table class='contract'>
  <tr>
   <td><strong>NOMBRE COMPLETO:</strong></td>
   <td>{$first_name} {$last_name}</td>
  </tr>
  <tr>
   <td><strong>DNI/NIE:</strong></td>
   <td>{$dni}</td>
  </tr>
  <tr>
   <td><strong>FECHA DE INSCRIPCIÓN:</strong></td>
   <td>{$regDate}</td>
  </tr>
  <tr>
   <td><strong>DOMICILIO:</strong></td>
   <td>{$street} {$streetnumber} {$flat}, {$postcode} {$city}, {$country} </td>
  </tr>
  <tr>
   <td><strong>FECHA DE NACIMIENTO:</strong></td>
   <td>{$day}/{$month}/{$year}</td>
  </tr>
  <tr>
   <td><strong>EMAIL:</strong></td>
   <td>{$email}</td>
  </tr>
  <tr>
   <td><strong>TELEFONO:</strong></td>
   <td>{$telephone}</td>
  </tr>
  <tr>
   <td><strong>N<sup>o</sup> SOCIO:</strong></td>
   <td>{$memberno}</td>
  </tr>
  <tr>
   <td><strong>PREVISION DE CONSUMO MENSUAL:</strong></td>
   <td>{$mconsumption} gr</td>
  </tr>
  <tr>
   <td><strong>TIPO DE SOCIO:</strong></td>
   <td>{$userTipo}</td>
  </tr>
 </table>
 </td>
 </tr>
 </table>
 </center>

<br />
<hr style='width: 95%' />



EOD;

	$file = "_club/_$domain/contract.php";
	
	if (file_exists($file)) {
		ob_start();
		include "$file";
		$_SESSION['contr1'] .= ob_get_clean();
		//$_SESSION['contr1'] .= file_get_contents($file);
	}
	
	
$_SESSION['contr1'] .= <<<EOD

<br />
<table width="90%" align="center">
 <tr>
  <td width="50%" align="center">FIRMA DEL AVALISTA:</td>
  <td width="50%" align="center">FIRMA DEL INTERESADO:</td>
 </tr>
EOD;

			
 $_SESSION['contr1'] .= <<<EOD
 <tr>
  <td width="50%" align="center">
EOD;
	if (file_exists($fileImg2)) {
		$_SESSION['contr1'] .= <<<EOD
<img src="$fileImg2" width="400" />
EOD;
}
$_SESSION['contr1'] .= <<<EOD
</td>
  <td width="50%" align="center">
EOD;
	if (file_exists($fileImg1)) {
		$_SESSION['contr1'] .= <<<EOD
<img src="$fileImg1" width="400" />
EOD;
}
$_SESSION['contr1'] .= <<<EOD
</td>
 </tr>
EOD;
	
 $_SESSION['contr1'] .= <<<EOD
</table>
</center>
</form>
</body>
</html>
EOD;


 displayFooter();
