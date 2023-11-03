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


<p>Por la presente, solicito a la Junta directiva de la asociaci&oacute;n ser admitido como socio al cumplir con los requisitos establecidos en los estatutos, y manifiesto ser mayor de  21 a&ntilde;os de edad  y juro o prometo:</p><br />
  <input type='radio' name='memberType' value='1' /><strong>SER CONSUMIDOR/A DE CANNABIS SATIVA L.</strong>, con fines de Salud y de mejora de mi calidad de vida.<br />
  <input type='radio' name='memberType' value='2' /><strong>SER CONSUMIDOR HABITUAL DE CANNABIS POR RAZONES TERAPÉUTICAS</strong>, y tener diagnosticada una enfermedad para la que el cannabis tiene efectos terap&eacute;uticos seg&uacute;n la IACM,  o que el uso paliativo de los cannabinoides ha sido probada cient&iacute;ficamente y aportar certificado m&eacute;dico acreditativo de la solicitud de uso de cannabis por parte del paciente sin oposici&oacute;n m&eacute;dica, o tener una licencia de uso de cannabis medicinal de cualquier pa&iacute;s del Mundo,
<p>AFIRMO mi voluntad de incorporarme como SOCIO/A a la ASOCIACI&Oacute;N  conociendo los estatutos y objetivos de &eacute;sta y me comprometo a cumplir con ellos, con las normas de funcionamiento interno y con la legislaci&oacute;n vigente, con especial atenci&oacute;n a:</p>
<p>El art&iacute;culo 17 de la Ley Org&aacute;nica 4/2015, de protecci&oacute;n de la seguridad ciudadana: 1) Los agentes de las Fuerzas y Cuerpos de Seguridad podr&aacute;n limitar o restringir la circulaci&oacute;n o permanencia en v&iacute;as o lugares p&uacute;blicos y establecer zonas de seguridad en supuestos de alteraci&oacute;n de la seguridad ciudadana o de la pac&iacute;fica convivencia, o cuando existan indicios racionales de que pueda producirse dicha alteraci&oacute;n, por el tiempo imprescindible para su mantenimiento o restablecimiento. Asimismo podr&aacute;n ocupar preventivamente los efectos o instrumentos susceptibles de ser utilizados pata acciones ilegales, d&aacute;ndoles el destino que legalmente proceda. 2) Para la prevenci&oacute;n de delitos de especial gravedad o generadores de alarma social, as&iacute; como para el descubrimiento y detenci&oacute;n de quienes hubieran participado en su comisi&oacute;n y proceder a la recogida de instrumentos, efectos o pruebas, se podr&aacute;n establecer controles en las v&iacute;as, lugares o establecimientos p&uacute;blicos, siempre que resulte indispensable proceder a la identificaci&oacute;n de personas que se encuentren en ellos, al registro de veh&iacute;culos o al control superficial de efectos personales.</p>
<p>El art&iacute;culo 18.1 de la Ley Org&aacute;nica 4/2015, de protecci&oacute;n de la seguridad ciudadana: Los agentes de la autoridad podr&aacute;n practicar las comprobaciones en las personas, bienes y veh&iacute;culos que sean necesarias para impedir que en las v&iacute;as, lugares y establecimientos p&uacute;blicos se porten o utilicen ilegalmente armas, explosivos, sustancias peligrosas u otros objetos, instrumentos o medios que generen un riesgo potencialmente grave para las personas, susceptibles de ser utilizados para la comisi&oacute;n de un delito o alterar la seguridad ciudadana, cuando tengan indicios de su eventual presencia en dichos lugares, procediendo, en su caso, a su intervenci&oacute;n. A tal fin, los ciudadanos tienen el deber de colaborar y no obstaculizar la labor de los agentes de la autoridad en el ejercicio de sus funciones.</p>
<p>El art&iacute;culo 19 de la Ley Org&aacute;nica 4/2015, de protecci&oacute;n de la seguridad ciudadana: 1) Las diligencias de identificaci&oacute;n, registro y comprobaci&oacute;n practicadas por los agentes de las Fuerzas y Cuerpos de Seguridad con ocasi&oacute;n de actuaciones realizadas conforme a lo dispuesto en esta secci&oacute;n no estar&aacute;n sujetas a las mismas formalidades que la detenci&oacute;n. 2) La aprehensi&oacute;n durante las diligencias de identificaci&oacute;n, registro y comprobaci&oacute;n de armas, drogas t&oacute;xicas, estupefacientes, sustancias psicotr&oacute;picas u otros efectos procedentes de un delito o infracci&oacute;n administrativa se har&aacute; constar en el acta correspondiente, que habr&aacute; de ser firmada por el interesado; si &eacute;ste se negara a firmarla, se dejar&aacute; constancia expresa de su negativa. El acta que se extienda gozar&aacute; de presunci&oacute;n de veracidad de los hechos en ella consignados, salvo prueba en contrario.</p>
<p>El art&iacute;culo 20 de la Ley Org&aacute;nica 4/2015, de protecci&oacute;n de la seguridad ciudadana:<br />
1) Podr&aacute; practicarse el registro corporal externo y superficial de la persona cuando existan indicios racionales para suponer que puede conducir al hallazgo de instrumentos, efectos u otros objetos relevantes para el ejercicio de las funciones de indagaci&oacute;n y prevenci&oacute;n que encomiendan las leyes a las Fuerzas y Cuerpos de Seguridad.<br />
2) Salvo que exista una situaci&oacute;n de urgencia por riesgo grave e inminente para los agentes: a) El registro se realizar&aacute; por un agente del mismo sexo que la persona sobre la que se practique esta diligencia; b) Y si exigiera dejar a la vista partes del cuerpo normalmente cubiertas por ropa, se efectuar&aacute; en un lugar reservado y fuera de la vista de terceros. Se dejar&aacute; constancia escrita de esta diligencia, de sus causas y de la identidad del agente que la adopt&oacute;.<br />
3) Los registros corporales externos respetar&aacute;n los principios del apartado 1 del art&iacute;culo 16, as&iacute; como el de injerencia m&iacute;nima, y se realizar&aacute;n del modo que cause el menor perjuicio a la intimidad y dignidad de la persona afectada, que ser&aacute; informada de modo inmediato y comprensible de las razones de su realizaci&oacute;n.<br />
4) Los registros a los que se refiere este art&iacute;culo podr&aacute;n llevarse a cabo contra la voluntad del afectado, adoptando las medidas de compulsi&oacute;n indispensables, conforme a los principios de idoneidad, necesidad y proporcionalidad.</p>
<p>El art&iacute;culo 36 de la Ley Org&aacute;nica 4/2015, de protecci&oacute;n de la seguridad ciudadana: Son infracciones graves: 16) El consumo o la tenencia il&iacute;citos de drogas t&oacute;xicas, estupefacientes o sustancias psicotr&oacute;picas, aunque no estuvieran destinadas al tr&aacute;fico, en lugares, v&iacute;as, establecimientos p&uacute;blicos o transportes colectivos, as&iacute; como el abandono de los instrumentos o efectos empleados para ello en los citados lugares. 17) El traslado de personas, con cualquier tipo de veh&iacute;culo, con el objeto de facilitar a &eacute;stas el acceso a drogas t&oacute;xicas, estupefacientes o sustancias psicotr&oacute;picas, siempre que no constituya delito. 18) La ejecuci&oacute;n de actos de plantaci&oacute;n y cultivo de drogas t&oacute;xicas, estupefacientes o sustancias psicotr&oacute;picas en lugares visibles al p&uacute;blico, cuando no sean constitutivos de infracci&oacute;n penal. 19) La tolerancia del consumo ilegal o el tr&aacute;fico de drogas t&oacute;xicas, estupefacientes o sustancias psicotr&oacute;picas en locales o establecimientos p&uacute;blicos o la falta de diligencia en orden a impedirlos por arte de los propietarios, administradores o encargados de los mismos. 20) La carencia de los registros previstos en esta Ley para las actividades con trascendencia para la seguridad ciudadana o la omisi&oacute;n de comunicaciones obligatorias.</p>

