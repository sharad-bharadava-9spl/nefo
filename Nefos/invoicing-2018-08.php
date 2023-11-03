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
			
	pageStart("Invoicing", NULL, $deleteDonationScript, "pmembership", NULL, "Invoicing - August 2018", $_SESSION['successMessage'], $_SESSION['errorMessage']);


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
<tr><td>	10010	</td><td>	1900	</td><td style='text-align: right;'><?php echo number_format(	11.11	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	2.55	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	13.66	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	13.66	,2); ?> €</td><td>		</td></tr>
<tr><td>	10009	</td><td>	Abuelita Maria	</td><td style='text-align: right;'><?php echo number_format(	76.07	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	17.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	93.57	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	93.57	,2); ?> €</td><td>		</td></tr>
<tr><td>	10081	</td><td>	Amagi 	</td><td style='text-align: right;'><?php echo number_format(	21.66	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	4.98	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	26.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	26.64	,2); ?> €</td><td>		</td></tr>
<tr><td>	10109	</td><td>	Amenzia Haze	</td><td style='text-align: right;'><?php echo number_format(	44.98	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.34	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	55.32	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	55.32	,2); ?> €</td><td>		</td></tr>
<tr><td>	10078	</td><td>	Apricot	</td><td style='text-align: right;'><?php echo number_format(	47.75	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.98	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	58.74	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	58.74	,2); ?> €</td><td>		</td></tr>
<tr><td>	10062	</td><td>	Aranua 	</td><td style='text-align: right;'><?php echo number_format(	145.48	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	33.46	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	178.95	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	178.95	,2); ?> €</td><td>		</td></tr>
<tr><td>	10022	</td><td>	Arbol De La Vida	</td><td style='text-align: right;'><?php echo number_format(	32.76	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	7.54	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	40.3	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	40.3	,2); ?> €</td><td>		</td></tr>
<tr><td>	10052	</td><td>	Asociación Acharya	</td><td style='text-align: right;'><?php echo number_format(	141.6	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	32.57	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	174.17	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	174.17	,2); ?> €</td><td>		</td></tr>
<tr><td>	10093	</td><td>	Asociación Aquí Ahora Club  (Smoke Signals)	</td><td style='text-align: right;'><?php echo number_format(	69.41	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	15.96	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	85.38	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	85.38	,2); ?> €</td><td>		</td></tr>
<tr><td>	10103	</td><td>	Asociación Canela En Rama	</td><td style='text-align: right;'><?php echo number_format(	118.83	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	27.33	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	146.16	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	146.16	,2); ?> €</td><td>		</td></tr>
<tr><td>	10082	</td><td>	Asociación Cannábica Conquense Cuatroveinte	</td><td style='text-align: right;'><?php echo number_format(	39.98	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	9.2	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	49.18	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	49.18	,2); ?> €</td><td>		</td></tr>
<tr><td>	10031	</td><td>	Asociación de amigos para el estudio de la botánica Oasis Verde del Norte de la Isla (O.V.N.I)
	</td><td style='text-align: right;'><?php echo number_format(	119.39	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	27.46	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	146.85	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	146.85	,2); ?> €</td><td>		</td></tr>
<tr><td>	10111	</td><td>	Asociación IBOGA	</td><td style='text-align: right;'><?php echo number_format(	57.75	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	13.28	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	71.03	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	71.03	,2); ?> €</td><td>		</td></tr>
<tr><td>	10080	</td><td>	Asociación KYO	</td><td style='text-align: right;'><?php echo number_format(	45.53	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	56.01	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	56.01	,2); ?> €</td><td>		</td></tr>
<tr><td>	10050	</td><td>	Asociación Mariland BCN	</td><td style='text-align: right;'><?php echo number_format(	126.6	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	29.12	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	155.72	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	155.72	,2); ?> €</td><td>		</td></tr>
<tr><td>	10044	</td><td>	Asociación Mr. Teddy	</td><td style='text-align: right;'><?php echo number_format(	264.87	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	60.92	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	325.79	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	325.79	,2); ?> €</td><td>		</td></tr>
<tr><td>	10045	</td><td>	Asociación Nuevos Aires 	</td><td style='text-align: right;'><?php echo number_format(	32.76	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	7.54	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	40.3	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	40.3	,2); ?> €</td><td>		</td></tr>
<tr><td>	10088	</td><td>	Asociación Trébol Garden
	</td><td style='text-align: right;'><?php echo number_format(	8.88	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	2.04	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.93	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.93	,2); ?> €</td><td>		</td></tr>
