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
		$memberType = $_POST['memberType'];
		$consumoPrevio = $_POST['consumoPrevio'];
		
		if ($memberType == '1') {
			$usage = "Recreational";
		} else {
			$usage = "Medicinal";
		}
		
		$query = "UPDATE users SET mconsumption = $consumoPrevio WHERE user_id = $user_id";
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting expense: " . mysql_error());
		
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

	$user_id = $_GET['user_id'];
	$_SESSION['tempNo'] = $user_id;
	
	pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes", $lang['member-newmembercaps'] . " - Statutes", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>


<script type="text/javascript" src="js/dd_signature_pad.js"></script>

	<form id="registerForm" method="post" action="">
	 <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />

<p><strong>Por la presente declara:</strong></p>

  <input type='radio' name='memberType' value='1' style='width: 10px; margin-left: 10px;' /> Ser usuario de cannabis l&uacute;dico, y/o,<br />
  <input type='radio' name='memberType' value='2' style='width: 10px; margin-left: 10px;' /> Ser usuario de cannabis  terap&eacute;utico por haber sido diagnosticado/a de alguna enfermedad de las que se conoce que el cannabis pueda resultar eficaz para paliar sus s&iacute;ntomas, aportando documentaci&oacute;n acreditativa de este extremo. 
<ul class="normallist">
 <li>Su voluntad de pertenecer a  la Asociaci&oacute;n CALIFORNIA CLUB , y su compromiso con la misma en el desarrollo de las actividades que se realicen para la consecuci&oacute;n de los fines recogidos en los Estatutos.</li>
 <li>Conocer los Estatutos y el Reglamento Interno de la Asociaci&oacute;n, as&iacute; como mostrar su conformidad con los mismos y ser consciente de que 
el incumplimiento de algunas normas, acarrear&aacute; la expulsi&oacute;n directa de la Asociaci&oacute;n.</li>
 <li>Comprometerse y responsabilizarse a dedicar el cannabis retirado de la Asociaci&oacute;n, &Uacute;NICA Y EXCLUSIVAMENTE A SU CONSUMO PERSONAL EN EL &Aacute;MBITO PRIVADO DE LA ASOCIACI&Oacute;N, asumiendo la responsabilidad de sus actos contrarios a la Ley, y eximiendo por tanto a la Asociaci&oacute;n, sus asociados y miembros de la Junta Directiva.</li>
 <li>El/la soci@  comunicar&aacute; a la Asociaci&oacute;n la notificaci&oacute;n de sanci&oacute;n en base al Art.-25.&ordm; de la Ley Org&aacute;nica 1/1992, sobre Protecci&oacute;n ciudadana.</li>
 <li>El socio se compromete a no ceder su carnet de soci@, el cual es intransferible, la transferencia del mismo ser&aacute; motivo de expulsi&oacute;n. La persona socia siempre mostrar&aacute; tanto su DNI como su carnet de socio para acceder a la sede de la asociaci&oacute;n.</li>
 <li>La persona que firma en calidad de AVAL, declara por la presente conocer la condici&oacute;n de consumidor del solicitante.</li>
</ul>

<ul class="normallist smallerfont">
 <li>La presente solicitud ser&aacute; debidamente revisada por los miembros de la Junta Directiva de la Asociaci&oacute;n, quienes decidir&aacute;n sobre la admisi&oacute;n o inadmisi&oacute;n de la misma. </li>
 <li>No podr&aacute; accederse a la Asociaci&oacute;n, hasta que la presente solicitud sea aprobada, ni por supuesto hacer uso del dispensario, siendo el mismo propiedad de los socios.</li>
</ul>

<p class="smallerfont">Ley Org&aacute;nica 15/1999 de 13 de diciembre de Protecci&oacute;n de Datos de Car&aacute;cter Personal, (LOPD)
CALIFORNIA CLUB informa a sus socios acerca de su pol&iacute;tica de protecci&oacute;n de datos de car&aacute;cter personal, para que los usuarios determinen libre y voluntariamente, si desean facilitar a  CALIFORNIA CLUB los datos personales que se les puedan requerir o que se puedan obtener con ocasi&oacute;n de este cuestionario.
Por lo tanto, los usuarios quedan informados y prestan su consentimiento para que los datos personales recogidos, sean objeto de tratamiento e incorporados a los correspondientes ficheros del CALIFORNIA CLUB siendo esta titular y responsable de los mismos. .Las respuestas a las preguntas sobre datos personales son voluntarias y las contestaciones de los usuarios solo se utilizar&aacute;n para proporcionar servicios solicitados por el usuario. Asimismo quedan informados de que los datos que nos facilitan se incorporar&aacute;n en ficheros responsabilidad del CALIFORNIA CLUB, cumpliendo con todos los requisitos exigidos por la legislaci&oacute;n vigente en materia de protecci&oacute;n de datos. CALIFORNIA CLUB ha adoptado los niveles de seguridad de protecci&oacute;n de los datos personales legalmente requeridos, y ha instalado todos los medios y medidas t&eacute;cnicas a su alcance para evitar la p&eacute;rdida, mal uso, alteraci&oacute;n, acceso no autorizado y robo de los datos personales facilitado. No obstante, el usuario debe ser consciente de que las medidas de seguridad en Internet no son inexpugnables. Los usuarios tienen reconocidos y podr&aacute;n ejercitar los derechos de acceso, cancelaci&oacute;n, rectificaci&oacute;n y oposici&oacute;n de sus datos de car&aacute;cter personal. Tales derechos podr&aacute;n ser ejercitados por el usuario mediante remisi&oacute;n de un correo electr&oacute;nico a la siguiente direcci&oacute;n. Asimismo, y de igual manera, puede revocar el consentimiento prestado a la recepci&oacute;n de comunicaciones comerciales de conformidad con lo dispuesto en la Ley 34/2002. 
</p>