<p>El art&iacute;culo 39 de la Ley Org&aacute;nica 4/2015, de protecci&oacute;n de la seguridad ciudadana: 1) Las infracciones muy graves se sancionar&aacute;n con multa de 30.001 a 600.000 euros; las graves, con multa de 601 a 30.000 euros, y las leves, con multa de 100 a 600 euros.</p>
<p>De acuerdo con lo dispuesto en el art&iacute;culo 33.2, los tramos correspondientes a los grados m&aacute;ximo, medio y m&iacute;nimo de las multas previstas por la comisi&oacute;n de infracciones graves y muy graves ser&aacute;n los siguientes: a) Para las infracciones muy graves, el grado m&iacute;nimo comprender&aacute; la multa de 30.001 a 220.000 euros; el grado medio, de 220.001 a 410.000 euros, y el grado m&aacute;ximo, de 410.001 a 600.000 euros. b) Para las infracciones graves, el grado m&iacute;nimo comprender&aacute; la multa de 601 a 10.400; el grado medio, de 10.401 a 20.200 euros, y el grado m&aacute;ximo, de 20.201 a 30.000 euros.</p>
<p>2) La multa podr&aacute; llevar aparejada alguna de las siguientes sanciones accesorias, atendiendo a la naturaleza de los hechos constitutivos de la infracci&oacute;n: a) la retirada de armas y de licencias o permisos correspondientes a las mismas. b) el comiso de los bienes, medios o instrumentos con los que  se haya reparado o ejecutado la infracci&oacute;n y, en su caso, de los efectos procedentes de &eacute;sta, salvo que unos u otros pertenezcan a un tercero de buena fe no responsable de dicha infracci&oacute;n que los haya adquirido legalmente. Cuando los instrumentos o efectos sean de l&iacute;cito comercio y su valor no guarde relaci&oacute;n con la naturaleza o gravedad de la infracci&oacute;n, el &oacute;rgano competente para imponer la sanci&oacute;n que proceda podr&aacute; no acordar el comiso o acodarlo parcialmente.</p>

