<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['day'])) {
		
		// Get minimum age from system settings
		$ageCheck = "SELECT minAge FROM systemsettings";
		
		$aC = mysql_query($ageCheck);
		
		$row = mysql_fetch_array($aC);
			$minAge = $row['minAge'];
		
		$bdayraw = $_POST['year'] . "-" . $_POST['month'] . "-" . $_POST['day'];
		
		$age = date_diff(date_create($bdayraw), date_create('today'))->y;
		
		if ($age < $minAge) {
			pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes", $lang['member-newmembercaps'] . " - Statutes", $_SESSION['successMessage'], $lang['too-young'] . $minAge . ".");
			exit();
		} else {
		
			$tempNo = $_SESSION['tempNo'];
	
			$encoded_data = $_POST['sigImageData'];
			$binary_data = base64_decode( $encoded_data );
			
			$imgname = 'images/sigs/' . $tempNo . '.png';
			
			// save to server (beware of permissions)
			$result = file_put_contents( $imgname, $binary_data );

			$_SESSION['consumoPrevio'] = $_POST['consumoPrevio'];
			$_SESSION['memberType'] = $_POST['memberType'];
			$_SESSION['day'] = $_POST['day'];
			$_SESSION['month'] = $_POST['month'];
			$_SESSION['year'] = $_POST['year'];
			header("Location: new-member-1.php");
			exit();
		}
		
	}
	
	
	$validationScript = <<<EOD
    $(document).ready(function() {
	    
   
$('#gramField').bind('keypress keyup blur', function() {
    $('#gramCopy').val($(this).val());
});
	    	    	    
	  $('#registerForm').validate({
		  rules: {
			  accept: {
				  required: true
			  },
			  day: {
				  required: true,
				  range:[1,31]
			  },
			  month: {
				  required: true,
				  range:[1,12]
			  },
			  year: {
				  required: true,
				  range:[1900,2000]
			  },
			  consumoPrevio: {
				  range: [0,60]
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			if (element.is("#savesig")){
				 error.appendTo("#errorBox1");
			} else if (element.is("#accept2")){
				 error.appendTo("#errorBox2");
			} else if (element.is("#accept3")){
				 error.appendTo("#errorBox3");
			} else {
				return true;
			}
		},
		 
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
	  $.validator.messages.required = "Tienes que aceptar!";
	  

  }); // end ready
EOD;

	// Generate random temporary membership number, to use throughout the process.
	$tempNo = "_" . generateRandomString();
	$_SESSION['tempNo'] = $tempNo;
		
	pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes", $lang['member-newmembercaps'] . " - Statutes", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>

<script type="text/javascript" src="scripts/SigWebTablet.js"></script>

<script type="text/javascript">
var tmr;

function onSign()
{
   var ctx = document.getElementById('cnv').getContext('2d');         
   SetDisplayXSize( 500 );
   SetDisplayYSize( 100 );
   SetJustifyMode(0);
   ClearTablet();
   tmr = SetTabletState(1, ctx, 50) || tmr;
}

function onClear()
{
   ClearTablet();
}

function onDone()
{
   if(NumberOfTabletPoints() == 0)
   {
      alert("Tienes que firmar!");
   }
   else
   {
      SetTabletState(0, tmr);
      //RETURN TOPAZ-FORMAT SIGSTRING
      SetSigCompressionMode(1);
      document.FORM1.bioSigData.value=GetSigString();
      document.FORM1.sigStringData.value += GetSigString();
      //this returns the signature in Topaz's own format, with biometric information


      //RETURN BMP BYTE ARRAY CONVERTED TO BASE64 STRING
      SetImageXSize(500);
      SetImageYSize(100);
      SetImagePenWidth(5);
      GetSigImageB64(SigImageCallback);
      document.getElementById("button2").style.background='#b6ec98';
   }
}

function SigImageCallback( str )
{
   document.FORM1.sigImageData.value = str;
}


	
</script> 


<script type="text/javascript">
window.onunload = window.onbeforeunload = (function(){
closingSigWeb()
})

function closingSigWeb()
{
   ClearTablet();
   SetTabletState(0, tmr);
}

</script>

	<form id="registerForm" method="post" name="FORM1" action="">
	


<p>Declaro:</p>

<p>Que cumplo con los requisitos orientados para el funcionamiento de un Club Social de Cannabis, el Reglamento Interno de la Asociación y sus Estatutos.</p>
<ul class="normallist">
 <li>Ser mayor de 21 años de edad</li>
 <li>Consumidor de cannabis</li>
 <li>Cumplir con los objetivos y fines de Asociación Club Social Mannali</li>
</ul>
<p>Que de acuerdo con los derechos de los socios, podré retirar una cantidad adecuada a un consumo responsable que será de: <input type="number" name="consumoPrevio" id="gramField" class="twoDigit" /> gr al mes.</p>
<p>El consumo por socio se limita a la cantidad de 60gr mensuales, siendo el abastecimiento por parte de la Asociación de sólo 7 días, siendo éste el máximo que se dispensa a los socios. (consumo de 1 semana)</p>

<p>Bajo promesa, declara:</p>
<ol>
 <li>Ser usuario habitual de cannabis o haber sido diagnosticado de alguna enfermedad para la cual se haya probado el uso terapéutico o paliativo del cannabis.</li>
 <li>Formar parte voluntariamente de la Compra Mancomunada de cannabis como medio de abastecimiento de la Asociación Club Social Manali.</li>
 <li>Autorizar a los socios colaboradores a comprar, recoger o dispensar su parte mancomunada.</li>
 <li>Compromiso personal de no vender ni hacer llegar a terceros el cannabis que le dispense la Asociación, total o parcialmente, por tratarse de un ilícito penal, ir contra los objetivos y principios de la Asociación, conllevando su expulsión por infracción muy grave.</li>
 <li>Comprometerse a solicitar la baja en la Asociación en el momento que decida abandonar su participación en ella y poder realizar un cálculo real del abastecimiento necesario.</li>
</ol>
<p>Declara consumir <input type="number" id="gramCopy" class="twoDigit" readonly /> gr. de cannabis al mes. Cantidad a ser revisada trimestralmente o prorrogada y siempre por debajo de los 60gr. al mes para concienciar y fomentar un consumo responsable entre los socios.</p>
<p>Los socios nuevos no tienen derecho a dispensación hasta la siguiente compra o abastecimiento por parte de la Asociación, a partir de la cuál se sumará el consumo indicado por el nuevo socio.</p>

<p class="smallerfont">Los Datos Personales que contiene esta inscripción están protegidos bajo el fichero denominado SOCIOS_CSMANALI con la finalidad de identificación de la persona como miembro efectivo de la Asociación Club Social Manali en el cumplimiento de la LO 13/1999 de 15 de diciembre. Se puede ejercer sus derechos ante el local sito en Plaza Arteijo, nº6, Madrid, como responsable del fichero SOCIOS_CSMANALI.</p>


<center>
<h1><?php echo $lang['birthdate']; ?></h1>
   <input type="number" lang="nb" name="day" class="twoDigit" maxlength="2" placeholder="dd" value="<?php echo $day; ?>" />
   <input type="number" lang="nb" name="month" class="twoDigit" maxlength="2" placeholder="mm" value="<?php echo $month; ?>" />
   <input type="number" lang="nb" name="year" class="fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" /><br /><br />


<strong>Tú firma:</strong><br />
<canvas id="cnv" name="cnv" width="500" height="150" onclick="javascript:onSign()" style="border: 2px solid #a80082;"></canvas><br /><br />

<input id="button1" name="ClearBtn" type="button" value="Limpiar" onclick="javascript:onClear()" style="margin-right: 10px; width: 80px;" />

<input id="button2" name="DoneBtn" type="button" value="Finalizar" onclick="javascript:onDone()" style="margin-left: 10px; width: 80px;" /><br />

<br />
<br />

 <table>
  <tr>
   <td><input type="checkbox" name="accept" id="savesig" style="width: 12px;" /></td>
   <td>Estoy de acuerdo y aceptado todo arriba.<br />
   <span id="errorBox1"></span></td>
  </tr>
 </table>
</center><br />
<INPUT TYPE=HIDDEN NAME="bioSigData">
<INPUT TYPE=HIDDEN NAME="sigImgData">
<INPUT TYPE=HIDDEN NAME="sigStringData" />
<INPUT TYPE=HIDDEN NAME="sigImageData" />

	 <button name='oneClick' class='oneClick' type="submit">Guardar</button><br />
	</form>



<?php displayFooter(); ?>
