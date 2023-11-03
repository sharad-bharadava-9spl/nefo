<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-contract.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();

	// Get the user ID
	if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}

	// Query to look up user
	$userDetails = "SELECT memberno, registeredSince, first_name, last_name, email, day, month, year, nationality, gender, dni, street, streetnumber, flat, postcode, city, country, telephone, mconsumption, usageType, signupsource, cardid, photoid, docid, doorAccess, friend, friend2, paidUntil, adminComment, registeredSince, photoext, dniext1, dniext2 FROM users WHERE user_id = $user_id";
	
	$result = mysql_query($userDetails)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());

	$row = mysql_fetch_array($result);
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
		
		
		
		// Query to look up user
		$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = '$friend'";
		
		$result = mysql_query($userDetails)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$avalNo = $row['memberno'];
			$avalFN = $row['first_name'];
			$avalLN = $row['last_name'];
			
$pattern = array("'é'", "'è'", "'ë'", "'ê'", "'É'", "'È'", "'Ë'", "'Ê'", "'á'", "'à'", "'ä'", "'â'", "'å'", "'Á'", "'À'", "'Ä'", "'Â'", "'Å'", "'ó'", "'ò'", "'ö'", "'ô'", "'Ó'", "'Ò'", "'Ö'", "'Ô'", "'í'", "'ì'", "'ï'", "'î'", "'Í'", "'Ì'", "'Ï'", "'Î'", "'ú'", "'ù'", "'ü'", "'û'", "'Ú'", "'Ù'", "'Ü'", "'Û'", "'ý'", "'ÿ'", "'Ý'", "'ø'", "'Ø'", "'œ'", "'Œ'", "'Æ'", "'ç'", "'Ç'", "'ñ'", "'Ñ'");
$replace = array('&eacute;', 'e', 'e', 'e', 'E', 'E', 'E', 'E', '&aacute;', 'a', 'a', 'a', 'a', '&Aacute;', 'A', 'A', 'A', 'A', '&oacute;', 'o', 'o', 'o', '&Oacute;', 'O', 'O', 'O', '&iacute;', 'i', 'i', '&Iacute;', 'I', 'I', 'I', 'I', '&uacute;', 'u', 'u', 'u', '&Uacute;', 'U', 'U', 'U', 'y', 'y', 'Y', 'o', 'O', 'a', 'A', 'A', 'c', 'C', '&ntilde;', '&Ntilde;'); 

