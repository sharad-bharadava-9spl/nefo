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
			pageStart($lang['member-newmembercaps'] . " - " . $lang['statutes'], NULL, $validationScript, "pprofile", "statutes", $lang['member-newmembercaps'] . " - " . $lang['statutes'], $_SESSION['successMessage'], $lang['too-young'] . $minAge . ".");
			exit();
		} else {
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
	    
$('#dd_signaturePadWrapper').click(function(e) {  
        $('#savesig').attr('checked', false)
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
	  
function demo_postSaveAction(f) {
	var objParent=document.getElementById('testDiv');
	var objDiv=document.createElement('div');
	with(objDiv) {
		setAttribute('id','demo_downloadWrapper');
		with(style) {
			position="relative";
			padding="10px";
			textAlign="left";
			margin="15px 70px 0px 70px";
			border="solid 1px #c0c0c0";
			backgroundColor="#efefef";
			borderRadius="4pt";
		}
		innerHTML="<h4>Demo signature saved to image. Click to download.</h4>";
		innerHTML+="<ul><li><a href=\"dd_signature_process.php?download="+f+"\" target=\"_blank\">"+f+"</a></li></ul>";
	}
	objParent.appendChild(objDiv);
}

  }); // end ready
EOD;

	// Generate random temporary membership number, to use throughout the process.
	$tempNo = "_" . generateRandomString();
	$_SESSION['tempNo'] = $tempNo;
	$_SESSION['aval'] = $_GET['aval'];

	pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes", $lang['member-newmembercaps'] . " - Statutes", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>


<script type="text/javascript" src="js/dd_signature_pad.js"></script>

	<form id="registerForm" method="post" action="">


<p><strong>Por la presente, solicito a la Junta directiva de la asociaci&oacute;n ser admitido como socio al cumplir con los requisitos establecidos en los estatutos, y manifiesto ser mayor de 21 a&ntilde;os y:</strong></p>

  <input type='checkbox' name='cannabis' value='1' style='width: 10px; margin-left: 10px;' /> SER CONSUMIDOR/A DE CANNABIS SATIVA L., <br />
  <input type='checkbox' name='tobacco' value='1' style='width: 10px; margin-left: 10px;' /> SER CONSUMIDOR DE TABACO<br />
  <input type='radio' name='memberType' value='1' style='width: 10px; margin-left: 10px;' /> SER CONSUMIDOR DE PLANTAS CON PROPIEDADES TERAP&Eacute;UTICAS<br />
  <input type='radio' name='memberType' value='2' style='width: 10px; margin-left: 10px;' /> SER CONSUMIDOR HABITUAL DE CANNABIS POR RAZONES TERAP&Eacute;UTICAS, y tener diagnosticada una enfermedad para la que el cannabis tiene efectos terap&eacute;uticos seg&uacute;n la IACM,  o que el uso paliativo de los cannabinoides ha sido probada cient&iacute;ficamente y aportar certificado m&eacute;dico acreditativo de la solicitud de uso de cannabis por parte del paciente sin oposici&oacute;n m&eacute;dica, o tener una licencia de uso de cannabis medicinal de cualquier pa&iacute;s del Mundo.
<p><strong>AFIRMO</strong> mi voluntad de incorporarme como <strong>SOCIO/A a la </strong>ASOCIACI&Oacute;N CANN&Aacute;BICA MIFAMAX</strong> conociendo los estatutos y objetivos de &eacute;sta y me comprometo a cumplir con ellos, con las normas de funcionamiento interno y con la legislaci&oacute;n espa&ntilde;ola, con especial atenci&oacute;n a:</p>

<p class="smallerfont"><strong>El art&iacute;culo 19 de la Ley Org&aacute;nica 1/1992, sobre Protecci&oacute;n de la Seguridad ciudadana: 1)</strong> Los agentes de las Fuerzas y Cuerpos de Seguridad <strong>podr&aacute;n limitar o restringir</strong>, por el tiempo imprescindible, <strong>la circulaci&oacute;n o permanencia en v&iacute;as o lugares p&uacute;blicos</strong> en supuestos de alteraci&oacute;n del orden, la seguridad ciudadana o la pac&iacute;fica convivencia, cuando fuere necesario para su restablecimiento. <strong>Asimismo podr&aacute;n ocupar preventivamente los efectos o instrumentos susceptibles de ser utilizados para acciones ilegales</strong>, d&aacute;ndoles el destino que legalmente proceda. <strong>2)</strong> Para el descubrimiento y detenci&oacute;n de los part&iacute;cipes en un hecho delictivo causante de grave alarma social y para la recogida de los instrumentos, efectos o pruebas del mismo, se <strong>podr&aacute;n establecer controles en las v&iacute;as, lugares o establecimientos p&uacute;blicos</strong>, en la medida indispensable a los fines de este apartado, <strong>al objeto de proceder a la identificaci&oacute;n de las personas que transiten o se encuentren en ellos, al registro de los veh&iacute;culos y al <u>control superficial de los efectos personales</u></strong> con el fin de comprobar que no se portan sustancias o instrumentos prohibidos o peligrosos. El resultado de la diligencia se pondr&aacute; de inmediato en conocimiento del Ministerio Fiscal.</p>
<p class="smallerfont"><strong>El art&iacute;culo 25.1 de la Ley Org&aacute;nica 1/1992, sobre Protecci&oacute;n de la Seguridad ciudadana:</strong> Constituyen infracciones graves a la seguridad ciudadana <strong>el consumo en lugares, v&iacute;as, establecimientos o transportes p&uacute;blicos</strong>, as&iacute; como <strong>la <u>tenencia il&iacute;cita</u></strong>, aunque no estuviera destinada al tr&aacute;fico de drogas t&oacute;xicas, <strong>estupefacientes o sustancias psicotr&oacute;picas, siempre que no constituya infracci&oacute;n penal</strong>, as&iacute; como <strong>el <u>abandono</u></strong> en los sitios mencionados <strong><u>de &uacute;tiles</u> o <u>instrumentos</u> utilizados para su consumo.</strong></p>
<p class="smallerfont"><strong>El art&iacute;culo 368 del C&oacute;digo Penal Espa&ntilde;ol, Ley Org&aacute;nica 5/2010:</strong> Los que ejecuten <strong>actos de cultivo, elaboraci&oacute;n o tr&aacute;fico</strong>, o de otro modo <strong>promuevan, favorezcan o faciliten el consumo ilegal de drogas t&oacute;xicas, estupefacientes o sustancias psicotr&oacute;picas</strong>, o las <strong>posean con aquellos fines</strong>, ser&aacute;n castigados con las <strong>penas de prisi&oacute;n de tres a seis a&ntilde;os y multa del tanto al triplo del valor de la droga objeto del delito si se tratare de sustancias o productos que causen grave da&ntilde;o a la salud, y de prisi&oacute;n de uno a tres a&ntilde;os y multa del tanto al duplo en los dem&aacute;s casos</strong>.</p>
<p class="smallerfont"><strong>El art&iacute;culo 18 de la Constituci&oacute;n Espa&ntilde;ola: 1.</strong> Se garantiza el <strong><u>derecho al honor, a la intimidad personal y familiar y a la propia imagen</u>. 2. El domicilio es inviolable. Ninguna entrada o registro</strong> podr&aacute; hacerse en el <strong>sin consentimiento del titular o resoluci&oacute;n judicial, salvo en caso de flagrante delito. 3.</strong> Se garantiza <strong>el secreto de las comunicaciones y</strong>, en especial, <strong>de las postales, telegr&aacute;ficas y telef&oacute;nicas, salvo resoluci&oacute;n judicial. 4. <u>La Ley</u> limitar&aacute; el uso de la inform&aacute;tica</strong> para garantizar el honor y la intimidad personal y familiar de los ciudadanos y el pleno ejercicio de sus derechos.</p>