<p>El art&iacute;culo 368 del C&oacute;digo Penal Espa&ntilde;ol: Los que ejecuten actos de cultivo, elaboraci&oacute;n o tr&aacute;fico, o de otro modo promuevan, favorezcan o faciliten el consumo ilegal de drogas t&oacute;xicas, estupefacientes o sustancias psicotr&oacute;picas, o las posean con aquellos fines, ser&aacute;n castigados con las penas de prisi&oacute;n de tres a seis a&ntilde;os y multa del tanto al triplo del valor de la droga objeto del delito si se tratare de sustancias o productos que causen grave da&ntilde;o a la salud, y de prisi&oacute;n de uno a tres a&ntilde;os y multa del tanto al duplo en los dem&aacute;s casos.</p>

<p>El art&iacute;culo 18 de la Constituci&oacute;n Espa&ntilde;ola:<br />
1. Se garantiza el derecho al honor, a la intimidad personal y familiar y a la propia imagen.<br />
2. El domicilio es inviolable. Ninguna entrada o registro podr&aacute; hacerse en el sin consentimiento del titular o resoluci&oacute;n judicial, salvo en caso de flagrante delito.<br />
3. Se garantiza el secreto de las comunicaciones y, en especial, de las postales, telegr&aacute;ficas y telef&oacute;nicas, salvo resoluci&oacute;n judicial.<br />
4. La Ley limitar&aacute; el uso de la inform&aacute;tica para garantizar el honor y la intimidad personal y familiar de los ciudadanos y el pleno ejercicio de sus derechos.</p>

<p>Declara dar su consentimiento expreso a que los datos recogidos en esta ficha ser&aacute;n incorporados a un fichero inform&aacute;tico cuyo titular es la ASOCIACI&Oacute;N, siendo tratados con la debida confidencialidad y reserva apropiadas, conforme a la Ley Org&aacute;nica 15/1999, de 13 de diciembre, de Protecci&oacute;n de Datos de Car&aacute;cter Personal, para su exclusiva utilizaci&oacute;n con fines de gesti&oacute;n interna de la asociaci&oacute;n. Usted podr&aacute; ejercer los derechos de acceso, rectificaci&oacute;n y cancelaci&oacute;n de sus datos mediante comunicaci&oacute;n escrita dirigida a cualquier delegaci&oacute;n de la Asociaci&oacute;n.</p>
<br />

<h1>Solicitud de participaci&oacute;n en el Programa de Autoabastecimiento y Acceso al Cannabis para el consumo individual privado de los socios</h1>

<p>Por la presente Juro o Prometo ser mayor de 21 a&ntilde;os, ser consumidor habitual de Cannabis Sativa L. y sus derivados y solicito participar en este programa con el resto de los miembros de la Asociaci&oacute;n participantes, asumiendo mi co-responsabilidad en las actividades necesarias para ello junto al 
resto de personas participantes en el mismo.
<br />Solicito participar en la cantidad de <input type="text" name="consumoPrevio" class="twoDigit" /> gr. de Cannabis al mes, con un m&aacute;ximo de 90 gramos mensuales en aras de mantener un consumo responsable y no problem&aacute;tico ni abusivo, con un m&aacute;ximo de  retirada de 3 gramos diarios.