$avalFN = preg_replace($pattern, $replace, $avalFN);
$avalLN = preg_replace($pattern, $replace, $avalLN);
$first_name = preg_replace($pattern, $replace, $first_name);
$last_name = preg_replace($pattern, $replace, $last_name);
$nationality = preg_replace($pattern, $replace, $nationality);
$country = preg_replace($pattern, $replace, $country);
$street = preg_replace($pattern, $replace, $street);
	
		if ($usageType = 'Medicinal') {
			
			$userTipo = "Terapeutico";
			
		} else {
			
			$userTipo = "Ludico";
			
		}
	echo "<center><h1 style='color: black;'>SOLICITUD DE ADMISION DE NUEVO SOCIO</h1></center>";
	
	pageStart("Contrato de socio", NULL, $deleteNoteScript, "contract", NULL, "Contrato de socio", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>
<center><h1 style='color: black;'>ASOCIACION CRYSTAL CANNA CLUB<br />
C/ Embajadores 93, 28045, Madrid</h1></center>
<center><a href='genpdf.php' class='cta'>Generar PDF</a></center>
<center><img class="profilepic" src="images/members/<?php echo $user_id . "." . $photoext;?>"  width="320" /></center>
<?php echo <<<EOD
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
<p>Mayor de edad, bajo promesa o juramento de decir verdad por la presente <strong>DECLARA:</strong></p>
<ol type="I">
 <li>Ser usuario/a  consumidor/a habitual de cannabis sativa, as&iacute; como de otras plantas, como el tabaco y sus derivados. O haber sido diagnosticado/a de alguna enfermedad para la cual la eficacia del uso terap&eacute;utico o paliativo de los cannabinoides ha sido probada cient&iacute;ficamente. </li>
 <li>Su voluntad de  entrar a formar parte de la "ASOCIACION CRYSTAL CANNA CLUB" de forma libre. Conociendo los estatutos, fines y objetivos de &eacute;sta.</li>
 <li>Su compromiso personal de no vender ni regalar el cannabis que la "ASOCIACION CRYSTAL CANNA CLUB" le proporcione, total o parcialmente, por ser la venta una actividad il&iacute;cita y penada por la ley, bajo sanci&oacute;n de expulsi&oacute;n de la Asociaci&oacute;n dada su consideraci&oacute;n como falta muy grave.</li>
 <li>Su compromiso de cumplir sus Estatutos y el Reglamento de R&eacute;gimen Interno, observar sus fines sociales y respetar y cumplir las decisiones de sus &oacute;rganos de gobierno.</li>
 <li>Ser mayor de 21 a&ntilde;os.</li>
 <li>Su compromiso de cumplir con la legislaci&oacute;n espa&ntilde;ola y comunitaria. Teniendo en especial consideraci&oacute;n los siguientes preceptos:</li>
</ol>
<br /><p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <u>En la Constituci&oacute;n Espa&ntilde;ola, 1978.</u>
<em><p>"Art&iacute;culo 18.</p>
<p>1. Se garantiza el derecho al honor, a la intimidad personal y familiar y a la propia imagen.</p>
<p>2. El domicilio es inviolable. Ninguna entrada o registro podr&aacute; hacerse en &eacute;l sin consentimiento del titular o resoluci&oacute;n judicial, salvo en caso de flagrante delito.</p>
<p>3. Se garantiza el secreto de las comunicaciones y, en especial, de las postales, telegr&aacute;ficas y telef&oacute;nicas, salvo resoluci&oacute;n judicial.</p>
<p>4. La ley limitar&aacute; el uso de la inform&aacute;tica para garantizar el honor y la intimidad personal y familiar de los ciudadanos y el pleno ejercicio de sus derechos."</p></em>


<br /><p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <u>L.O. 10/1995, de 23 de noviembre, del C&oacute;digo Penal ("B.O.E." 23 junio).</u>
<em><p>"Art&iacute;culo 368.</p>
<p>Los que ejecuten actos de cultivo, elaboraci&oacute;n o tr&aacute;fico, o de otro modo promuevan, favorezcan o faciliten el consumo ilegal de drogas t&oacute;xicas, estupefacientes o sustancias psicotr&oacute;picas, o las posean con aquellos fines, ser&aacute;n castigados con las penas de prisi&oacute;n de tres a seis a&ntilde;os y multa del tanto al triplo del valor de la droga objeto del delito si se tratare de sustancias o productos que causen grave da&ntilde;o a la salud, y de prisi&oacute;n de uno a tres a&ntilde;os y multa del tanto al duplo en los dem&aacute;s casos.</p>
<p>No obstante lo dispuesto en el p&aacute;rrafo anterior, los tribunales podr&aacute;n imponer la pena inferior en grado a las se&ntilde;aladas en atenci&oacute;n a la escasa entidad del hecho y a las circunstancias personales del culpable. No se podr&aacute; hacer uso de esta facultad si concurriere alguna de las circunstancias a que se hace referencia en los art&iacute;culos 369 bis y 370."</p>
<p>El Art&iacute;culo 368 redactado por el apartado cent&eacute;simo cuarto del art&iacute;culo &uacute;nico de L.O. 5/2010, de 22 de junio, por la que se modifica la L.O. 10/1995, de 23 de noviembre, del C&oacute;digo Penal ("B.O.E." 23 junio).</p></em>

<br /><p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <u>Ley Org&aacute;nica 1/1992, de 21 de febrero, sobre Protecci&oacute;n de la Seguridad Ciudadana.</u>
<em><p>"Art&iacute;culo 19.</p>
<p>1. Los agentes de las Fuerzas y Cuerpos de Seguridad podr&aacute;n limitar o restringir, por el tiempo imprescindible, la circulaci&oacute;n o permanencia en v&iacute;as o lugares p&uacute;blicos en supuestos de alteraci&oacute;n del orden, la seguridad ciudadana o la pac&iacute;fica convivencia, cuando fuere necesario para su restablecimiento. Asimismo, podr&aacute;n ocupar preventivamente los efectos o instrumentos susceptibles de ser utilizados para acciones ilegales, d&aacute;ndoles el destino que legalmente proceda.</p>
<p>2. Para el descubrimiento y detenci&oacute;n de los part&iacute;cipes en un hecho delictivo causante de grave alarma social y para la recogida de los instrumentos, efectos o pruebas del mismo, se podr&aacute;n establecer controles en las v&iacute;as, lugares o establecimientos p&uacute;blicos, en la medida indispensable a los fines de este apartado, al objeto de proceder a la identificaci&oacute;n de las personas que transiten o se encuentren en ellos, al registro de los veh&iacute;culos y al control superficial de los efectos personales con el fin de comprobar que no se portan sustancias o instrumentos prohibidos o peligrosos. El resultado de la diligencia se pondr&aacute; de inmediato en conocimiento del Ministerio Fiscal."</p></em>

<br /><p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <u>Ley Org&aacute;nica 1/1992, de 21 de febrero, sobre Protecci&oacute;n de la Seguridad Ciudadana.</u>
<em><p>"Art&iacute;culo 25.</p>
<p>1. Constituyen infracciones graves a la seguridad ciudadana el consumo en lugares, v&iacute;as, establecimientos o transportes p&uacute;blicos, as&iacute; como la tenencia il&iacute;cita, aunque no estuviera destinada al tr&aacute;fico, de drogas t&oacute;xicas, estupefacientes o sustancias psicotr&oacute;picas, siempre que no constituya infracci&oacute;n penal, as&iacute; como el abandono en los sitios mencionados de &uacute;tiles o instrumentos utilizados para su consumo.</p>
<p>2. Las sanciones impuestas por estas infracciones podr&aacute;n suspenderse si el infractor se somete a un tratamiento de deshabituaci&oacute;n en un centro o servicio debidamente acreditado, en la forma y por el tiempo que reglamentariamente se determine."</p></em>

<p>El socio se compromete y responsabiliza a dedicar el cannabis retirado de la Asociaci&oacute;n &Uacute;NICA Y EXCLUSIVAMENTE A SU CONSUMO PERSONAL, as&iacute; como consumirlo &uacute;nica y exclusivamente dentro de la sede asociativa, asumiendo cualquier responsabilidad de sus actos contrarios a la Ley que se pudieran derivar y eximiendo de ello a la Asociaci&oacute;n, Asociados y Junta Directiva. La Junta Directiva asume todas las responsabilidades derivadas de la gesti&oacute;n y administraci&oacute;n de la Asociaci&oacute;n.</p>
<p>Para este efecto adjunta el patrocinio/aval de UN SOCIO de la Asociaci&oacute;n a la presente solicitud que manifiesta ser conocedor del consumo de cannabis por parte de la persona que solicita su incorporaci&oacute;n a la Asociaci&oacute;n. Cualquier incumplimiento del presente compromiso implicar&aacute; la expulsi&oacute;n de la Asociaci&oacute;n mediante el correspondiente procedimiento fijado en los estatutos.</p>
<p>El socio autoriza a los socios activos colaboradores o fundadores de la Asociaci&oacute;n a recoger y repartir entre los socios su parte de lo comprado mancomunadamente.</p>
<p>El socio se obliga a comunicar de inmediato, en el momento que decida abandonar su participaci&oacute;n en la Asociaci&oacute;n, la solicitud de baja de la Asociaci&oacute;n.</p>
<p>El socio comunicar&aacute; a la Asociaci&oacute;n la notificaci&oacute;n de sanci&oacute;n en base al art&iacute;culo 25.1 de la Ley Org&aacute;nica 1/1992, sobre Protecci&oacute;n Ciudadana, o la imputaci&oacute;n del delito tipificado en el art&iacute;culo 368 del C&oacute;digo Penal, pudiendo implicar en el primer caso e implicando en el segundo la expulsi&oacute;n del socio.</p>
<p>El socio se compromete a no ceder su carnet de socio, el cual es intransferible. La transferencia del carnet de socio ser&aacute; motivo de expulsi&oacute;n de la asociaci&oacute;n y siempre exhibir&aacute; su carnet de identidad y el carnet de socio para retirar su material.</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;El Socio manifiesta haber le&iacute;do los Estatutos, el Dosier Explicativo de Funcionamiento y Objetivos, el Reglamento de R&eacute;gimen Interno, as&iacute; como, la presente Solicitud de Ingreso.</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;El socio se compromete un uso responsable de los servicios e instalaciones de la asociaci&oacute;n as&iacute; como de los objetos pertenecientes a esta.</p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cualquier incumplimiento de los compromisos adquiridos mediante la firma del presente documento, implicar&aacute; para el socio  su expulsi&oacute;n de la Asociaci&oacute;n mediante el correspondiente procedimiento fijado en los estatutos.</p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;El solicitante manifieste haber le&iacute;do los estatutos, el reglamento de r&eacute;gimen interno y la presente solicitud de admisi&oacute;n como socio de la "ASOCIACION CRYSTAL CANNA CLUB"</p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;El Socio Avalista se responsabiliza de la actitud de su avalado respecto al cumplimiento de todos los puntos anteriores, adem&aacute;s garantiza el que su avalado ya es consumidor de Cannabis previamente a la firma de esta solicitud.</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A) SOCIO L&Uacute;DICO:</p>
<p>Aporta los datos personales as&iacute; como la firma del socio que avala su condici&oacute;n de previo consumidor de las sustancias mencionadas en los estatutos y en este documento de solicitud de admisi&oacute;n.</p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;B) SOCIO TERAP&Eacute;UTICO:</p>
<p>Muestra la documentaci&oacute;n original en la que se acredita su condici&oacute;n de socio terap&eacute;utico. Aportando un certificado m&eacute;dico original acreditativo de la solicitud de uso de Cannabinoides, THC, CBD, CBN y/o otras plantas medicinales por parte del paciente, sin oposici&oacute;n m&eacute;dica.</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;O bien exhibiendo la licencia de uso de cannabis medicinal de cualquier pa&iacute;s del mundo.</p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;El socio/a declara consumir la cantidad indicada al comienzo del contrato de manera mensual. Cantidad que deber&aacute; ser revisada o confirmada por el socio/a a cada trimestre. En caso de no confirmar o revisar se prorrogar&aacute; o ajustar&aacute; por parte de la asociaci&oacute;n hacia una  cantidad conforme a la realidad del usuario y siempre inferior a la cantidad inicial de este, abogando as&iacute; por una reducci&oacute;n de riesgos. La Asociaci&oacute;n siempre entregar&aacute; cantidades inferiores al consumo manifestado por los socios/as con el fin de concienciar hacia un consumo responsable.</p>
<br />
<center>
<table width="90%" align="center">
 <tr>
  <td width="50%" align="center">FIRMA DEL AVALISTA:</td>
  <td width="50%" align="center">FIRMA DEL INTERESADO:</td>
 </tr>
 <tr>
  <td width="50%" align="center"><img src="images/sigs/<?php echo $friend; ?>.png" width="500" /></td>
  <td width="50%" align="center"><img src="images/sigs/<?php echo $user_id; ?>.png" width="500" /></td>
 </tr>
