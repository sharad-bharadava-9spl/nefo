<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	$id = $_GET['user_id'];
	$number = $_GET['number'];
	
	$query = "SELECT db_pwd, customer, warning, domain FROM db_access WHERE customer = '$number'";
	try
	{
		$result = $pdo->prepare("$query");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user1: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	if ($data) {

		$row = $data[0];
			$db_pwd = $row['db_pwd'];
			$customer = $row['customer'];
			$warning = $row['warning'];
			$domain = $row['domain'];

		$db_name = "ccs_" . $domain;
		$db_user = $db_name . "u";

		try	{
	 		$pdo6 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
	 		$pdo6->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$pdo6->exec('SET NAMES "utf8"');
		}
		catch (PDOException $e)	{
	  		$output = 'Unable to connect to the database server: ' . $e->getMessage();
	
	 		echo $output;
	 		exit();
		}
		
		$query = "SELECT * FROM contract";
		try
		{
			$result = $pdo6->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user2: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$cif = $row['cif'];		
			$name = $row['name'];		
			$dni = $row['dni'];		
			$club = $row['club'];		
			$address = $row['address'];		
			$box3 = $row['box3'];		
			$time = date("d/m/Y", strtotime($row['time']));
			$image = $row['image'];		
		  	 	 	 	 	 	 	
	} else {
		
		pageStart("Contrato", NULL, $validationScript, "pprofile", "statutes", "CCS Software Contract", $_SESSION['successMessage'], "Database not available!<br />Client might have been sunset.");
		
		exit();
		
	}


	
	pageStart("Contrato", NULL, $validationScript, "pprofile", "statutes", "CCS Software Contract", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>

	
<div class="actionbox-np2" style='text-align: left;'>
	<div class='boxcontent'>

<p>En Madrid, a <?php echo $time; ?></p>
<br /><h3>REUNIDOS</h3>

<p>DE UNA  PARTE, <?php echo $name; ?> mayor de edad, con D.N.I. n&uacute;mero <?php echo $dni; ?> y en nombre y representaci&oacute;n de la <?php echo $club; ?>, en adelante, el "CLIENTE", domiciliada en <?php echo $address; ?>, <?php echo $cif; ?>.</p>
<p>DE OTRA PARTE, Bob Ahab Thornhill mayor de edad, con D.N.I. número Y0600489L y en nombre y representación de la mercantil Mykinlink S.L., en adelante, el "PROVEEDOR", domiciliada en Calle Esteban Collantes 22, 28017 Madrid, España y con CIF B87843504.</p>
<p>El CLIENTE y el PROVEEDOR, en adelante, podrán ser denominadas, individualmente, "la Parte" y, conjuntamente, "las Partes", reconociéndose mutuamente capacidad jurídica y de obrar suficiente para la celebración del presente Contrato.</p>

<br /><br />
<br /><h3>EXPONEN</h3>
<p><strong>PRIMERO:</strong> Que el CLIENTE está interesado en la compra de licencia de un programa de software.</p>
<p>El CLIENTE está interesado en contratar dichos servicios para utilizar en sus ordenadores el programa CCS (Cannabis Club Systems).</p>
<p><strong>SEGUNDO:</strong> Que el PROVEEDOR es una empresa especializada en la prestación de servicios de creación, desarrollo, distribución, actualización y mantenimiento de sistemas informáticos, Cloud computing, web y mail hosting.</p>
<p><strong>TERCERO:</strong> Que las Partes están interesadas en celebrar un contrato de licencia de uso en virtud del cual el PROVEEDOR licencie al CLIENTE para el uso del programa informático denominado CCS (Cannabis Club Systems).</p>
<p>Que las Partes reunidas en la sede social del CLIENTE, acuerdan celebrar el presente contrato de LICENCIA DE USO, en adelante, el "Contrato", de acuerdo con las siguientes cláusulas.</p>

<br /><h3>CLÁUSULAS</h3>

<br /><h3>PRIMERA.- OBJETO</h3>
<p>En virtud del Contrato el PROVEEDOR se obliga a ceder el uso, sin exclusiva al CLIENTE del programa de software CCS (Cannabis Club Systems).</p>
<p>El pago de la licencia otorgada bajo el presente contrato, no constituye la compra de los programas ni de los títulos, ni derechos de autor correspondientes.</p>

<br /><h3>SEGUNDA.- TÉRMINOS Y CONDICIONES GENERALES Y ESPECÍFICOS DE PRESTACIÓN DE LOS SERVICIOS</h3>
<p>2.1. Los Servicios se prestarán en los siguientes términos y condiciones generales:</p>
<p>2.1.1.1. El PROVEEDOR responderá de la calidad del trabajo desarrollado con la diligencia exigible a una empresa experta en la realización de los trabajos objeto del Contrato.</p>
<p>2.1.1.2. El PROVEEDOR se obliga a gestionar y obtener, a su cargo, todas las licencias, permisos y autorizaciones administrativas que pudieren ser necesarias para la realización de los Servicios. </p>
<p>2.1.1.3. El PROVEEDOR guardará confidencialidad sobre la información que le facilite el CLIENTE en o para la ejecución del Contrato o que por su propia naturaleza deba ser tratada como tal. Se excluye de la categoría de información confidencial toda aquella información que sea divulgada por el CLIENTE, aquella que haya de ser revelada de acuerdo con las leyes o con una resolución judicial o acto de autoridad competente. Este deber se mantendrá durante un plazo de tres años a contar desde la finalización del servicio.</p>
<p>2.1.1.4. En el caso de que la prestación de los Servicios suponga la necesidad de acceder a datos de carácter personal, el PROVEEDOR, como encargado del tratamiento, queda obligado al cumplimiento de la Ley 15/1999, de 13 de diciembre, de Protección de Datos de Carácter Personal y del Real Decreto 1720/2007, de 21 de diciembre, por el que se aprueba el Reglamento de desarrollo de la Ley Orgánica 15/1999 y demás normativa aplicable. </p>
<p>El PROVEEDOR responderá, por tanto, de las infracciones en que pudiera incurrir en el caso de que destine los datos personales a otra finalidad, los comunique a un tercero, o en general, los utilice de forma irregular, así como cuando no adopte las medidas correspondientes para el almacenamiento y custodia de los mismos. A tal efecto, se obliga a indemnizar al CLIENTE, por cualesquiera daños y perjuicios que sufra directamente, o por toda reclamación, acción o procedimiento, que traiga su causa de un incumplimiento o cumplimiento defectuoso por parte del PROVEEDOR de lo dispuesto tanto en el Contrato como lo dispuesto en la normativa reguladora de la protección de datos de carácter personal.</p>
<p>A los efectos del artículo 12 de la Ley 15/1999, el PROVEEDOR únicamente tratará los datos de carácter personal a los que tenga acceso conforme a las instrucciones del CLIENTE y no los aplicará o utilizará con un fin distinto al objeto del Contrato, ni los comunicará, ni siquiera para su conservación, a otras personas. En el caso de que el PROVEEDOR destine los datos a otra finalidad, los comunique o los utilice incumpliendo las estipulaciones del Contrato, será considerado también responsable del tratamiento, respondiendo de las infracciones en que hubiera incurrido personalmente. </p>
<p>El PROVEEDOR deberá adoptar las medidas de índole técnica y organizativas necesarias que garanticen la seguridad de los datos de carácter personal y eviten su alteración, pérdida, tratamiento o acceso no autorizado, habida cuenta del estado de la tecnología, la naturaleza de los datos almacenados y los riesgos a que están expuestos, ya provengan de la acción humana o del medio físico o natural. A estos efectos el PROVEEDOR deberá aplicar los niveles de seguridad que se establecen en el Real Decreto 1720/2007 de acuerdo a la naturaleza de los datos que trate.</p>
<p>2.1.1.5. El PROVEEDOR responderá de la corrección y precisión de los documentos que aporte al CLIENTE en ejecución del Contrato y avisará sin dilación al CLIENTE cuando detecte un error para que pueda adoptar las medidas y acciones correctoras que estime oportunas.</p>
<p>2.1.1.6. El PROVEEDOR responderá de los daños y perjuicios que se deriven para el CLIENTE y de las reclamaciones que pueda realizar un tercero, y que tengan su causa directa en errores del PROVEEDOR, o de su personal, en la ejecución del Contrato o que deriven de la falta de diligencia referida anteriormente. </p>
<p>2.1.1.7. Las obligaciones establecidas para el PROVEEDOR por la presente cláusula serán también de obligado cumplimiento para sus posibles empleados, colaboradores, tanto externos como internos, y subcontratistas, por lo que el PROVEEDOR responderá frente al CLIENTE si tales obligaciones son incumplidas por tales empleados.</p>
<p>2.2. El PROVEEDOR cederá la licencia de uso, no exclusiva y prestará los Servicios en los siguientes términos y condiciones específicos: </p>
<p>2.2.1.1. El PROVEEDOR licencia al CLIENTE el uso del programa descrito en cláusula 1ª de este contrato; Dicha licencia se entiende como Licencia personal, de carácter no exclusivo e intransferible. </p>
<p>2.2.1.2. La Propiedad Intelectual del programa informático licenciado, es única y exclusivamente del PROVEEDOR. Dicha propiedad intelectual abarca el programa informático, su código fuente y la estructura de su base de datos.</p>
<p>2.2.1.3. El CLIENTE reconoce que el programa se suministra tal y como se describe en el Objeto del contrato, cláusula 1ª y que el presente acuerdo le concede derecho sobre otras versiones, mejoras o modificaciones del mismo. </p>
<p>2.2.1.4. La licencia de uso incluye todos los conocimientos necesarios y adicionales al contenido del programa y sus especificaciones técnicas para que éste sea útil al CLIENTE. </p>
<p>2.2.1.5. La licencia de uso del programa se concede al CLENTE que será responsable de su custodia. El licenciatario es el propietario de los medios en los cuales haya almacenado el software, pero reconoce al PROVEEDOR  y sus cedentes de licencia la propiedad del software.</p>
<p>El PROVEEDOR  se reserva cualesquiera derechos no otorgados expresamente al licenciatario.</p>
<p>2.2.1.6. El CLIENTE una vez recibido el programa debe, dentro de los 10 días hábiles siguientes a la instalación de los programas, manifestar por escrito al PROVEEDOR la conformidad al programa y a las especificaciones Técnicas de funcionamiento. Si transcurrido dicho término, El CLIENTE no ha manifestado su conformidad al PROVEEDOR, se entender que los programas han sido aceptados. </p>
<p>2.2.1.7. El PROVEEDOR garantiza el buen estado de los soportes y de la nube en el soporte online.</p>
<p>2.2.1.8. El PROVEEDOR informará al CLIENTE de cualquier novedad, ampliación, etc., que se produzca en el programa, por si le interesase su instalación.</p>
<p>2.2.1.9. El PROVEEDOR ejecutará el Contrato realizando de manera competente y profesional los Servicios, cumpliendo los niveles de calidad exigidos y cuidando diligentemente los materiales del CLIENTE que tuviera que utilizar como consecuencia del Contrato. </p>
<p>2.3 Queda prohibida la transferencia o cesión total o parcial del presente contrato sin mediar el consentimiento previo por parte del PROVEEDOR.</p>

<br /><h3>TERCERA.- POLÍTICA DE USO</h3>
<p>3.1 El CLIENTE es el único responsable de la selección del programa de software y de los servicios que constituyen el objeto de este Contrato y que éstos se ajustan a sus necesidades, así como de la instalación y utilización del programa. </p>
<p>3.2 En ningún caso será el PROVEEDOR del programa responsable, ante el usuario o terceras partes, de cualquier daño, incluida pérdida de beneficios, pérdida de ahorro o cualquier tipo de perjuicio surgido como consecuencia de su utilización, siendo el uso del programa a riesgo y ventura del CLIENTE.</p>
<p>3.3 El CLIENTE se compromete a no divulgar la información que le haya sido proporcionada por el PROVEEDOR y que sea identificada por éste como "Confidencial", a excepción de aquella información que sea de dominio público.</p>
<p>3.4 El PROVEEDOR excluye cualquier responsabilidad de cualquier tipo en el acceso a los contenidos facilitados a sus usuarios a través de sus servicios que sean contrarios a la Ley, la moral y al Orden Público, que infrinjan derechos de propiedad intelectual, industrial o contengan cualquier vicio defecto o virus informático o rutina de software similar.</p>
<p>El PROVEEDOR no tiene conocimiento de que las páginas a las que se pueda acceder a través de sus servicios tengan un contenido contario a la Ley ,a la moral o al orden público , que infrinjan cualquier vicio , defecto , virus informático o rutina de Software similar.</p>
<p>3.5 El PROVEEDOR   eliminará el servicio en un plazo de 24 horas  en el caso de que el hecho al que se refiere la cláusula 3.4 llegare a su conocimiento.</p>
<p>3.6 El PROVEEDOR no se responsabiliza por la exactitud, verosimilitud o idoneidad profesional de los datos publicados, así como de los no publicados u ocultos por El CLIENTE y demás  usuarios de su software  y de la utilización que se les dé a los mismos, no asumiendo responsabilidad alguna en relación a la mala utilización de este software.</p>

<br /><h3>CUARTA.- PRECIO Y FACTURACIÓN </h3>
<p>4.1 El precio del Contrato es de 0,555 euros al mes por socio activo IVA (21%) excluido.</p>
<p>Las tarifas para el software con todos los módulos se calculan en función del número de socios activos que tenga la asociación. Por socio activo se entenderá los socios que han sido dispensados en la asociación durante el mes. </p>
<p>La cantidad máxima facturada será la correspondiente a 500 socios ACTIVOS. Si su asociación consta de más de 500 socios ACTIVOS, no le cobraremos ninguna cantidad por los socios ACTIVOS por encima de dicha cantidad.</p>
<p>La cantidad mínima facturada será la correspondiente a 6€ + IVA. Esta cantidad no se facturara si EL CLIENTE, con antelación de 5 días, informa al PROVEEDOR por correo electrónico a soporte@cannabisclub.systems que quiere darse de baja temporal por razones internos.</p>
<p>Para otros módulos y servicios ver lista de servicios y precios.</p>
<p>4.2 MES SIN COMPROMISO es un período de 30 días que comienza desde la primera vez que EL CLIENTE inicia sesión en el software. En este período, debería configurar y empezar a utilizar el software, una vez que hayan transcurrido máximo 23 días, EL CLIENTE tiene derecho a cancelar su suscripción, informándo al PROVEEDOR por correo electrónico a soporte@cannabisclub.systems y NO se le cobrará por el mes de prueba. Si EL CLIENTE desea continuar, el mes de prueba se cobrará de forma convencional, por socio activo u otro modulo que haya elegido.</p>
<p>4.3 El pago de las facturas por los servicios se realizará, tras la aceptación de los trabajos por el CLIENTE, mediante transferencia bancaria o ingreso en efectivo a los 10 días de la fecha de recepción de la factura a la siguiente cuenta corriente titularidad del PROVEEDOR: ES94 0182 0981 4902 0318 3962 (BBVA) </p>
<p>4.4 Las facturas DEBERÁN haber sido abonadas en un plazo máximo de 30 días desde su fecha de emisión. En caso de no haber abonado una factura pasado dicho plazo, su acceso al programa quedará temporalmente bloqueado hasta que el impago quede resuelto. Si llegara perder el acceso por impago de la/s factura/s se le aplicara 'Cargo por Reconexión' de 30€ (más IVA) en su siguiente factura para el uso de software. </p>
<p>4.5 El PROVEEDOR se compromete a avisar por correo electrónico al CLIENTE del cambio del precio mínimo 30 días con antelación. Si el CLIENTE esta desacuerdo y quiere terminar los servicios con el PROVEEDOR tiene que enviar por correo electrónico su baja de servicios.</p>

<br /><h3>QUINTA.- DURACIÓN DEL CONTRATO</h3>
<p>El plazo de duración del presente Contrato es indefinida. La terminación de esta Licencia de uso se puede producir las causas previstas en la Ley de Propiedad Intelectual y cualquier violación de las obligaciones de este contrato. </p>
<p>El Contrato podrá ser prorrogado expresamente y por escrito si ninguna de las partes realiza preaviso de un mes.</p>

<br /><h3>SEXTA.- ACUERDO DE NIVEL DE SERVICIO</h3>
<p>6.1 Todos los Servicios prestados por el PROVEEDOR se realizarán por personal especializado en cada materia. El personal del PROVEEDOR acudirá previsto de todo el material necesario, adecuado y actualizado, para prestar los Servicios.</p>
<p>6.2 Las averías o el mal funcionamiento de los Servicios se comunicarán al PROVEEDOR a través de correo electrónico soporte@cannabisclub.systems.</p>
<p>6.3 Los problemas se resolverán en un período máximo de 24 horas si es crítica, 48 si la incidencia es grave y cinco días si es leve desde la recepción del aviso.</p>
<p>6.4 Se entiende por incidencia crítica: las incidencias que, en el marco de la prestación de los Servicios, afectan significativamente al CLIENTE.</p>
<ul class='normallist'>
 <li>Se entiende por incidencia grave: las incidencias que, en el marco de la prestación de los Servicios, afectan moderadamente al CLIENTE.</li>
 <li>Se entiende por incidencia leve: las incidencias que se limitan a entorpecer la prestación de los Servicios.</li>
</ul>
<p>6.5 El estado de los Servicios se revisará trimestralmente por el CLIENTE y el PROVEEDOR para comprobar su buen funcionamiento.</p>
<p>6.6 PROPIEDAD DE LOS PROGRAMAS Y DERECHOS DE AUTOR.</p>
<p>Los programas amparados por este contrato bajo licencia, las reproducciones originales de los mismos, cualquier copia parcial o total, realizada por el PROVEEDOR o la CLIENTE o por cualquier otra persona, los derechos legales de copia, las patentes, las marcas, los secretos comerciales, y de cualquier otro derecho intelectual o de propiedad, pertenecen al PROVEEDOR, por lo que cuenta con las autorizaciones suficientes para otorgar a su vez licencias de uso sobre dichos programas.</p>
<p>EL CLIENTE acepta y reconoce que los programas bajo licencia son secretos comerciales del PROVEEDOR, así como toda la información o documentación que le sea proporcionada y que haya sido identificada por este como confidencial.</p>
<p>El CLIENTE deber abstenerse de copiar con o sin fines de lucro los programas bajo licencia, dictando las medidas internas necesarias tendientes a la protección de los de autor de los propietarios de los programas bajo licencia.</p>

<br /><h3>SÉPTIMA.- MODIFICACIÓN</h3>
<p>Las Partes podrán modificar el contrato de mutuo acuerdo y por escrito.</p>

<br /><h3>OCTAVA.- RESOLUCIÓN</h3>
<p>Las Partes podrán resolver el Contrato, con derecho a la indemnización de daños y perjuicios causados, en caso de incumplimiento de las obligaciones establecidas en el mismo.</p>

<br /><h3>NOVENA.- NOTIFICACIONES</h3>
<p>Las notificaciones que se realicen las Partes deberán realizarse por correo electrónico. El CLIENTE se compromete dar al PROVEEDOR los datos de contacto actualizados. </p>

<br /><h3>DÉCIMA.- REGIMEN JURÍDICO</h3>
<p>El presente contrato tiene carácter mercantil, no existiendo en ningún caso vínculo laboral alguno entre el CLIENTE y el personal del PROVEEDOR que preste concretamente los Servicios. </p>
<p>Toda controversia derivada de este contrato o que guarde relación con él -incluida cualquier cuestión relativa a su existencia, validez o terminación- será resuelta mediante arbitraje DE DERECHO, administrado por la Asociación Europea de Arbitraje de Madrid (Aeade), de conformidad con su Reglamento de Arbitraje vigente a la fecha de presentación de la solicitud de arbitraje. El Tribunal Arbitral que se designe a tal efecto estará compuesto por un único árbitro experto y el idioma del arbitraje será el Castellano La sede del arbitraje será Madrid.</p>
<p>Y en prueba de cuanto antecede, las Partes suscriben el Contrato, en dos ejemplares y a un solo efecto, en el lugar y fecha señalados en el encabezamiento
<br />
<br />

<br /><h3>Cláusula RGPD para recabar datos de interesados con consentimiento</h3>
<p>De acuerdo con lo establecido en el Reglamento General de Protección de Datos, le informamos que trataremos sus datos personales con la finalidad de realizar la gestión administrativa, contable y fiscal derivada de nuestra relación comercial, así como enviarle comunicaciones comerciales sobre nuestros productos y/o servicios. Los datos proporcionados se conservarán mientras se mantenga la relación comercial o durante el tiempo necesario para cumplir con las obligaciones legales. Los datos no se cederán a terceros salvo en los casos en que exista una obligación legal y los trataremos en base a su consentimiento.</p>
<p>Asimismo, le informamos de la posibilidad de ejercer los siguientes derechos sobre sus datos personales: derecho de acceso, rectificación, supresión u olvido, limitación, oposición, portabilidad y  a retirar el consentimiento prestado.</p>
<p>Para ello podrá enviar un email a: info@mykinlink.com o dirigir un escrito a Mykinlink SL, Calle Esteban Collantes 22, 28017 Madrid.</p>
<p>Además, el interesado puede  dirigirse a  la Autoridad de Control en  materia de Protección de Datos competente para obtener información adicional o presentar una reclamación.</p>
<br />
<br /><h3>CONSENTIMIENTO</h3>
<p>La finalidad y uso previsto tanto de los datos en sí mismos como de su tratamiento, es prestarle el servicio solicitado o entregarle el producto adquirido. A continuación podrá aceptar las finalidades que crea convenientes marcando su casilla correspondiente, tenga en cuenta que algunas finalidades pueden ser necesarias para poderle prestar el servicio, en el caso de NO marcar dichas casillas, no se podrá prestar/entregar el servicio/producto asociado.</p>
<form>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Prestación del servicio contratado (Si acepta el tratamiento de sus datos con esta finalidad marque esta casilla)
	  <input type="checkbox" name="accept2" id="accept2" checked disabled />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Envío del producto adquirido (Si acepta el tratamiento de sus datos con esta finalidad marque esta casilla)
	  <input type="checkbox" name="accept3" id="accept3" checked disabled />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />

	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox4"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Envío de ofertas de productos y servicios de su interés (Si acepta el tratamiento de sus datos con esta finalidad marque esta casilla)
	  <input type="checkbox" name="accept4" id="accept4" value='1' <?php if ($box3 == 1) { echo 'checked'; } ?> disabled />
	  <div class="fakebox"></div>
	 </label>
	</div>
</form>
<p class='smallerfont'>Datos identificativos del responsable:<br />
Mykinlink SL, B87843504, Calle Esteban Collantes 22, 28017 Madrid, 644441092</p>

<center><img src="https://ccsnubev2.com/v6/images/_<?php echo $domain; ?>/sigs/<?php echo $image; ?>.png" /></center>

	</div>
</div>

<?php displayFooter();