<tr><td>	10065	</td><td>	Asociación Tresor	</td><td style='text-align: right;'><?php echo number_format(	217.67	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	50.06	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	267.74	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	267.74	,2); ?> €</td><td>		</td></tr>
<tr><td>	10106	</td><td>	Asociación Yellow Fingers	</td><td style='text-align: right;'><?php echo number_format(	44.42	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.22	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	54.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	54.64	,2); ?> €</td><td>		</td></tr>
<tr><td>	10105	</td><td>	Babel Weed	</td><td style='text-align: right;'><?php echo number_format(	24.43	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	5.62	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	30.05	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	30.05	,2); ?> €</td><td>		</td></tr>
<tr><td>	10094	</td><td>	Betty Boop	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10114	</td><td>	Bloom Room 	</td><td style='text-align: right;'><?php echo number_format(	5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	1.15	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	6.15	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	6.15	,2); ?> €</td><td>		</td></tr>
<tr><td>	10085	</td><td>	Boston Club	</td><td style='text-align: right;'><?php echo number_format(	94.4	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	21.71	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	116.11	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	116.11	,2); ?> €</td><td>		</td></tr>
<tr><td>	10042	</td><td>	California Club	</td><td style='text-align: right;'><?php echo number_format(	172.14	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	39.59	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	211.73	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	211.73	,2); ?> €</td><td>		</td></tr>
<tr><td>	10026	</td><td>	C-loud	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10101	</td><td>	Club Cannábico P.K (Fósforo y Potasio)
	</td><td style='text-align: right;'><?php echo number_format(	8.88	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	2.04	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.93	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.93	,2); ?> €</td><td>		</td></tr>