</table>
</center>
<br /><br /><br />
<center><h1 style='color: black;'>INFORMACI&Oacute;N PARA LA JUNTA DIRECTIVA</h1></center>

<hr />

<p>ACEPTACION DE LA JUNTA DIRECTIVA</p>
<p>FECHA DE INGRESO: <?php echo $insDate; ?></p>
<p>LA FIRMA DEL DOCUMENTO ACEPTA LA PRESENTE SOLICITUD:</p>
<br /><br /><br /><br /><br /><br /><br /><br /><br />

<hr />
<p class="smallerfont">Los datos recogidos en esta ficha ser&aacute;n incorporados a un fichero inform&aacute;tico, siendo tratados con la debida confidencialidad y reserva apropiadas, conforme a la Ley Org&aacute;nica 15/1999, de 13 de diciembre, de Protecci&oacute;n de Datos de Car&aacute;cter Personal, para su exclusiva utilizaci&oacute;n con fines de gesti&oacute;n interna de la asociaci&oacute;n. Usted podr&aacute; ejercer los derechos de acceso, rectificaci&oacute;n y cancelaci&oacute;n de sus datos mediante comunicaci&oacute;n escrita dirigida a cualquier delegaci&oacute;n de la "ASOCIACION CRYSTAL CANNA CLUB", para nuestra asociaci&oacute;n tanto la seguridad de sus datos personales como su intimidad son absolutamente prioritarias, sus datos estar&aacute;n guardados siempre de forma privada, segura y confidencial y no ser&aacute;n revelados a ninguna persona, entidad o autoridad a no ser que una resoluci&oacute;n judicial as&iacute; dice lo disponga.</p>


