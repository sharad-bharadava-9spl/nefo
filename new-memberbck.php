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
			
			$bday = $_POST['year'] . "-" . $_POST['month'] . "-" . $_POST['day'];
			
			function checkmydate($date) {
			  $tempDate = explode('-', $date);
			  // checkdate(month, day, year)
			  return checkdate($tempDate[1], $tempDate[2], $tempDate[0]);
			}
			
			if (checkmydate($bday) == false) {
				$_SESSION['errorMessage'] = $lang['wrong-date-format'];

			} else {
			
				header("Location: new-member-1.php");
				exit();
				
			}
		}
		
	}
		
	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
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
	  

  }); // end ready
EOD;

	// Generate random temporary membership number, to use throughout the process.
	$tempNo = "_" . generateRandomString();
	$_SESSION['tempNo'] = $tempNo;
	$_SESSION['tempNo2'] = $tempNo;
	
	if (isset($_GET['aval'])) {
		$_SESSION['aval'] = $_GET['aval'];
	}
	if (isset($_GET['aval2'])) {
		$_SESSION['aval2'] = $_GET['aval2'];
	}

	pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes", $lang['member-newmembercaps'] . " - Statutes", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>


<script type="text/javascript" src="js/dd_signature_pad.js"></script>

	<form id="registerForm" method="post" action="">

<p><strong>Por la presente declara:</strong></p>
  <input type='radio' name='memberType' value='0' /><strong>Ser usuario de cannabis lúdico.</strong><br />
  <input type='radio' name='memberType' value='1' /><strong>Ser usuario de cannabis terapéutico</strong> por haber sido diagnosticado/a de alguna enfermedad de las que se conoce que el cannabis pueda resultar eficaz para paliar sus síntomas, aportando documentación acreditativa de este extremo. <br /><br />
  
<ol>
 <li>El solicitante afirma cumplir con los siguientes requisitos, necesarios para obtener la condición de socio.<br />
  <ul class="normallist">
   <li>Ser mayor de 18/21 años.</li>
   <li>Tener los documentos acreditativos de identidad en regla.</li>
   <li>No haber sido condenado en relación al artículo 368 del código Penal.</li>
   <li>Ser consumidor previo de cánnabis.</li>
   <li>Ser usuario/a de cannabis lúdico o haber sido diagnosticado/a de alguna enfermedad para la cual la eficacia del uso terapéutico o paliativo de los cannabinoides ha sido reconocida por la IACM.</li>
   <li>Su voluntad de pertenecer como socio en la Asociación Betty Boop de Cannabis,y su compromiso de cumplir sus Estatutos y Reglamento de Régimen Interno, a observar sus fines sociales y a respetar las decisiones de sus órganos de gobierno.</li>
  </ul>
 </li>
 <li>El solicitante declara, haber leído y entendido los estatutos que rigen la Asociación, aceptando las disposiciones que contiene, especialmente las relativas al artículo 5 (fines de la Asociación) y el artículo 28 (obligaciones de los asociados).</li>
 <li>El solicitante se compromete a respetar el reglamento interno de la Asociación así como sus disposiciones Estatutarias  </li>
 <li>El Solicitante quedara integrado en la Asociación denominada Betty Boop, una vez haya satisfecho la cuota que establezca la Junta Directiva,  ello le otorgará derecho a participar en el cultivo colectivo de la Asociación, asumiendo y aceptando plenamente la responsabilidad sobre todos los actos relativos al cultivo, distribución, transporte, almacenaje y cualesquiera otros que fueren necesarios para la puesta a disposición del cultivo a los socios.</li>
 <li>La autorización para obtener la condición de socio, corre a cargo de la Junta Directiva,  siendo requisito indispensable el aval de uno de los socios pertenecientes a la Asociación.</li>
 <li>La vinculación del socio con la Asociación, queda supeditada al pago de las cuotas que se establezcan en cada momento por la Junta Directiva. El impago de dichas cuotas supone que al socio se le dará automáticamente de baja de la Asociación, dado que se configura como un requisito indispensable, la Junta Directiva presumirá que el socio no desea seguir perteneciendo a la misma, y tramitará la baja sin requisito procedimental alguno.<br />