<h1>SOLICITUD DE PARTICIPACI&Oacute;N EN EL PROGRAMA DE CULTIVO COLECTIVO DE LA ASOCIACI&Oacute;N CANN&Aacute;BICA MIFAMAX</h1>

<p>Por la presente manifiesto ser mayor de 21 a&ntilde;os y  mi condici&oacute;n de consumidor habitual de Cannabis Sativa L. y solicito participar en el programa de cultivo colectivo con los miembros de la asociaci&oacute;n asumiendo mi corresponsabilidad en el cultivo junto al resto de personas participantes en el mismo.</p>
El producto de mi participaci&oacute;n en el cultivo asociativo es &Uacute;NICA Y EXCLUSIVAMENTE A PARA MI CONSUMO PERSONAL EN EL &Aacute;MBITO PRIVADO  DEL LOCAL SOCIAL, asumiendo cualquier responsabilidad de mis actos contrarios a la Ley que se pudieran derivar y <u>eximiendo de ello a la Asociaci&oacute;n, Asociadas y Junta Directiva</u>.</p>

<ul class="normallist">
 <li>Para este efecto adjunta el <u>patrocinio/aval de UNA SOCIA de la Asociaci&oacute;n</u> a la presente solicitud que manifiesta ser conocedora del consumo de cannabis por parte de la persona que solicita su incorporaci&oacute;n a la Asociaci&oacute;n o documentaci&oacute;n que acredite que se trata de una persona consumidora.</li>
 <li>Solicito participar en la cantidad de <input type="text" name="consumoPrevio" class="twoDigit" /> gr. de Cannabis al mes, cantidad que  ser&aacute; revisada o confirmada por la socia a  cada trimestre. En caso de no confirmar o revisar se prorrogar&aacute; la cantidad ya declarada.</li>
 <li>La socia autoriza a las socias activas colaboradoras de la Asociaci&oacute;n a cultivar, recoger y repartir entre las socias el Cannabis proveniente del cultivo colectivo asociativo para el autoconsumo.</li>
 <li>La Socia se obliga a <u>comunicar de inmediato</u>, en el momento que decida abandonar su participaci&oacute;n en la Asociaci&oacute;n, <u>la solicitud de baja de la Asociaci&oacute;n</u>. Anualmente deber&aacute; ratificarse la condici&oacute;n de socia.</li>
 <li>La Socia <u>comunicar&aacute;</u> a la Asociaci&oacute;n la <u>notificaci&oacute;n de sanci&oacute;n</u> en base al <u>art&iacute;culo 25.1 de la Ley Org&aacute;nica 1/1992, sobre Protecci&oacute;n Ciudadana</u>, <u>o</u> la <u>imputaci&oacute;n del delito tipificado en el art&iacute;culo 368 del C&oacute;digo Penal</u>, pudiendo implicar en el primer caso e implicando en el segundo la expulsi&oacute;n de la asociaci&oacute;n.</li>
 <li>La Socia se compromete a <u>no ceder su carnet socia</u>, el cu&aacute;l es intransferible; la transferencia del carnet de socia ser&aacute; motivo de expulsi&oacute;n de la asociaci&oacute;n. La persona socia siempre exhibir&aacute; su documento de identidad y el carnet de socia para retirar su material.</li>
 <li>Cualquier <u>incumplimiento de los compromisos</u> adquiridos mediante la firma del presente <u>podr&aacute; implicar o implicar&aacute; la expulsi&oacute;n de la Asociaci&oacute;n</u> mediante el correspondiente procedimiento fijado en los estatutos.</li>