<?php

// Create all HTML manually, i.e. no View!



$insDate = date("d/m/Y H:i", strtotime($registeredSince . "+$offsetSec seconds"));

$_SESSION['contr1'] = <<<EOD

<table width="100%" align="center">
 <tr>
  <td align="center"><h2 style='color: black;'>SOLICITUD DE ADMISION DE NUEVO SOCIO</h2></td>
 </tr>
 <tr>
  <td align="center">&nbsp;<br /><img src="images/logo.png" /><br />&nbsp;</td>
 </tr>
 <tr>
  <td align="center"><h2 style='color: black;'>ASOCIACION CRYSTAL CANNA CLUB<br />C/ Embajadores 93, 28045, Madrid</h2></td>
 </tr>
EOD;

	$fileImg = 'images/members/' . $user_id . '.' . $photoext;

	if (file_exists($fileImg)) {
		
 $_SESSION['contr1'] .= <<<EOD
 <tr>
  <td align="center">&nbsp;<br /><img class="profilepic" src="images/members/{$user_id}.{$photoext}" width="320" /><br />&nbsp;</td>
 </tr>
EOD;

	}
	
 $_SESSION['contr1'] .= <<<EOD
</table>
 <table cellspacing="10">
  <tr>
   <td><strong>NOMBRE COMPLETO:</strong></td>
   <td>{$first_name} {$last_name}</td>
  </tr>
  <tr>
   <td><strong>DNI/NIE:</strong></td>
   <td>{$dni}</td>
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
<br />
<hr style='width: 95%' />
<p>Mayor de edad, bajo promesa o juramento de decir verdad por la presente <strong>DECLARA:</strong></p>
<ol type="I">
 <li>Ser usuario/a  consumidor/a habitual de cannabis sativa, as&iacute; como de otras plantas, como el tabaco y sus derivados. O haber sido diagnosticado/a de alguna enfermedad para la cual la eficacia del uso terap&eacute;utico o paliativo de los cannabinoides ha sido probada cient&iacute;ficamente.<br />&nbsp;</li>
 <li>Su voluntad de  entrar a formar parte de la "ASOCIACION CRYSTAL CANNA CLUB" de forma libre. Conociendo los estatutos, fines y objetivos de &eacute;sta.<br />&nbsp;</li>
 <li>Su compromiso personal de no vender ni regalar el cannabis que la "ASOCIACION CRYSTAL CANNA CLUB" le proporcione, total o parcialmente, por ser la venta una actividad il&iacute;cita y penada por la ley, bajo sanci&oacute;n de expulsi&oacute;n de la Asociaci&oacute;n dada su consideraci&oacute;n como falta muy grave.<br />&nbsp;</li>
 <li>Su compromiso de cumplir sus Estatutos y el Reglamento de R&eacute;gimen Interno, observar sus fines sociales y respetar y cumplir las decisiones de sus &oacute;rganos de gobierno.<br />&nbsp;</li>
 <li>Ser mayor de 21 a&ntilde;os.<br />&nbsp;</li>
 <li>Su compromiso de cumplir con la legislaci&oacute;n espa&ntilde;ola y comunitaria. Teniendo en especial consideraci&oacute;n los siguientes preceptos:</li>