<h1>CONTRATO DE PREVISI&Oacute;N DE CONSUMO</h1>

<ul>
 <li>Ser usuario/a habitual de cannabis sativa y sus derivados o haber sido diagnosticado de alguna enfermedad para la cual la eficacia del uso terap&eacute;utico o paliativo de los cannabinoides haya sido probada cient&iacute;ficamente.</li>

 <li>Comprometerse y responsabilizarse a dedicar el cannabis retirado de la asociaci&oacute;n, y el cual pertenece a todos los socios, &uacute;nica y exclusivamente a su consumo personal en el &aacute;mbito privado de la Asociaci&oacute;n, asumiendo cualquier responsabilidad de sus actos.</li>

 <li>Su compromiso de conocer los Estatutos y cumplir el Reglamento Interno de la Asociaci&oacute;n.</li>

 <li>Qua autoriza expresamente a la Asociaci&oacute;n a adquirir/producir cannabis para su consumo, siempre de acuerdo a la siguiente previsi&oacute;n de consumo mensual (M&Aacute;XIMO 90 GRAMOS): <input type="text" name="consumoPrevio" class="twoDigit" /></li>

 <li>Esta previsi&oacute;n podr&aacute; ser revisada o confirmada por el declarante de forma trimestral, en caso de no procederse a la revisi&oacute;n de la misma, se entender&aacute; la continuidad de la se&ntilde;alada.</li>
</ul>

<h1>NORMAS B&Aacute;SICAS DE CONVIVENCIA:</h1>

<ul>
 <li>Es imprescindible  DNI, NIE, o PASAPORTE, y el carnet de socio para acceder a la Asociaci&oacute;n.</li>
 <li>El carnet es personal e intransferible, si lo extrav&iacute;as, hacer uno nuevo tendr&aacute; un coste de 5 &euro;.</li>
 <li>Para poder acceder a los servicios de la Asociaci&oacute;n, deber&aacute;s haber satisfecho las cuotas correspondientes.</li>
 <li>Respeta el barrio y sus vecinos. </li>
 <li>La Ley proh&iacute;be y sanciona la tenencia y uso de cannabis en la v&iacute;a p&uacute;blica, recuerda que lo que retires debe ser para su consumo inmediato, y siempre en la sede de la asociaci&oacute;n. Puedes dejar en dep&oacute;sito lo que te haya sobrado dentro del dispensario, en el espacio que hay disponible para ello.</li>
 <li>Los productos que sean retirados ser&aacute;n para el uso personal y exclusivo del socio que los retire.</li>
 <li>Queda terminante prohibida la cesi&oacute;n o venta de los productos retirados, lo que supondr&aacute; la expulsi&oacute;n directa de la Asociaci&oacute;n.</li>
 <li>Si vienes acompa&ntilde;ado de no-socios, que no esperen en las inmediaciones, hay que evitar aglomeraciones innecesarias.</li>
 <li>Cuida la sede y sus instalaciones, son nuestra casa.</li>
 <li>Queda terminantemente prohibido y ser&aacute; sancionado con la expulsi&oacute;n directa, el consumo de cualquier sustancia estupefaciente distinta al cannabis. As&iacute; como la venta de cualquier sustancia estupefaciente en la sede de la Asociaci&oacute;n. </li>
 <li>Evita molestar a quien se est&eacute; dispensando, o realizando alguna gesti&oacute;n en Recepci&oacute;n, RESPETA SU INTIMIDAD.</li>
 <li>Si haces fotos en la sede, aseg&uacute;rate de que no salga ning&uacute;n socio.</li>
 <li>Todo lo que te dispenses, sea del Office o del Dispensario, debe quedar registrado, no olvides rellenar y firmar la hoja en cada dispensaci&oacute;n. </li>
 <li>Si haces uso del Office, recuerda que es un espacio de todos, d&eacute;jalo limpio y ordenado.</li>
 <li>No vengas con mucha prisa, los socios que colaboran en la Asociaci&oacute;n ponen todo de su parte para ayudarte.</li>
</ul>

<p><strong>EL INCUMPLIMIENTO DE ESTAS NORMAS, TENDR&Aacute;N CONSECUENCIAS QUE PODR&Aacute;N LLEGAR A LA EXPULSI&Oacute;N DIRECTA EN ALGUNOS CASOS.</strong></p>


</div>
<center><strong><a href="#sig">Your signature:</a></strong><br /><br />
<a name="sig"></a><div id="signatureSet">
		<div id="dd_signaturePadWrapper"></div>
	</div><br />
</center>

<center>
 <table>
  <tr>
   <td><input type="checkbox" name="accept" id="savesig" style="width: 12px;" /></td>
   <td>Estoy de acuerdo y aceptado todo arriba.<br />
   <span id="errorBox1"></span></td>
  </tr>
 </table>   
</center>

<center><span id="errorBox"></span><br />
	 <button name='oneClick' class='oneClick' type="submit">Submit</button><br /><br /><br /></center>
	</form>



<?php displayFooter(); ?>
