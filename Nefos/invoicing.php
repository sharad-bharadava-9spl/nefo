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
			
	pageStart("Invoicing", NULL, $deleteDonationScript, "pmembership", NULL, "Invoicing - January 2018", $_SESSION['successMessage'], $_SESSION['errorMessage']);


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
	    <th>Shipping</th>
	    <th>Base price</th>
	    <th>Base + Shipping</th>
	   </tr>
	  </thead>
	  <tbody>
	  
<tr><td>	10151	</td><td>	059	</td><td style='text-align: right;'><?php echo number_format(	25.39	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	114.39	,2); ?> €</td></tr>
<tr><td>	10142	</td><td>	386	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	36.65	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	36.65	,2); ?> €</td></tr>
<tr><td>	10009	</td><td>	Abuelita Maria	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	56.08	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	56.08	,2); ?> €</td></tr>
<tr><td>	10145	</td><td>	Agartha Joy Land	</td><td style='text-align: right;'><?php echo number_format(	7.94	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.55	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	18.49	,2); ?> €</td></tr>
<tr><td>	10081	</td><td>	Amagi 	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	48.31	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	48.31	,2); ?> €</td></tr>
<tr><td>	10109	</td><td>	Amenzia Haze	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	156.59	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	156.59	,2); ?> €</td></tr>
<tr><td>	10022	</td><td>	Arbol De La Vida	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	17.21	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	17.21	,2); ?> €</td></tr>
<tr><td>	10052	</td><td>	Asociación Acharya	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	174.36	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	174.36	,2); ?> €</td></tr>
<tr><td>	10124	</td><td>	Asociación Amics de Jamaica	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td></tr>
<tr><td>	10093	</td><td>	Asociación Aquí Ahora Club  (Smoke Signals)	</td><td style='text-align: right;'><?php echo number_format(	64.73	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	97.73	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	162.46	,2); ?> €</td></tr>
<tr><td>	10103	</td><td>	Asociación Canela En Rama	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	132.71	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	132.71	,2); ?> €</td></tr>
<tr><td>	10082	</td><td>	Asociación Cannábica Conquense Cuatroveinte	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	49.98	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	49.98	,2); ?> €</td></tr>
<tr><td>	10130	</td><td>	Asociación Cultural Cannabica y otros tipos Tabaco (ACCOTT) 420	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td></tr>
<tr><td>	10031	</td><td>	"Asociación de amigos para el estudio de la botánica Oasis Verde del Norte de la Isla (O.V.N.I)
"	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	74.96	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	74.96	,2); ?> €</td></tr>
<tr><td>	10111	</td><td>	Asociación IBOGA	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	77.74	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	77.74	,2); ?> €</td></tr>
<tr><td>	10129	</td><td>	Asociación Juegos Mentales (Green City)	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	23.32	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	23.32	,2); ?> €</td></tr>
<tr><td>	10080	</td><td>	Asociación KYO	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	42.76	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	42.76	,2); ?> €</td></tr>
<tr><td>	10044	</td><td>	Asociación Mr. Teddy	</td><td style='text-align: right;'><?php echo number_format(	26.34	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	303.98	,2); ?> €</td></tr>
<tr><td>	10045	</td><td>	Asociación Nuevos Aires 	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	37.2	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	37.2	,2); ?> €</td></tr>
<tr><td>	10126	</td><td>	Asociación para aplicación terapeutica autoconsumo Cannabis (A.P.A.T.A.C)	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td></tr>
<tr><td>	10088	</td><td>	"Asociación Trébol Garden
"	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	19.99	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	19.99	,2); ?> €</td></tr>
<tr><td>	10065	</td><td>	Asociación Tresor	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	196.02	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	196.02	,2); ?> €</td></tr>
<tr><td>	10107	</td><td>	Asociación Ugorg IBZ 	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	4.44	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	4.44	,2); ?> €</td></tr>
<tr><td>	10125	</td><td>	Asociación Verd Fort (RGPD)	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	11.11	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	11.11	,2); ?> €</td></tr>
<tr><td>	10105	</td><td>	Babel Weed	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	34.98	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	34.98	,2); ?> €</td></tr>
<tr><td>	10116	</td><td>	Barra de Hash	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	81.63	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	81.63	,2); ?> €</td></tr>
<tr><td>	10094	</td><td>	Betty Boop	</td><td style='text-align: right;'><?php echo number_format(	31.31	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	308.95	,2); ?> €</td></tr>
<tr><td>	10114	</td><td>	Bloom Room 	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	11.66	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	11.66	,2); ?> €</td></tr>
<tr><td>	10085	</td><td>	Boston Club	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	209.34	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	209.34	,2); ?> €</td></tr>
<tr><td>	10042	</td><td>	California Club	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	203.23	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	203.23	,2); ?> €</td></tr>
<tr><td>	10026	</td><td>	C-loud	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td></tr>
<tr><td>	10137	</td><td>	Chillum	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	48.31	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	48.31	,2); ?> €</td></tr>
<tr><td>	10135	</td><td>	Choko	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td></tr>
<tr><td>	10101	</td><td>	"Club Cannábico P.K (Fósforo y Potasio)
"	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	8.33	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	8.33	,2); ?> €</td></tr>
<tr><td>	10075	</td><td>	Club Marley 	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	207.68	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	207.68	,2); ?> €</td></tr>
<tr><td>	10049	</td><td>	Creme Club	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	150	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	150	,2); ?> €</td></tr>
<tr><td>	10027	</td><td>	Crystal	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	150	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	150	,2); ?> €</td></tr>
<tr><td>	10139	</td><td>	CSC Buda	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	54.97	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	54.97	,2); ?> €</td></tr>
<tr><td>	10037	</td><td>	Culcanna	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	163.81	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	163.81	,2); ?> €</td></tr>
<tr><td>	10091	</td><td>	DeeDee Club	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	181.58	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	181.58	,2); ?> €</td></tr>
<tr><td>	10138	</td><td>	Delta 9	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	62.19	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	62.19	,2); ?> €</td></tr>
<tr><td>	10076	</td><td>	El Numero Uno	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	126.05	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	126.05	,2); ?> €</td></tr>
<tr><td>	10059	</td><td>	El Plaer De La Vida	</td><td style='text-align: right;'><?php echo number_format(	8.46	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	264.87	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	273.33	,2); ?> €</td></tr>
<tr><td>	10134	</td><td>	Euskunk	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	172.14	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	172.14	,2); ?> €</td></tr>
<tr><td>	10098	</td><td>	Fire House	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	172.69	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	172.69	,2); ?> €</td></tr>
<tr><td>	10054	</td><td>	Fito Apotheka	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	86.62	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	86.62	,2); ?> €</td></tr>
<tr><td>	10141	</td><td>	Fortnite	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	111.06	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	111.06	,2); ?> €</td></tr>
<tr><td>	10086	</td><td>	Friend's Lounge	</td><td style='text-align: right;'><?php echo number_format(	8.46	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	138.27	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	146.73	,2); ?> €</td></tr>
<tr><td>	10087	</td><td>	G Planet	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	102.73	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	102.73	,2); ?> €</td></tr>
<tr><td>	10136	</td><td>	G&A	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	19.43	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	19.43	,2); ?> €</td></tr>
<tr><td>	10028	</td><td>	G13	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	327.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	327.64	,2); ?> €</td></tr>
<tr><td>	10149	</td><td>	Golden Bush	</td><td style='text-align: right;'><?php echo number_format(	26.01	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	76.1	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	102.11	,2); ?> €</td></tr>
<tr><td>	10115	</td><td>	Good Vibes	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	140.49	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	140.49	,2); ?> €</td></tr>
<tr><td>	10117	</td><td>	Gourmet Ganja	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td></tr>
<tr><td>	10013	</td><td>	Green Gold	</td><td style='text-align: right;'><?php echo number_format(	47.73	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	325.37	,2); ?> €</td></tr>
<tr><td>	10128	</td><td>	Green Sensaton	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td></tr>
<tr><td>	10144	</td><td>	Habana Club	</td><td style='text-align: right;'><?php echo number_format(	43.43	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	176.03	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	219.46	,2); ?> €</td></tr>
<tr><td>	10152	</td><td>	Herbalize	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	48.87	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	48.87	,2); ?> €</td></tr>
<tr><td>	10127	</td><td>	Ibiza Maria	</td><td style='text-align: right;'><?php echo number_format(	29.14	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	164.92	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	194.06	,2); ?> €</td></tr>
<tr><td>	10120	</td><td>	Impact of Medical Green 	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	102.17	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	102.17	,2); ?> €</td></tr>
<tr><td>	10055	</td><td>	La Botica Verde	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	88.85	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	88.85	,2); ?> €</td></tr>
<tr><td>	10038	</td><td>	La Crème Gracia	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	150.48	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	150.48	,2); ?> €</td></tr>
<tr><td>	10041	</td><td>	La Cremme 	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	155.48	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	155.48	,2); ?> €</td></tr>
<tr><td>	10060	</td><td>	La Gran Muralla Verde	</td><td style='text-align: right;'><?php echo number_format(	8.46	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	99.95	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	108.41	,2); ?> €</td></tr>
<tr><td>	10146	</td><td>	La Nova 201 (16/11/2018 - 30/11/2018)	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	61.08	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	61.08	,2); ?> €</td></tr>
<tr><td>	10146	</td><td>	La Nova 201 (December 2018)	</td><td style='text-align: right;'><?php echo number_format(	11.36	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	213.23	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	224.59	,2); ?> €</td></tr>
<tr><td>	10063	</td><td>	La Roca	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	15.55	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	15.55	,2); ?> €</td></tr>
<tr><td>	10099	</td><td>	Mad Monkey	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	56.08	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	56.08	,2); ?> €</td></tr>
<tr><td>	10014	</td><td>	Manali	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td></tr>
<tr><td>	10035	</td><td>	Maria Maria (Former Bob Green)	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td></tr>
<tr><td>	10030	</td><td>	Mariador	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	64.97	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	64.97	,2); ?> €</td></tr>
<tr><td>	10077	</td><td>	Monoloco	</td><td style='text-align: right;'><?php echo number_format(	8.46	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	286.1	,2); ?> €</td></tr>
<tr><td>	10012	</td><td>	Personal CannaPharmacia	</td><td style='text-align: right;'><?php echo number_format(	23.38	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	192.13	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	215.51	,2); ?> €</td></tr>
<tr><td>	10023	</td><td>	Pharmacann	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	52.75	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	52.75	,2); ?> €</td></tr>
<tr><td>	10133	</td><td>	River Green	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	100.51	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	100.51	,2); ?> €</td></tr>
<tr><td>	10061	</td><td>	Raices	</td><td style='text-align: right;'><?php echo number_format(	7.68	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	83.85	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	91.53	,2); ?> €</td></tr>
<tr><td>	10140	</td><td>	Relax	</td><td style='text-align: right;'><?php echo number_format(	32.68	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	310.32	,2); ?> €</td></tr>
<tr><td>	10102	</td><td>	Selektum 	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	106.61	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	106.61	,2); ?> €</td></tr>
<tr><td>	10002	</td><td>	Shambala	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	121.61	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	121.61	,2); ?> €</td></tr>
<tr><td>	10068	</td><td>	Sibaritas Cannabis Club	</td><td style='text-align: right;'><?php echo number_format(	23.38	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	301.02	,2); ?> €</td></tr>
<tr><td>	10047	</td><td>	Sky Lounge	</td><td style='text-align: right;'><?php echo number_format(	10.29	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	287.93	,2); ?> €</td></tr>
<tr><td>	10072	</td><td>	StarGrass	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td></tr>
<tr><td>	10069	</td><td>	Strain Hunters Club	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	326.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	326.64	,2); ?> €</td></tr>
<tr><td>	10121	</td><td>	Supreme	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	201.01	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	201.01	,2); ?> €</td></tr>
<tr><td>	10079	</td><td>	Sweet Oil Cannabis Social Club 	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td></tr>
<tr><td>	10073	</td><td>	Terps Army 	</td><td style='text-align: right;'><?php echo number_format(	36.32	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	313.96	,2); ?> €</td></tr>
<tr><td>	10015	</td><td>	The Back Yard	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	248.21	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	248.21	,2); ?> €</td></tr>
<tr><td>	10145	</td><td>	The Cut	</td><td style='text-align: right;'><?php echo number_format(	20.57	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.57	,2); ?> €</td></tr>
<tr><td>	10084	</td><td>	The Green Forest	</td><td style='text-align: right;'><?php echo number_format(	8.46	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	97.46	,2); ?> €</td></tr>
<tr><td>	10113	</td><td>	The Roots 	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td></tr>
<tr><td>	10053	</td><td>	Tree House Club	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	166.59	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	166.59	,2); ?> €</td></tr>
<tr><td>	10096	</td><td>	Varetto Club	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	167.7	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	167.7	,2); ?> €</td></tr>
<tr><td>	10148	</td><td>	We Flowers	</td><td style='text-align: right;'><?php echo number_format(	16.93	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	121.05	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	137.98	,2); ?> €</td></tr>
<tr><td>	10048	</td><td>	Weed You BCN	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td></tr>
<tr><td>	10029	</td><td>	Weed's Family CSC	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	86.07	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	86.07	,2); ?> €</td></tr>
<tr><td>	10106	</td><td>	YellowFingers	</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	33.87	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	33.87	,2); ?> €</td></tr>
<tr><td>	10150	</td><td>	Zanzi	</td><td style='text-align: right;'><?php echo number_format(	39	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	39	,2); ?> €</td></tr>

	 </tbody>
	 </table>

   
<?php displayFooter(); ?>
