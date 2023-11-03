<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_POST['accept'])) {
		$user_id = $_POST['user_id'];
		$usageType = $_POST['memberType'];
		$consumoPrevio = $_POST['consumoPrevio'];

		$query = "UPDATE users SET usageType = '$usageType', mconsumption = $consumoPrevio WHERE user_id = $user_id";
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
		
		$_SESSION['successMessage'] = "Contrato actualizado con &eacute;xito!";
		header("Location: profile.php?user_id=$user_id");
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
			  memberType: {
				  required: true
			  },
			  consumoPrevio: {
				  required: true
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
			border="solid 1px #00a48c";
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

	$user_id = $_GET['user_id'];
	$mconsumption = $_GET['mconsumption'];
	$usageType = $_GET['usageType'];
	
	$_SESSION['tempNo'] = $user_id;
	
	pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes dev-align-center", $lang['member-newmembercaps'] . " - Statutes", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>


<script type="text/javascript" src="js/dd_signature_pad.js"></script>
<div class="actionbox-np2" style="text-align: left;">
	<div class='mainboxheader'>SOLICITUD DE INSCRIPCIÓN / DECLARACIÓN JURADA</div>
	<div class="boxcontent"> 
<form id="registerForm" method="post" action="">
 <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />	
	


<p><strong>DEMO CLUB</strong></p>
		<div class="fakeboxholder customradio">	
	 	 
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a) Socio lúdico: aporta datos, fecha y firma de el/la soci@ aval de la condición de consumidor de las sustancias mencionadas en los estatutos y en este documento de la solicitante de admisión.
		  <input type="radio" name="memberType"  value="0">
		  <div class="fakebox"></div>
		 </label>
		</div>	
		<br>	
		<div class="fakeboxholder customradio">	
	 	 
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b) Socio terapéutico: Muestra informes médicos que acreditan la condición de Usuario Terapéutico, (por estar contenidas estas patologías en la lista de la IACM) o certificado médico emitido por un doctor donde le recomienda el uso de los cannabinoides.
		  <input type="radio" name="memberType"  value="1">
		  <div class="fakebox"></div>
		 </label>
		</div>
<!--   <input type='radio' name='memberType' value='0' style='width: 10px; margin-left: 10px;' /> a) Socio lúdico: aporta datos, fecha y firma de el/la soci@ aval de la condición de consumidor de las sustancias mencionadas en los estatutos y en este documento de la solicitante de admisión.<br /><br />
  <input type='radio' name='memberType' value='1' style='width: 10px; margin-left: 10px;' /> b) Socio terapéutico: Muestra informes médicos que acreditan la condición de Usuario Terapéutico, (por estar contenidas estas patologías en la lista de la IACM) o certificado médico emitido por un doctor donde le recomienda el uso de los cannabinoides. -->

 <p><strong>Por la presente declara:</strong></p>

<ul class="normallist">
 <li>Ser usuario/a de cannabis lúdico o terapéutico por haber sido diagnosticado/a de alguna enfermedad para la cual el cannabis sea eficaz para paliar sus síntomas.<br />&nbsp;</li>
 <li>Ser consumidor de tabaco o asumir no tener problemas con que otros lo consuman en el recinto de la entidad.<br />&nbsp;</li>
 <li>Su voluntad de pertenecer como asociado/a.<br />&nbsp;</li>
 <li>No tener antecedentes penales relativos a delitos contra la salud pública, de tenerlos deberá reflejarlos<br />&nbsp;</li>
 <li>Haber leído y aceptado en su totalidad los Estatutos que rigen la entidad, y su compromiso de cumplirlos, a la vez que el reglamento de Régimen Interno, observando sus fines sociales y respetando las decisiones de sus órganos de gobierno.<br />&nbsp;</li>
 <li>Como consumidor habitual de cannabis indicar que el consumo semanal que la asociación aportará a este socio será de <input type="text" name="consumoPrevio" class="twoDigit defaultinput" /> gramos de cannabis, acorde la misma en la cuantía estimada por la jurisprudencia de autoconsumo, de 3 a 5 gramos diarios. Este aporte de Cannabis no podrá ser extraído del local de la asociación en ningún caso y tras proporcionárselo el socio deberá permanecer en el local una estancia mínima de veinte minutos<br />&nbsp;</li>
 <li>Habiendo sido informado de las necesidades de la asociación se compromete a aportar de forma voluntaria una cuota mensual
de 15 euros para el sostenimiento de la misma, mediante efectivo antes del día cinco de cada mes, esta cuota da la
condición de socio de la misma y su impago propiciaría la expulsión. Cabe también la forma de donación trimestral o anual.</li>
 <li>Por la presente autorizo a DEMO CLUB a recoger, transportar, almacenar, custodiar y a administrar la demanda de consumo y la retirada de mi parte de la compra mancomunada o cosecha colectiva<br />&nbsp;</li>
 <li>Conocer y poner especial atención a los siguientes preceptos:<br />&nbsp;</li>