</ul>

<p class="smallerfont">De conformidad con lo que establece la Ley Org&aacute;nica 15/1999 de Protecci&oacute;n de Datos de Car&aacute;cter Personal, le informamos que sus datos personales ser&aacute;n incorporados a un fichero bajo la responsabilidad de ASOCIACION MIFAMAX, con la finalidad de poder atender su solicitud de asociarse a nuestra entidad. Puede ejercer sus derechos de acceso, cancelaci&oacute;n,rectificaci&oacute;n y oposici&oacute;n mediante un escrito a la direcci&oacute;n: NAPOLES, 187 BAJOS 08013,BARCELONA.</p>
<p>Si en el periodo de 30 d&iacute;as no nos comunica lo contrario, entenderemos que sus datos no han sido modificados, que se compromete a notificarnos cualquier variaci&oacute;n y que tenemos su consentimiento para enviarle publicidad de las actividades que lleva a t&eacute;rmino nuestra entidad.</p>

<br />
<center>
<h1><?php echo $lang['birthdate']; ?></h1>
   <input type="number" lang="nb" name="day" class="twoDigit" maxlength="2" placeholder="dd" value="<?php echo $day; ?>" />
   <input type="number" lang="nb" name="month" class="twoDigit" maxlength="2" placeholder="mm" value="<?php echo $month; ?>" />
   <input type="number" lang="nb" name="year" class="fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" /><br /><br />
</center>

<center><strong>T&uacute; firma:</strong><br />
<div id="signatureSet">
		<div id="dd_signaturePadWrapper"></div>
	</div><br />
</center>

<center>
 <table>
  <tr>
   <td><input type="checkbox" name="accept" id="savesig" style="width: 12px;" /></td>
   <td>La solicitante manifiesta haber le&iacute;do los estatutos y la presente solicitud de participaci&oacute;n en el programa de cultivo colectivo.<br />
   <span id="errorBox1"></span></td>
  </tr>
 </table>   
</center><br />
<center><span id="errorBox"></span></center><br />

	 <button name='oneClick' class='oneClick' type="submit">Submit</button><br />
	</form>



<?php displayFooter(); ?>