</ol>
<br /><p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <u>En la Constituci&oacute;n Espa&ntilde;ola, 1978.</u>
<em><p>"Art&iacute;culo 18.</p>
<p>1. Se garantiza el derecho al honor, a la intimidad personal y familiar y a la propia imagen.</p>
<p>2. El domicilio es inviolable. Ninguna entrada o registro podr&aacute; hacerse en &eacute;l sin consentimiento del titular o resoluci&oacute;n judicial, salvo en caso de flagrante delito.</p>
<p>3. Se garantiza el secreto de las comunicaciones y, en especial, de las postales, telegr&aacute;ficas y telef&oacute;nicas, salvo resoluci&oacute;n judicial.</p>
<p>4. La ley limitar&aacute; el uso de la inform&aacute;tica para garantizar el honor y la intimidad personal y familiar de los ciudadanos y el pleno ejercicio de sus derechos."</p></em>


<br /><p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <u>L.O. 10/1995, de 23 de noviembre, del C&oacute;digo Penal ("B.O.E." 23 junio).</u>
<em><p>"Art&iacute;culo 368.</p>
<p>Los que ejecuten actos de cultivo, elaboraci&oacute;n o tr&aacute;fico, o de otro modo promuevan, favorezcan o faciliten el consumo ilegal de drogas t&oacute;xicas, estupefacientes o sustancias psicotr&oacute;picas, o las posean con aquellos fines, ser&aacute;n castigados con las penas de prisi&oacute;n de tres a seis a&ntilde;os y multa del tanto al triplo del valor de la droga objeto del delito si se tratare de sustancias o productos que causen grave da&ntilde;o a la salud, y de prisi&oacute;n de uno a tres a&ntilde;os y multa del tanto al duplo en los dem&aacute;s casos.</p>
<p>No obstante lo dispuesto en el p&aacute;rrafo anterior, los tribunales podr&aacute;n imponer la pena inferior en grado a las se&ntilde;aladas en atenci&oacute;n a la escasa entidad del hecho y a las circunstancias personales del culpable. No se podr&aacute; hacer uso de esta facultad si concurriere alguna de las circunstancias a que se hace referencia en los art&iacute;culos 369 bis y 370."</p>
<p>El Art&iacute;culo 368 redactado por el apartado cent&eacute;simo cuarto del art&iacute;culo &uacute;nico de L.O. 5/2010, de 22 de junio, por la que se modifica la L.O. 10/1995, de 23 de noviembre, del C&oacute;digo Penal ("B.O.E." 23 junio).</p></em>