</ul>

 <p><strong><em>El artículo 36.16 de la Ley Orgánica 4/2015, de 30 de marzo, de protección de la seguridad ciudadana:</em></strong></p>

 <p>El consumo o la tenencia ilícitos de drogas tóxicas, estupefacientes o sustancias psicotrópicas, aunque no estuvieran destinadas al tráfico, en lugares, vías, establecimientos públicos o transportes colectivos, así como el abandono de los instrumentos u otros efectos empleados para ello en los citados lugares.</p>

 <p><strong><em>El artículo 368 del Código Penal Español, Ley Orgánica 10/1995:</em></strong></p>

 <p>Los que ejecuten actos de cultivo, elaboración o tráfico, o de otro modo promuevan, favorezcan o faciliten el consumo ilegal de drogas tóxicas, estupefacientes o sustancias psicotrópicas, o las posean con aquellos fines, serán castigados con las penas de prisión de tres a seis años y multa del tanto al triplo del valor de la droga objeto del delito si se tratare de sustancias o productos que causen grave daño a la salud, y de prisión de uno a tres años y multa del tanto al duplo en los demás casos.</p>

 <p><strong><em>El artículo 18 de la Constitución Española:</em></strong></p>

 <ol>
  <li>Se garantiza el derecho al honor, a la intimidad personal y familiar y a la propia imagen.</li>
  <li>El domicilio es inviolable. Ninguna entrada o registro podrá hacerse en el sin consentimiento del titular o resolución judicial, salvo en caso de flagrante delito.</li>
  <li>Se garantiza el secreto de las comunicaciones y, en especial, de las postales, telegráficas y telefónicas, salvo resolución judicial.</li>
  <li>La Ley limitará el uso de la informática para garantizar el honor y la intimidad personal y familiar de los ciudadanos y el pleno ejercicio de sus derechos.</li>
 </ol>
 <p><strong><em>El artículo 22 de la Constitución Española:</em></strong></p>
 <ol>
  <li>Se reconoce el derecho de asociación.</li>
  <li>Las asociaciones que persigan fines o utilicen medios tipificados como delito son ilegales.</li>
  <li>Las asociaciones constituidas al amparo de este artículo deberán inscribirse en un registro a los solos efectos de publicidad.</li>
  <li>Las asociaciones sólo podr??n ser disueltas o suspendidas en sus actividades en virtud de resolución judicial motivada.</li>
  <li>Se prohíben las asociaciones secretas y las de carácter paramilitar.</li>
 </ol>

 <p class="smallerfont"><strong>Ley Orgánica 15/1999 de 13 de diciembre de Protección de Datos de Carácter Personal, (LOPD)</strong></p>
 <p class="smallerfont">DEMO CLUB informa a sus socios acerca de su política de protección de datos de carácter personal, para que
los usuarios determinen libre y voluntariamente, si desean facilitar a DEMO CLUB los datos personales que
se les puedan requerir o que se puedan obtener con ocasión de este cuestionario en DEMO CLUB. Por lo
tanto, los usuarios quedan informados y prestan su consentimiento para que los datos personales recogidos, sean objeto de tratamiento e incorporados a los
correspondientes ficheros de DEMO CLUB siendo esta titular y responsable de los mismos.</p>
 <p class="smallerfont">Las respuestas a las preguntas sobre datos personales son voluntarias y las contestaciones de los usuarios solo se utilizarán para proporcionar servicios solicitados
por el usuario. Asimismo, quedan informados de que los datos que nos facilitan se incorporarán en ficheros responsabilidad del DEMO CLUB , cumpliendo con todos los requisitos exigidos por la legislación vigente en materia de protección de datos.
DEMO CLUB ha adoptado los niveles de seguridad de protección de los datos personales legalmente
requeridos, y ha instalado todos los medios y medidas técnicas a su alcance para evitar la pérdida, mal uso, alteración, acceso no autorizado y robo de los datos
personales facilitado. No obstante, el usuario debe ser consciente de que las medidas de seguridad en Internet no son inexpugnables. Los usuarios tienen
reconocidos y podrán ejercitar los derechos de acceso, cancelación, rectificación y oposición de sus datos de carácter personal. Tales derechos podrán ser
ejercitados por el usuario mediante remisión de un correo electrónico a la siguiente dirección</p>
 <p class="smallerfont">Asimismo, y de igual manera, puede revocar el consentimiento prestado a la recepción de comunicaciones comerciales de conformidad con lo dispuesto en la Ley
34/2002.</p>
<br />
<br />

<center><strong><a href="#sig">Tú firma:</a></strong><br /><br />
<a name="sig"></a><div id="signatureSet">
		<div id="dd_signaturePadWrapper"></div>
	</div><br />
</center>

<center>
 <table>
  <tr>
   <td>
	   	<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; La solicitante manifiesta haber le&iacute;do los estatutos y la presente solicitud de participaci&oacute;n en el programa de cultivo colectivo.
		  <input type="checkbox" name="accept" id="savesig">
		  <div class="fakebox"></div>
		 </label>
		</div>
	   <span id="errorBox1"></span>
	</td>
  </tr>
 </table>   
</center><br />
<center><span id="errorBox"></span></center><br />

	 <center><button name='oneClick' class='oneClick cta1' type="submit">Submit</button></center>
	</form>
	</div>
</div>



<?php displayFooter(); ?>
