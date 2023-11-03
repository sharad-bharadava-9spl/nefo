<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();

	$deleteDonationScript = <<<EOD
	
	    $(document).ready(function() {
		    


$("#xllink").click(function(){

	  $("#mainTable").table2excel({
	    // exclude CSS class
	    exclude: ".noExl",
	    name: "Invoicing",
	    filename: "Invoicing" //do not include extension

	  });

	});
		    
		    
		    
			$('#cloneTable').width($('#mainTable').width());
			
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					1: {
						sorter: "text"
					},
					2: {
						sorter: "currency"
					},
					3: {
						sorter: "currency"
					},
					4: {
						sorter: "currency"
					},
					5: {
						sorter: "currency"
					},
					6: {
						sorter: "currency"
					}
				}
			});
			
		});
EOD;
			
	pageStart("Invoicing", NULL, $deleteDonationScript, "pmembership", NULL, "Invoicing - September 2018", $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>
	 <table class='default' id='cloneTable' style='text-align: left;'>
      <tr class='nonhover'>
       <td colspan='6' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a><br /><br />
       </td>
      </tr>
     </table>


	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th>Customer #</th>
	    <th>Customer name</th>
	    <th>Base price</th>
	    <th>VAT (23%)</th>
	    <th>SW</th>
	    <th>Shipping</th>
	    <th>TOTAL</th>
	    <th>Comment</th>
	   </tr>
	  </thead>
	  <tbody>
<tr><td>	10009	</td><td>	Abuelita Maria	</td><td style='text-align: right;'><?php echo number_format(	78.85	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	18.14	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	96.99	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	96.99	,2); ?> €</td><td>	</td></tr>
<tr><td>	10081	</td><td>	Amagi 	</td><td style='text-align: right;'><?php echo number_format(	37.76	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	8.68	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	46.44	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	46.44	,2); ?> €</td><td>	</td></tr>
<tr><td>	10109	</td><td>	Amenzia Haze	</td><td style='text-align: right;'><?php echo number_format(	87.74	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.18	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	107.91	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	107.91	,2); ?> €</td><td>	</td></tr>
<tr><td>	10078	</td><td>	Apricot	</td><td style='text-align: right;'><?php echo number_format(	56.08	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	12.9	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	68.98	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	68.98	,2); ?> €</td><td>	</td></tr>
<tr><td>	10062	</td><td>	Aranua 	</td><td style='text-align: right;'><?php echo number_format(	92.73	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	21.33	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	114.06	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	114.06	,2); ?> €</td><td>	</td></tr>
<tr><td>	10022	</td><td>	Arbol De La Vida	</td><td style='text-align: right;'><?php echo number_format(	22.21	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	5.11	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	27.32	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	27.32	,2); ?> €</td><td>	</td></tr>
<tr><td>	10052	</td><td>	Asociación Acharya	</td><td style='text-align: right;'><?php echo number_format(	128.27	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	29.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	157.77	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	157.77	,2); ?> €</td><td>	</td></tr>
<tr><td>	10103	</td><td>	Asociación Canela En Rama	</td><td style='text-align: right;'><?php echo number_format(	126.6	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	29.12	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	155.72	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	155.72	,2); ?> €</td><td>	</td></tr>
<tr><td>	10082	</td><td>	Asociación Cannábica Conquense Cuatroveinte	</td><td style='text-align: right;'><?php echo number_format(	44.98	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.35	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	55.32	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	55.32	,2); ?> €</td><td>	</td></tr>
<tr><td>	10130	</td><td>	Asociación Cultural Cannabica y otros tipos Tabaco (ACCOTT) 420	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>	</td></tr>
<tr><td>	10031	</td><td>	"Asociación de amigos para el estudio de la botánica Oasis Verde del Norte de la Isla (O.V.N.I)
"	</td><td style='text-align: right;'><?php echo number_format(	103.84	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	23.88	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	127.72	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	127.72	,2); ?> €</td><td>	</td></tr>
<tr><td>	10111	</td><td>	Asociación IBOGA	</td><td style='text-align: right;'><?php echo number_format(	56.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	13.03	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	69.67	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	69.67	,2); ?> €</td><td>	</td></tr>
<tr><td>	10129	</td><td>	Asociación Juegos Mentales (Green City)	</td><td style='text-align: right;'><?php echo number_format(	36.09	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	8.3	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	44.4	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	44.4	,2); ?> €</td><td>	</td></tr>
<tr><td>	10080	</td><td>	Asociación KYO	</td><td style='text-align: right;'><?php echo number_format(	46.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.73	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	57.37	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	57.37	,2); ?> €</td><td>	</td></tr>
<tr><td>	10050	</td><td>	Asociación Mariland BCN	</td><td style='text-align: right;'><?php echo number_format(	61.08	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	14.05	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	75.13	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	75.13	,2); ?> €</td><td>	</td></tr>
<tr><td>	10044	</td><td>	Asociación Mr. Teddy	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>	</td></tr>
<tr><td>	10045	</td><td>	Asociación Nuevos Aires 	</td><td style='text-align: right;'><?php echo number_format(	33.32	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	7.66	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	40.98	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	40.98	,2); ?> €</td><td>	</td></tr>
<tr><td>	10126	</td><td>	Asociación para aplicación terapeutica autoconsumo Cannabis (A.P.A.T.A.C)	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>	</td></tr>
<tr><td>	10122	</td><td>	Asociación PRK	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>	</td></tr>
<tr><td>	10100	</td><td>	Asociación Sandicá	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>	</td></tr>
<tr><td>	10088	</td><td>	"Asociación Trébol Garden
"	</td><td style='text-align: right;'><?php echo number_format(	7.77	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	1.79	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	9.56	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	9.56	,2); ?> €</td><td>	</td></tr>
<tr><td>	10125	</td><td>	Asociación Verd Fort (RGPD)	</td><td style='text-align: right;'><?php echo number_format(	18.88	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	4.34	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	23.22	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	23.22	,2); ?> €</td><td>	</td></tr>
<tr><td>	10106	</td><td>	Asociación Yellow Fingers	</td><td style='text-align: right;'><?php echo number_format(	33.87	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	7.79	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	41.66	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	41.66	,2); ?> €</td><td>	</td></tr>
<tr><td>	10105	</td><td>	Babel Weed	</td><td style='text-align: right;'><?php echo number_format(	22.77	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	5.24	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	28	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	28	,2); ?> €</td><td>	</td></tr>
<tr><td>	10094	</td><td>	Betty Boop	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>	</td></tr>
<tr><td>	10114	</td><td>	Bloom Room 	</td><td style='text-align: right;'><?php echo number_format(	9.44	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	2.17	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	11.61	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	11.61	,2); ?> €</td><td>	</td></tr>
<tr><td>	10085	</td><td>	Boston Club	</td><td style='text-align: right;'><?php echo number_format(	98.84	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	22.73	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	121.57	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	121.57	,2); ?> €</td><td>	</td></tr>
<tr><td>	10042	</td><td>	California Club	</td><td style='text-align: right;'><?php echo number_format(	166.59	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	38.32	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	204.9	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	204.9	,2); ?> €</td><td>	</td></tr>
<tr><td>	10026	</td><td>	C-loud	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>	</td></tr>
<tr><td>	10101	</td><td>	"Club Cannábico P.K (Fósforo y Potasio)
"	</td><td style='text-align: right;'><?php echo number_format(	5.55	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	1.28	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	6.83	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	6.83	,2); ?> €</td><td>	</td></tr>
<tr><td>	10075	</td><td>	Club Marley 	</td><td style='text-align: right;'><?php echo number_format(	196.57	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	45.21	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	241.78	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	241.78	,2); ?> €</td><td>	</td></tr>
<tr><td>	10049	</td><td>	Creme Club	</td><td style='text-align: right;'><?php echo number_format(	150	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	34.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	184.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	184.5	,2); ?> €</td><td>	</td></tr>
<tr><td>	10027	</td><td>	Crystal	</td><td style='text-align: right;'><?php echo number_format(	150	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	34.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	184.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	184.5	,2); ?> €</td><td>	</td></tr>
<tr><td>	10037	</td><td>	Culcanna	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>	</td></tr>
<tr><td>	10123	</td><td>	Cuyagua	</td><td style='text-align: right;'><?php echo number_format(	120.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	27.72	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	148.21	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	148.21	,2); ?> €</td><td>	</td></tr>
<tr><td>	10091	</td><td>	DeeDee Club	</td><td style='text-align: right;'><?php echo number_format(	198.79	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	45.72	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	244.51	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	244.51	,2); ?> €</td><td>	</td></tr>
<tr><td>	10076	</td><td>	El Numero Uno	</td><td style='text-align: right;'><?php echo number_format(	227.11	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	52.24	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	279.35	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	279.35	,2); ?> €</td><td>	</td></tr>
<tr><td>	10059	</td><td>	El Plaer De La Vida	</td><td style='text-align: right;'><?php echo number_format(	221.56	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	50.96	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	272.52	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	272.52	,2); ?> €</td><td>	</td></tr>
<tr><td>	10098	</td><td>	Fire House	</td><td style='text-align: right;'><?php echo number_format(	188.8	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	43.42	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	232.22	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	232.22	,2); ?> €</td><td>	</td></tr>
<tr><td>	10054	</td><td>	Fito Apotheka	</td><td style='text-align: right;'><?php echo number_format(	54.97	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	12.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	67.62	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	67.62	,2); ?> €</td><td>	</td></tr>
<tr><td>	10086	</td><td>	Friend's Lounge	</td><td style='text-align: right;'><?php echo number_format(	120.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	27.72	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	148.21	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	148.21	,2); ?> €</td><td>	</td></tr>
<tr><td>	10087	</td><td>	G Planet	</td><td style='text-align: right;'><?php echo number_format(	235.44	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	54.15	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	289.59	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	289.59	,2); ?> €</td><td>	</td></tr>
<tr><td>	10028	</td><td>	G13	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>	</td></tr>
<tr><td>	10115	</td><td>	Good Vibes	</td><td style='text-align: right;'><?php echo number_format(	109.95	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	25.29	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	135.23	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	135.23	,2); ?> €</td><td>	</td></tr>
<tr><td>	10013	</td><td>	Green Gold	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>	</td></tr>
<tr><td>	10128	</td><td>	Green Sensaton	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>	</td></tr>
<tr><td>	10127	</td><td>	Ibiza Maria	</td><td style='text-align: right;'><?php echo number_format(	68.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	15.84	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	84.69	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	84.69	,2); ?> €</td><td>	</td></tr>
<tr><td>	10120	</td><td>	Impact of Medical Green 	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>	</td></tr>
<tr><td>	10055	</td><td>	La Botica Verde	</td><td style='text-align: right;'><?php echo number_format(	78.3	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	18.01	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	96.3	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	96.3	,2); ?> €</td><td>	</td></tr>
<tr><td>	10038	</td><td>	La Crème Gracia	</td><td style='text-align: right;'><?php echo number_format(	121.05	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	27.84	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	148.89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	148.89	,2); ?> €</td><td>	</td></tr>
<tr><td>	10041	</td><td>	La Cremme 	</td><td style='text-align: right;'><?php echo number_format(	131.6	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	30.27	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	161.87	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	161.87	,2); ?> €</td><td>	</td></tr>
<tr><td>	10060	</td><td>	La Gran Muralla Verde	</td><td style='text-align: right;'><?php echo number_format(	93.84	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	21.58	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	115.43	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	115.43	,2); ?> €</td><td>	</td></tr>
<tr><td>	10063	</td><td>	La Roca	</td><td style='text-align: right;'><?php echo number_format(	7.77	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	1.79	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	9.56	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	9.56	,2); ?> €</td><td>	</td></tr>
<tr><td>	10099	</td><td>	Mad Monkey	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>	</td></tr>
<tr><td>	10014	</td><td>	Manali	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>	</td></tr>
<tr><td>	10035	</td><td>	Maria Maria (Former Bob Green)	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>	</td></tr>
<tr><td>	10030	</td><td>	Mariador	</td><td style='text-align: right;'><?php echo number_format(	67.74	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	15.58	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	83.33	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	83.33	,2); ?> €</td><td>	</td></tr>
<tr><td>	10012	</td><td>	Personal CannaPharmacia	</td><td style='text-align: right;'><?php echo number_format(	275.98	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.48	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	339.45	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	339.45	,2); ?> €</td><td>	</td></tr>
<tr><td>	10023	</td><td>	Pharmacann	</td><td style='text-align: right;'><?php echo number_format(	43.87	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.09	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	53.96	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	53.96	,2); ?> €</td><td>	</td></tr>
<tr><td>	10133	</td><td>	River Green	</td><td style='text-align: right;'><?php echo number_format(	87.18	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.05	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	107.23	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	107.23	,2); ?> €</td><td>	</td></tr>
<tr><td>	10061	</td><td>	Raices	</td><td style='text-align: right;'><?php echo number_format(	67.74	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	15.58	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	83.33	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	83.33	,2); ?> €</td><td>	</td></tr>
<tr><td>	10102	</td><td>	Selektum 	</td><td style='text-align: right;'><?php echo number_format(	63.3	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	14.56	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	77.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	77.86	,2); ?> €</td><td>	</td></tr>
<tr><td>	10002	</td><td>	Shambala	</td><td style='text-align: right;'><?php echo number_format(	103.84	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	23.88	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	127.72	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	127.72	,2); ?> €</td><td>	</td></tr>
<tr><td>	10068	</td><td>	Sibaritas Cannabis Club	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>	</td></tr>
<tr><td>	10047	</td><td>	Sky Lounge	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>	</td></tr>
<tr><td>	10072	</td><td>	StarGrass	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>	</td></tr>
<tr><td>	10069	</td><td>	Strain Hunters Club	</td><td style='text-align: right;'><?php echo number_format(	326.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	75.13	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	401.77	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	401.77	,2); ?> €</td><td>	</td></tr>
<tr><td>	10121	</td><td>	Supreme	</td><td style='text-align: right;'><?php echo number_format(	245.44	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	56.45	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	301.89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	301.89	,2); ?> €</td><td>	</td></tr>
<tr><td>	10079	</td><td>	Sweet Oil Cannabis Social Club 	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>	</td></tr>
<tr><td>	10073	</td><td>	Terps Army 	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>	</td></tr>
<tr><td>	10007	</td><td>	THC Madrid	</td><td style='text-align: right;'><?php echo number_format(	46.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.73	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	57.37	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	57.37	,2); ?> €</td><td>	</td></tr>
<tr><td>	10015	</td><td>	The Back Yard	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>	</td></tr>
<tr><td>	10084	</td><td>	The Green Forest	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>	</td></tr>
<tr><td>	10071	</td><td>	The Medical Green	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>	</td></tr>
<tr><td>	10113	</td><td>	The Roots 	</td><td style='text-align: right;'><?php echo number_format(	189.35	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	43.55	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	232.9	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	232.9	,2); ?> €</td><td>	</td></tr>
<tr><td>	10131	</td><td>	Top Shelf	</td><td style='text-align: right;'><?php echo number_format(	37.76	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	8.68	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	46.44	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	46.44	,2); ?> €</td><td>	</td></tr>
<tr><td>	10053	</td><td>	Tree House Club	</td><td style='text-align: right;'><?php echo number_format(	114.39	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	26.31	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	140.7	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	140.7	,2); ?> €</td><td>	</td></tr>
<tr><td>	10096	</td><td>	Varetto Club	</td><td style='text-align: right;'><?php echo number_format(	141.04	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	32.44	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	173.48	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	173.48	,2); ?> €</td><td>	</td></tr>
<tr><td>	10048	</td><td>	Weed You BCN	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>	</td></tr>
<tr><td>	10029	</td><td>	Weed's Family CSC	</td><td style='text-align: right;'><?php echo number_format(	87.18	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.05	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	107.23	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	107.23	,2); ?> €</td><td>	</td></tr>




	 </tbody>
	 </table>

   
<?php displayFooter(); ?>