<br /><p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <u>Ley Org&aacute;nica 1/1992, de 21 de febrero, sobre Protecci&oacute;n de la Seguridad Ciudadana.</u>
<em><p>"Art&iacute;culo 19.</p>
<p>1. Los agentes de las Fuerzas y Cuerpos de Seguridad podr&aacute;n limitar o restringir, por el tiempo imprescindible, la circulaci&oacute;n o permanencia en v&iacute;as o lugares p&uacute;blicos en supuestos de alteraci&oacute;n del orden, la seguridad ciudadana o la pac&iacute;fica convivencia, cuando fuere necesario para su restablecimiento. Asimismo, podr&aacute;n ocupar preventivamente los efectos o instrumentos susceptibles de ser utilizados para acciones ilegales, d&aacute;ndoles el destino que legalmente proceda.</p>
<p>2. Para el descubrimiento y detenci&oacute;n de los part&iacute;cipes en un hecho delictivo causante de grave alarma social y para la recogida de los instrumentos, efectos o pruebas del mismo, se podr&aacute;n establecer controles en las v&iacute;as, lugares o establecimientos p&uacute;blicos, en la medida indispensable a los fines de este apartado, al objeto de proceder a la identificaci&oacute;n de las personas que transiten o se encuentren en ellos, al registro de los veh&iacute;culos y al control superficial de los efectos personales con el fin de comprobar que no se portan sustancias o instrumentos prohibidos o peligrosos. El resultado de la diligencia se pondr&aacute; de inmediato en conocimiento del Ministerio Fiscal."</p></em>