A menos que la junta Directiva no acuerde lo contrario, la cuota a satisfacer será de carácter anual, otorgando al socio la capacidad de entrar en las instalaciones de la Asociación y usar todos sus servicios. Una vez transcurrido este periodo el socio deberá abonar de nuevo la cuota.<br />
Con el fin de supervisar el control de las cuotas, la junta directiva se reserva la facultad de incluir la fecha e inscripción en la tarjeta identificativa del socio, quedando este informado a su vez del periodo de cobertura de la cuota.
 <li>Uno de los fines primordiales de la Asociación,  reside en la evaluación de los riesgos derivados del consumo de cannabis, en los propios estatutos se hace constar que la sustancia no es inocua. Es por este motivo que se le pide a toda persona que quiera ingresar que haga un autoanálisis, determinando con la mayor exactitud posible sus hábitos de consumo, habida cuenta de que el volumen y cuantificación del cultivo colectivo se basa en las estimaciones de consumo que hayan realizado los socios.<br />
La Asociación pretende fomentar un uso racional de la sustancia, es por ello que se le pide al socio que sea consciente de los riesgos derivados del consumo del cannabis antes de firmar este documento.<br />
Atendiendo a este aspecto las instancias directivas de la asociación limitarán la cantidad máxima mensual que puede adquirir cada socio a  80 gramos, con el fin de evitar patrones de conducta abusivos.<br />
No obstante ello no quiere decir que se fomente el consumo de dicha cantidad, al contrario, des de el seno de la entidad se promoverá un uso racional de la sustancia, procurando informar al socio en todo momento de los riesgos asociados al abuso. Todo ello con el fin de erradicar los hábitos de consumo perniciosos en cuanto estos sean detectados.
 </li>
</ol>
 <p>El Solicitante declara consumir  <input type="text" name="consumoPrevio" class="twoDigit" /> gramos de cannabis al mes aproximadamente.</p>

<br /><h1>Cláusula de Protección de Datos Personales</h1>

<span class='smallerfont'><p>De acuerdo con lo establecido en la Ley Orgánica 15/1999, de 13 de diciembre, de Protección de Datos de carácter Personal, La Asociación Betty Boop, solicitan su autorización para realizar el tratamiento de sus datos, quedando el Usuario informado de la incorporación de sus datos a los ficheros automatizados y/o correlativos expedientes en papel, La Asociación tratará estos datos, con la máxima confidencialidad, siendo ésta la única y exclusiva destinataria de los mismos, y no efectuando cesiones o comunicaciones a terceros al margen de las señaladas por la normativa vigente. Los Titulares de los datos, podrán ejercitar sus derechos de acceso, 
rectificación y oposición de acuerdo con la LOPD pudiendo utilizar para ello cualquiera de los canales de comunicación de la Asociación bien sea dirigiéndose a nuestra oficina 
principal o por correo electrónico.</p>
<p>El Usuario responde de la veracidad de los datos facilitados, a la Avocación reservándose el derecho a excluirlos, caso de constatar la falsedad de los mismos.</p>

<p>El Solicitante además da su autorización para que se incluyan sus datos personales, en las bases de datos y ficheros de la Asociación.</p>
<p>La Asociación se compromete a:</p>