<tr><td>	10075	</td><td>	Club Marley 	</td><td style='text-align: right;'><?php echo number_format(	208.79	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	48.02	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	256.81	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	256.81	,2); ?> €</td><td>		</td></tr>
<tr><td>	10049	</td><td>	Creme Club	</td><td style='text-align: right;'><?php echo number_format(	150	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	34.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	184.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	184.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10027	</td><td>	Crystal	</td><td style='text-align: right;'><?php echo number_format(	150	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	34.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	184.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	184.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10037	</td><td>	Culcanna	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>Member module only</td></tr>
<tr><td>	10091	</td><td>	DeeDee Club	</td><td style='text-align: right;'><?php echo number_format(	208.79	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	48.02	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	256.81	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	256.81	,2); ?> €</td><td>		</td></tr>
<tr><td>	10076	</td><td>	El Numero Uno	</td><td style='text-align: right;'><?php echo number_format(	57.75	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	13.28	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	71.03	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	71.03	,2); ?> €</td><td>		</td></tr>
<tr><td>	10059	</td><td>	El Plaer De La Vida	</td><td style='text-align: right;'><?php echo number_format(	227.67	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	52.36	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	280.03	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	280.03	,2); ?> €</td><td>		</td></tr>
<tr><td>	10098	</td><td>	Fire House	</td><td style='text-align: right;'><?php echo number_format(	102.17	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	23.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	125.67	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	125.67	,2); ?> €</td><td>		</td></tr>
<tr><td>	10054	</td><td>	Fito Apotheka	</td><td style='text-align: right;'><?php echo number_format(	54.42	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	12.52	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	66.93	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	66.93	,2); ?> €</td><td>		</td></tr>
<tr><td>	10086	</td><td>	Friend's Lounge	</td><td style='text-align: right;'><?php echo number_format(	94.4	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	21.71	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	116.11	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	116.11	,2); ?> €</td><td>		</td></tr>
<tr><td>	10087	</td><td>	G Planet	</td><td style='text-align: right;'><?php echo number_format(	103.12	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	23.72	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	126.84	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	126.84	,2); ?> €</td><td>	Tourist account - check DB!	</td></tr>
<tr><td>	10028	</td><td>	G13	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10115	</td><td>	Good Vibes	</td><td style='text-align: right;'><?php echo number_format(	119.39	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	27.46	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	146.85	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	146.85	,2); ?> €</td><td>		</td></tr>
<tr><td>	10013	</td><td>	Green Gold	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10055	</td><td>	La Botica Verde	</td><td style='text-align: right;'><?php echo number_format(	79.41	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	18.26	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	97.67	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	97.67	,2); ?> €</td><td>		</td></tr>
<tr><td>	10038	</td><td>	La Crème Gracia	</td><td style='text-align: right;'><?php echo number_format(	122.16	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	28.1	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	150.26	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	150.26	,2); ?> €</td><td>		</td></tr>
<tr><td>	10041	</td><td>	La Cremme 	</td><td style='text-align: right;'><?php echo number_format(	117.72	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	27.08	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	144.8	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	144.8	,2); ?> €</td><td>		</td></tr>
<tr><td>	10060	</td><td>	La Gran Muralla Verde	</td><td style='text-align: right;'><?php echo number_format(	97.17	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	22.35	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	119.53	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	119.53	,2); ?> €</td><td>		</td></tr>
<tr><td>	10063	</td><td>	La Roca	</td><td style='text-align: right;'><?php echo number_format(	6.66	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	1.53	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	8.2	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	8.2	,2); ?> €</td><td>		</td></tr>
<tr><td>	10099	</td><td>	Mad Monkey	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>Member module only		</td></tr>
<tr><td>	10014	</td><td>	Manali	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10035	</td><td>	Maria Maria (Former Bob Green)	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10030	</td><td>	Mariador	</td><td style='text-align: right;'><?php echo number_format(	67.74	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	15.58	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	83.33	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	83.33	,2); ?> €</td><td>		</td></tr>
<tr><td>	10012	</td><td>	Personal CannaPharmacia	</td><td style='text-align: right;'><?php echo number_format(	247.1	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	56.83	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	303.94	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	303.94	,2); ?> €</td><td>		</td></tr>
<tr><td>	10023	</td><td>	Pharmacann	</td><td style='text-align: right;'><?php echo number_format(	48.31	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	11.11	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	59.42	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	59.42	,2); ?> €</td><td>		</td></tr>
<tr><td>	10061	</td><td>	Raices	</td><td style='text-align: right;'><?php echo number_format(	74.96	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	17.24	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	92.21	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	92.21	,2); ?> €</td><td>		</td></tr>
<tr><td>	10102	</td><td>	Selektum 	</td><td style='text-align: right;'><?php echo number_format(	78.85	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	18.14	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	96.99	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	96.99	,2); ?> €</td><td>		</td></tr>
<tr><td>	10002	</td><td>	Shambala	</td><td style='text-align: right;'><?php echo number_format(	108.84	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	25.03	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	133.87	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	133.87	,2); ?> €</td><td>		</td></tr>
<tr><td>	10068	</td><td>	Sibaritas Cannabis Club	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10047	</td><td>	Sky Lounge	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10072	</td><td>	StarGrass	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>	Tourist account - check DB!	</td></tr>
<tr><td>	10069	</td><td>	Strain Hunters Club	</td><td style='text-align: right;'><?php echo number_format(	326.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	75.13	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	401.77	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	401.77	,2); ?> €</td><td>Special agreement		</td></tr>
<tr><td>	10121	</td><td>	Supreme	</td><td style='text-align: right;'><?php echo number_format(	232.85	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	53.56	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	286.41	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	286.41	,2); ?> €</td><td>	Tourist account - check DB!</td></tr>
<tr><td>	10079	</td><td>	Sweet Oil Cannabis Social Club 	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>Member module only		</td></tr>
<tr><td>	10073	</td><td>	Terps Army 	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10007	</td><td>	THC Madrid	</td><td style='text-align: right;'><?php echo number_format(	49.98	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	11.49	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	61.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	61.47	,2); ?> €</td><td>		</td></tr>
<tr><td>	10015	</td><td>	The Back Yard	</td><td style='text-align: right;'><?php echo number_format(	204.34	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	251.34	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	251.34	,2); ?> €</td><td>		</td></tr>
<tr><td>	10084	</td><td>	The Green Forest	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>Member module only		</td></tr>
<tr><td>	10113	</td><td>	The Roots 	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>Member module only		</td></tr>
<tr><td>	10053	</td><td>	Tree House Club	</td><td style='text-align: right;'><?php echo number_format(	122.72	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	28.23	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	150.94	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	150.94	,2); ?> €</td><td>		</td></tr>
<tr><td>	10096	</td><td>	Varetto Club	</td><td style='text-align: right;'><?php echo number_format(	141.04	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	32.44	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	173.48	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	173.48	,2); ?> €</td><td>		</td></tr>
<tr><td>	10048	</td><td>	Weed You BCN	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10029	</td><td>	Weed's Family CSC	</td><td style='text-align: right;'><?php echo number_format(	88.29	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.31	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	108.6	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	108.6	,2); ?> €</td><td>		</td></tr>
<tr><td>	10005	</td><td>	Zhara	</td><td style='text-align: right;'><?php echo number_format(	8.33	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	1.92	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.25	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	0	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.25	,2); ?> €</td><td>		</td></tr>




	 </tbody>
	 </table>

   
<?php displayFooter(); ?>