<br /><p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <u>Ley Org&aacute;nica 1/1992, de 21 de febrero, sobre Protecci&oacute;n de la Seguridad Ciudadana.</u>
<em><p>"Art&iacute;culo 25.</p>
<p>1. Constituyen infracciones graves a la seguridad ciudadana el consumo en lugares, v&iacute;as, establecimientos o transportes p&uacute;blicos, as&iacute; como la tenencia il&iacute;cita, aunque no estuviera destinada al tr&aacute;fico, de drogas t&oacute;xicas, estupefacientes o sustancias psicotr&oacute;picas, siempre que no constituya infracci&oacute;n penal, as&iacute; como el abandono en los sitios mencionados de &uacute;tiles o instrumentos utilizados para su consumo.</p>
<p>2. Las sanciones impuestas por estas infracciones podr&aacute;n suspenderse si el infractor se somete a un tratamiento de deshabituaci&oacute;n en un centro o servicio debidamente acreditado, en la forma y por el tiempo que reglamentariamente se determine."</p></em>

<p>El socio se compromete y responsabiliza a dedicar el cannabis retirado de la Asociaci&oacute;n &Uacute;NICA Y EXCLUSIVAMENTE A SU CONSUMO PERSONAL, as&iacute; como consumirlo &uacute;nica y exclusivamente dentro de la sede asociativa, asumiendo cualquier responsabilidad de sus actos contrarios a la Ley que se pudieran derivar y eximiendo de ello a la Asociaci&oacute;n, Asociados y Junta Directiva. La Junta Directiva asume todas las responsabilidades derivadas de la gesti&oacute;n y administraci&oacute;n de la Asociaci&oacute;n.</p>
<p>Para este efecto adjunta el patrocinio/aval de UN SOCIO de la Asociaci&oacute;n a la presente solicitud que manifiesta ser conocedor del consumo de cannabis por parte de la persona que solicita su incorporaci&oacute;n a la Asociaci&oacute;n. Cualquier incumplimiento del presente compromiso implicar&aacute; la expulsi&oacute;n de la Asociaci&oacute;n mediante el correspondiente procedimiento fijado en los estatutos.</p>
<p>El socio autoriza a los socios activos colaboradores o fundadores de la Asociaci&oacute;n a recoger y repartir entre los socios su parte de lo comprado mancomunadamente.</p>
<p>El socio se obliga a comunicar de inmediato, en el momento que decida abandonar su participaci&oacute;n en la Asociaci&oacute;n, la solicitud de baja de la Asociaci&oacute;n.</p>
<p>El socio comunicar&aacute; a la Asociaci&oacute;n la notificaci&oacute;n de sanci&oacute;n en base al art&iacute;culo 25.1 de la Ley Org&aacute;nica 1/1992, sobre Protecci&oacute;n Ciudadana, o la imputaci&oacute;n del delito tipificado en el art&iacute;culo 368 del C&oacute;digo Penal, pudiendo implicar en el primer caso e implicando en el segundo la expulsi&oacute;n del socio.</p>
<p>El socio se compromete a no ceder su carnet de socio, el cual es intransferible. La transferencia del carnet de socio ser&aacute; motivo de expulsi&oacute;n de la asociaci&oacute;n y siempre exhibir&aacute; su carnet de identidad y el carnet de socio para retirar su material.</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;El Socio manifiesta haber le&iacute;do los Estatutos, el Dosier Explicativo de Funcionamiento y Objetivos, el Reglamento de R&eacute;gimen Interno, as&iacute; como, la presente Solicitud de Ingreso.</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;El socio se compromete un uso responsable de los servicios e instalaciones de la asociaci&oacute;n as&iacute; como de los objetos pertenecientes a esta.</p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cualquier incumplimiento de los compromisos adquiridos mediante la firma del presente documento, implicar&aacute; para el socio  su expulsi&oacute;n de la Asociaci&oacute;n mediante el correspondiente procedimiento fijado en los estatutos.</p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;El solicitante manifieste haber le&iacute;do los estatutos, el reglamento de r&eacute;gimen interno y la presente solicitud de admisi&oacute;n como socio de la "ASOCIACION CRYSTAL CANNA CLUB"</p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;El Socio Avalista se responsabiliza de la actitud de su avalado respecto al cumplimiento de todos los puntos anteriores, adem&aacute;s garantiza el que su avalado ya es consumidor de Cannabis previamente a la firma de esta solicitud.</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A) SOCIO L&Uacute;DICO:</p>
<p>Aporta los datos personales as&iacute; como la firma del socio que avala su condici&oacute;n de previo consumidor de las sustancias mencionadas en los estatutos y en este documento de solicitud de admisi&oacute;n.</p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;B) SOCIO TERAP&Eacute;UTICO:</p>
<p>Muestra la documentaci&oacute;n original en la que se acredita su condici&oacute;n de socio terap&eacute;utico. Aportando un certificado m&eacute;dico original acreditativo de la solicitud de uso de Cannabinoides, THC, CBD, CBN y/o otras plantas medicinales por parte del paciente, sin oposici&oacute;n m&eacute;dica.</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;O bien exhibiendo la licencia de uso de cannabis medicinal de cualquier pa&iacute;s del mundo.</p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;El socio/a declara consumir la cantidad indicada al comienzo del contrato de manera mensual. Cantidad que deber&aacute; ser revisada o confirmada por el socio/a a cada trimestre. En caso de no confirmar o revisar se prorrogar&aacute; o ajustar&aacute; por parte de la asociaci&oacute;n hacia una  cantidad conforme a la realidad del usuario y siempre inferior a la cantidad inicial de este, abogando as&iacute; por una reducci&oacute;n de riesgos. La Asociaci&oacute;n siempre entregar&aacute; cantidades inferiores al consumo manifestado por los socios/as con el fin de concienciar hacia un consumo responsable.</p>
<br />
<table width="90%" align="center">
 <tr>
  <td width="50%" align="center">FIRMA DEL AVALISTA:</td>
  <td width="50%" align="center">FIRMA DEL INTERESADO:</td>
 </tr>