<ol>
 <li>Tratar los datos cedidos únicamente para la finalidad especificada, que no será otra que la de ostentar control del volumen de socios, las cuotas anuales y el límite la aportación mensual que el socio pueda realizar.v

 <li>No enriquecerlos con otros datos personales obtenidos por cauces que no garanticen el respeto a la Ley Orgánica 15/1999, de 13 de diciembre, de Protección de Datos de Carácter Personal (en adelante LOPD), que no sean adecuados y pertinentes o resulten excesivos en relación con dicha finalidad.</li>

 <li>No cederlos a terceros o a otras Asociaciones Cannábicas  no autorizados a su uso.</li>

 <li>Aplicar los medios necesarios para garantizar la seguridad de los datos cedidos, de conformidad con lo previsto en la LOPD y disposiciones de desarrollo.</li>

 <li>Guardar la confidencialidad de los datos.</li>

 <li>Cumplir, cuando sea procedente, con la obligación de notificación e inscripción de ficheros en el Registro de la Autoridad de Protección de Datos correspondiente.</li>

 <li>Cancelarlos, procediendo en su caso a su eliminación, una vez cumplida la finalidad para la que han sido solicitado</li>

 <li>Certifico que he sido informado de la existencia del ?chero o tratamiento de    datos de carácter personal de la Asociación Betty Boopde su ?nalidad y de 
los destinatarios de la información. Así mismo he sido informado del carácter obligatorio o facultativo de la respuesta a las preguntas que me han sido planteadas; de las consecuencias de la obtención de los datos y de mi posible negativa a suministrarlos. Se me ha informado de la posibilidad de ejercitar los derechos de acceso, recti?cación, cancelación y oposición.</li>
</ol>
</span>
<em>
<br /><h1>CLAUSULA INFORMATIVA SOBRE VIDEOVIGILANCIA:</h1>
<p>Art. 3, apartado B. Instrucción 1/2006, de 8 de noviembre, de la Agencia Española de Protección de Datos, sobre el tratamiento de datos personales con fines de vigilancia a través de sistemas de cámaras o videocámaras.</p>
<p>De conformidad con lo dispuesto en el art. 5.1 LO 15/1999, de 13 de diciembre, de Protección de Datos, se informa:</p>
<ol>
 <li>Que sus datos personales serán tratados con la finalidad de seguridad a través de un sistema de videovigilancia. 
 <li>Que el destinatario de sus datos personales es la Asociación Betty Boop, cuyo responsable será en todo caso el presidente.</li>
 <li>Que los datos que obtenemos a través de la videovigilancia no serán cedidos bajo ningún concepto, exceptuando los cuerpos de seguridad pertinentes. Y serán tratados dentro de la normativa vigente en materia de protección de datos, Ley Orgánica 15/1999, de 13 de diciembre, de Protección de Datos de carácter personal. (LOPD) Estos datos serán incluidos di ello fuere procedente, en un fichero informático denominado videovigilancia, La función de recabar o grabar imágenes de los socios no es otra que proteger los bienes comunes de la Asociación.</li>
</ol>
</em>
<br />
<center>
<h1><?php echo $lang['birthdate']; ?></h1>
   <input type="number" lang="nb" name="day" class="twoDigit" maxlength="2" placeholder="dd" value="<?php echo $day; ?>" />
   <input type="number" lang="nb" name="month" class="twoDigit" maxlength="2" placeholder="mm" value="<?php echo $month; ?>" />
   <input type="number" lang="nb" name="year" class="fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" /><br /><br />
</center>
</div>
<center><strong><a href="#sig">Tú firma:</a></strong><br /><br />
<a name="sig"></a><div id="signatureSet">
		<div id="dd_signaturePadWrapper"></div>
	</div><br />
</center>
<center>
 <table>
  <tr>
   <td><input type="checkbox" name="accept" id="savesig" style="width: 12px;" /></td>
   <td>&nbsp;&nbsp;La
solicitante
manifiesta
haber
leído
los
estatutos
y
la
presente
solicitud
de
admisión
de
persona
socia.<br />
   <span id="errorBox1"></span></td>
  </tr>
 </table>   
</center>

<center><span id="errorBox"></span><br />
	 <button name='oneClick' class='oneClick' type="submit">Submit</button><br /><br /><br /></center>
	</form>



<?php displayFooter(); ?>