<p>Compromisos del solicitante:</p>
<ul class="normallist">
 <li>Destinar el cannabis y sus derivados obtenidos por medio de este programa &uacute;nica y exclusivamente a mi consumo personal, exclusivo y excluyente, en el &aacute;mbito privado del local social, asumiendo mi responsabilidad en caso de sacarlo del mismo.</li>
 <li>Notificar el uso de cualquier medicaci&oacute;n que pueda interactuar con los cannabinoides para poder ser evaluado por el m&eacute;dico colaborador.</li>
 <li>Para garantizar un consumo responsable, la participaci&oacute;n de cada socio est&aacute; limitada en la cantidad de 90 gramos mensuales a raz&oacute;n de  un m&aacute;ximo de retirada de 3 gramos diarios. </li>
 <li>Comunicar a la Asociaci&oacute;n la notificaci&oacute;n de sanci&oacute;n en base a la L.O. de Seguridad Ciudadana, o la imputaci&oacute;n del delito tipificado en el art&iacute;culo 368 del C&oacute;digo Penal. La persona socia asume la total responsabilidad derivada de sus actos contrarios a la Ley y a  los compromisos adquiridos con la asociaci&oacute;n, eximiendo de cualquier responsabilidad a la Asociaci&oacute;n, asociados y junta directiva. </li>
 <li>Cualquier incumplimiento de los compromisos adquiridos mediante la firma del presente implicar&aacute; la p&eacute;rdida inmediata de la condici&oacute;n de socio mediante el correspondiente procedimiento fijado en los estatutos, sin derecho a recuperar las cuotas o pagos realizados hasta ese momento.</li>
 <li>La persona Socia se obliga a comunicar de inmediato, en el momento que decida abandonar su participaci&oacute;n en la Asociaci&oacute;n, la solicitud de baja de la Asociaci&oacute;n. </li>
 <li>Ser&aacute;n administrados cuestionarios sobre consumo responsable en el momento de la solicitud de admisi&oacute;n de socios y caso de no cumplir criterios de consumo responsable ser&aacute; denegada la solicitud.</li>
</ul>
<p>El Socio Avalador y resto de asociados informaran a la Asamblea o a la junta directiva si tienen conocimiento de que alg&uacute;n socio contraviene los compromisos adquiridos o normas de funcionamiento; con el fin de controlar el buen funcionamiento y, en su caso, activar expedientes de expulsi&oacute;n.</p>
<ul>
 <li>El solicitante manifiesta haber le&iacute;do la presente solicitud de participaci&oacute;n en el programa de acceso al c&aacute;nnabis para consumo individual privado, haberla entendido y se compromete a su estricta observancia, control y cumplimiento de forma responsable.</li>
</ul>

<p class="smallerfont">De acuerdo con lo establecido en la Ley Org&aacute;nica 15/1999 de protecci&oacute;n de datos de car&aacute;cter personal, le informamos que sus datos personales recogidos en este documento se incluir&aacute;n en un fichero automatizado bajo la responsabilidad de Asociaci&oacute;n, con la finalidad de poder gestionar las condiciones y ventajas de ser socio. Puede ejercer sus derechos de acceso, cancelaci&oacute;n, rectificaci&oacute;n y oposici&oacute;n mediante un escrito en nuestra direcci&oacute;n, Calle Sa Casa Vermella, Edificio Los Girasoles, Bloque 1, Local 2a Eivissa. Mientras no nos comunique lo contrario, entenderemos que sus datos no han sido modificados y que se compromete a notificarnos cualquier variaci&oacute;n y que tenemos el consentimiento para utilizarlos a fin de poder fidelizar la relaci&oacute;n entre ambas partes. Tambi&eacute;n solicitamos su consentimiento para tratar aquellos datos relacionados con su salud cuando sea preciso debido a sus especiales necesidades, as&iacute; como cederlos a terceras entidades colaboradoras con fines terap&eacute;uticos. A partir de la firma del presente formulario usted autoriza expresamente el tratamiento de sus datos de car&aacute;cter personal, para la finalidad especificada, por parte de Asociaci&oacute;n.</p>
<br />
<center>
<h1><?php echo $lang['birthdate']; ?></h1>
   <input type="number" lang="nb" name="day" class="twoDigit" maxlength="2" placeholder="dd" value="<?php echo $day; ?>" />
   <input type="number" lang="nb" name="month" class="twoDigit" maxlength="2" placeholder="mm" value="<?php echo $month; ?>" />
   <input type="number" lang="nb" name="year" class="fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" /><br /><br />
</center>

<center><strong>Your signature:</strong><br />
<div id="signatureSet">
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
</center><br />
<center><span id="errorBox"></span></center><br />

	 <button name='oneClick' class='oneClick' type="submit">Submit</button><br />
	</form>



<?php displayFooter(); ?>