EOD;

	$fileImg3 = 'images/sigs/' . $friend . '.png';
	
	if (file_exists($fileImg3)) {
		
 $_SESSION['contr1'] .= <<<EOD
 <tr>
  <td width="50%" align="center"><img src="images/sigs/{$friend}.png" width="500" /></td>
  <td width="50%" align="center"><img src="images/sigs/{$user_id}.png" width="500" /></td>
 </tr>
EOD;

	} else {
		
 $_SESSION['contr1'] .= <<<EOD
 <tr>
  <td width="50%" align="center"></td>
  <td width="50%" align="center"><img src="images/sigs/{$user_id}.png" width="500" /></td>
 </tr>
EOD;
		
	}
	
 $_SESSION['contr1'] .= <<<EOD
</table>
</center>
<br /><br /><br />
<table width="100%" align="center">
 <tr>
  <td align="center"><h2 style='color: black;'>INFORMACI&Oacute;N PARA LA JUNTA DIRECTIVA</h2></td>
 </tr>
</table><br />
<hr />
<br />
<p>ACEPTACION DE LA JUNTA DIRECTIVA</p>
<p>FECHA DE INGRESO: {$insDate}</p>
<p>LA FIRMA DEL DOCUMENTO ACEPTA LA PRESENTE SOLICITUD:</p>
<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;

<hr />
<p class="smallerfont">Los datos recogidos en esta ficha ser&aacute;n incorporados a un fichero inform&aacute;tico, siendo tratados con la debida confidencialidad y reserva apropiadas, conforme a la Ley Org&aacute;nica 15/1999, de 13 de diciembre, de Protecci&oacute;n de Datos de Car&aacute;cter Personal, para su exclusiva utilizaci&oacute;n con fines de gesti&oacute;n interna de la asociaci&oacute;n. Usted podr&aacute; ejercer los derechos de acceso, rectificaci&oacute;n y cancelaci&oacute;n de sus datos mediante comunicaci&oacute;n escrita dirigida a cualquier delegaci&oacute;n de la "ASOCIACION CRYSTAL CANNA CLUB", para nuestra asociaci&oacute;n tanto la seguridad de sus datos personales como su intimidad son absolutamente prioritarias, sus datos estar&aacute;n guardados siempre de forma privada, segura y confidencial y no ser&aacute;n revelados a ninguna persona, entidad o autoridad a no ser que una resoluci&oacute;n judicial as&iacute; dice lo disponga.</p>


EOD;

 displayFooter();
