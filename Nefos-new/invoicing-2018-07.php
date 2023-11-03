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
					}
				}
			});
			
		});
EOD;
			
	pageStart("Invoicing", NULL, $deleteDonationScript, "pmembership", NULL, "Invoicing - July 2018", $_SESSION['successMessage'], $_SESSION['errorMessage']);


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
	    <th>Total</th>
	    <th>Comment</th>
	   </tr>
	  </thead>
	  <tbody>
<tr><td>	10005	</td><td>	Zhara	</td><td style='text-align: right;'><?php echo number_format(	49.98	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	11.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	61.48	,2); ?> €</td><td>		</td></tr>
<tr><td>	10029	</td><td>	Weed's Family CSC	</td><td style='text-align: right;'><?php echo number_format(	82.18	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	18.9	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	101.08	,2); ?> €</td><td>		</td></tr>
<tr><td>	10048	</td><td>	Weed You BCN	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10096	</td><td>	Varetto Club	</td><td style='text-align: right;'><?php echo number_format(	150.48	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	34.61	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	185.09	,2); ?> €</td><td>		</td></tr>
<tr><td>	10024	</td><td>	Txakan	</td><td style='text-align: right;'><?php echo number_format(	43.31	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	9.96	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	53.27	,2); ?> €</td><td>		</td></tr>
<tr><td>	10053	</td><td>	Tree House Club	</td><td style='text-align: right;'><?php echo number_format(	123.27	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	28.35	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	151.62	,2); ?> €</td><td>		</td></tr>
<tr><td>	10113	</td><td>	The Roots	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>	Member module only	</td></tr>
<tr><td>	10083	</td><td>	The Home Club	</td><td style='text-align: right;'><?php echo number_format(	64.97	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	14.94	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	79.91	,2); ?> €</td><td>		</td></tr>
<tr><td>	10084	</td><td>	The Green Forest	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>	Member module only	</td></tr>
<tr><td>	10015	</td><td>	The Back Yard	</td><td style='text-align: right;'><?php echo number_format(	236	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	54.28	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	290.28	,2); ?> €</td><td>		</td></tr>
<tr><td>	10007	</td><td>	THC Madrid	</td><td style='text-align: right;'><?php echo number_format(	54.42	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	12.52	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	66.94	,2); ?> €</td><td>		</td></tr>
<tr><td>	10073	</td><td>	Terps Army	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10079	</td><td>	Sweet Oil Cannabis Social Club	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>	Member module only	</td></tr>
<tr><td>	10069	</td><td>	Strain Hunters Club	</td><td style='text-align: right;'><?php echo number_format(	326.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	75.13	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	401.77	,2); ?> €</td><td>	Special agreement	</td></tr>
<tr><td>	10072	</td><td>	StarGrass	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>	Tourist account - check DB!	</td></tr>
<tr><td>	10047	</td><td>	Sky Lounge	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10068	</td><td>	Sibaritas Cannabis Club	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10002	</td><td>	Shambala	</td><td style='text-align: right;'><?php echo number_format(	103.84	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	23.88	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	127.72	,2); ?> €</td><td>		</td></tr>
<tr><td>	10102	</td><td>	Selektum	</td><td style='text-align: right;'><?php echo number_format(	78.85	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	18.14	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	96.99	,2); ?> €</td><td>		</td></tr>
<tr><td>	10061	</td><td>	Raices	</td><td style='text-align: right;'><?php echo number_format(	73.3	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	16.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	90.16	,2); ?> €</td><td>		</td></tr>
<tr><td>	10023	</td><td>	Pharmacann	</td><td style='text-align: right;'><?php echo number_format(	47.75	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.98	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	58.73	,2); ?> €</td><td>		</td></tr>
<tr><td>	10012	</td><td>	Personal CannaPharmacia	</td><td style='text-align: right;'><?php echo number_format(	237.66	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	54.66	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	292.32	,2); ?> €</td><td>		</td></tr>
<tr><td>	10070	</td><td>	Originals BCN	</td><td style='text-align: right;'><?php echo number_format(	68.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	15.84	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	84.7	,2); ?> €</td><td>		</td></tr>
<tr><td>	10030	</td><td>	Mariador	</td><td style='text-align: right;'><?php echo number_format(	63.3	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	14.56	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	77.86	,2); ?> €</td><td>		</td></tr>
<tr><td>	10035	</td><td>	Maria Maria (Former Bob Green)	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10014	</td><td>	Manali	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10099	</td><td>	Mad Monkey	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>	Member module only	</td></tr>
<tr><td>	10063	</td><td>	La Roca	</td><td style='text-align: right;'><?php echo number_format(	6.66	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	1.53	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	8.19	,2); ?> €</td><td>		</td></tr>
<tr><td>	10060	</td><td>	La Gran Muralla Verde	</td><td style='text-align: right;'><?php echo number_format(	98.29	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	22.61	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	120.9	,2); ?> €</td><td>		</td></tr>
<tr><td>	10038	</td><td>	La Crème Gracia	</td><td style='text-align: right;'><?php echo number_format(	113.28	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	26.05	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	139.33	,2); ?> €</td><td>		</td></tr>
<tr><td>	10041	</td><td>	La Cremme	</td><td style='text-align: right;'><?php echo number_format(	125.49	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	28.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	154.35	,2); ?> €</td><td>		</td></tr>
<tr><td>	10055	</td><td>	La Botica Verde	</td><td style='text-align: right;'><?php echo number_format(	77.74	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	17.88	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	95.62	,2); ?> €</td><td>		</td></tr>
<tr><td>	10111	</td><td>	Iboga Buena	</td><td style='text-align: right;'><?php echo number_format(	46.09	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	10.6	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	56.69	,2); ?> €</td><td>		</td></tr>
<tr><td>	10013	</td><td>	Green Gold	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10028	</td><td>	G13	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10087	</td><td>	G Planet	</td><td style='text-align: right;'><?php echo number_format(	25.54	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	5.87	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	31.41	,2); ?> €</td><td>		</td></tr>
<tr><td>	10086	</td><td>	Friend's Lounge	</td><td style='text-align: right;'><?php echo number_format(	62.19	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	14.3	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	76.49	,2); ?> €</td><td>		</td></tr>
<tr><td>	10054	</td><td>	Fito Apotheka	</td><td style='text-align: right;'><?php echo number_format(	57.19	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	13.15	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	70.34	,2); ?> €</td><td>		</td></tr>
<tr><td>	10098	</td><td>	Fire House	</td><td style='text-align: right;'><?php echo number_format(	99.95	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	22.99	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	122.94	,2); ?> €</td><td>		</td></tr>
<tr><td>	10059	</td><td>	El Plaer De La Vida	</td><td style='text-align: right;'><?php echo number_format(	220.45	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	50.7	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	271.15	,2); ?> €</td><td>		</td></tr>
<tr><td>	10091	</td><td>	DeeDee Club	</td><td style='text-align: right;'><?php echo number_format(	216.01	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	49.68	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	265.69	,2); ?> €</td><td>		</td></tr>
<tr><td>	10037	</td><td>	Culcanna	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>	Member module only	</td></tr>
<tr><td>	10027	</td><td>	Crystal	</td><td style='text-align: right;'><?php echo number_format(	150	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	34.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	184.5	,2); ?> €</td><td>	Special agreement	</td></tr>
<tr><td>	10049	</td><td>	Creme Club	</td><td style='text-align: right;'><?php echo number_format(	150	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	34.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	184.5	,2); ?> €</td><td>	Special agreement	</td></tr>
<tr><td>	10075	</td><td>	Club Marley	</td><td style='text-align: right;'><?php echo number_format(	207.68	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	47.77	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	255.45	,2); ?> €</td><td>		</td></tr>
<tr><td>	10101	</td><td>	Club Cannábico P.K (Fósforo y Potasio)	</td><td style='text-align: right;'><?php echo number_format(	9.44	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	2.17	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	11.61	,2); ?> €</td><td>		</td></tr>
<tr><td>	10042	</td><td>	California Club	</td><td style='text-align: right;'><?php echo number_format(	194.35	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	44.7	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	239.05	,2); ?> €</td><td>		</td></tr>
<tr><td>	10026	</td><td>	C-loud	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10085	</td><td>	Boston Club	</td><td style='text-align: right;'><?php echo number_format(	78.85	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	18.14	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	96.99	,2); ?> €</td><td>		</td></tr>
<tr><td>	10094	</td><td>	Betty Boop	</td><td style='text-align: right;'><?php echo number_format(	277.64	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	63.86	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	341.5	,2); ?> €</td><td>		</td></tr>
<tr><td>	10105	</td><td>	Babel Weed	</td><td style='text-align: right;'><?php echo number_format(	24.99	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	5.75	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	30.74	,2); ?> €</td><td>		</td></tr>
<tr><td>	10051	</td><td>	B.C.N.74	</td><td style='text-align: right;'><?php echo number_format(	7.22	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	1.66	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	8.88	,2); ?> €</td><td>	Tourist account - check DB!	</td></tr>
<tr><td>	10106	</td><td>	Asociación Yellow Fingers	</td><td style='text-align: right;'><?php echo number_format(	42.76	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	9.83	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	52.59	,2); ?> €</td><td>		</td></tr>
<tr><td>	10088	</td><td>	Asociación Trébol Garden	</td><td style='text-align: right;'><?php echo number_format(	11.66	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	2.68	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	14.34	,2); ?> €</td><td>		</td></tr>
<tr><td>	10065	</td><td>	Asociación Tresor	</td><td style='text-align: right;'><?php echo number_format(	149.37	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	34.36	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	183.73	,2); ?> €</td><td>		</td></tr>
<tr><td>	10045	</td><td>	Asociación Nuevos Aires	</td><td style='text-align: right;'><?php echo number_format(	33.32	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	7.66	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	40.98	,2); ?> €</td><td>		</td></tr>
<tr><td>	10044	</td><td>	Asociación Mr. Teddy	</td><td style='text-align: right;'><?php echo number_format(	224.89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	51.72	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	276.61	,2); ?> €</td><td>		</td></tr>
<tr><td>	10050	</td><td>	Asociación Mariland BCN	</td><td style='text-align: right;'><?php echo number_format(	147.71	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	33.97	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	181.68	,2); ?> €</td><td>		</td></tr>
<tr><td>	10080	</td><td>	Asociación KYO	</td><td style='text-align: right;'><?php echo number_format(	41.09	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	9.45	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	50.54	,2); ?> €</td><td>		</td></tr>
<tr><td>	10089	</td><td>	Asociación Fumanza	</td><td style='text-align: right;'><?php echo number_format(	89	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	20.47	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	109.47	,2); ?> €</td><td>	Member module only	</td></tr>
<tr><td>	10031	</td><td>	Asociación de amigos para el estudio de la botánica Oasis Verde del Norte de la Isla (O.V.N.I)	</td><td style='text-align: right;'><?php echo number_format(	102.17	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	23.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	125.67	,2); ?> €</td><td>		</td></tr>
<tr><td>	10082	</td><td>	Asociación Cannábica Conquense Cuatroveinte	</td><td style='text-align: right;'><?php echo number_format(	43.31	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	9.96	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	53.27	,2); ?> €</td><td>		</td></tr>
<tr><td>	10103	</td><td>	Asociación Canela En Rama	</td><td style='text-align: right;'><?php echo number_format(	114.94	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	26.44	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	141.38	,2); ?> €</td><td>		</td></tr>
<tr><td>	10093	</td><td>	Asociación Aquí Ahora Club (Smoke Signals)	</td><td style='text-align: right;'><?php echo number_format(	70.52	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	16.22	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	86.74	,2); ?> €</td><td>		</td></tr>
<tr><td>	10052	</td><td>	Asociación Acharya	</td><td style='text-align: right;'><?php echo number_format(	143.26	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	32.95	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	176.21	,2); ?> €</td><td>		</td></tr>
<tr><td>	10022	</td><td>	Arbol De La Vida	</td><td style='text-align: right;'><?php echo number_format(	23.32	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	5.36	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	28.68	,2); ?> €</td><td>		</td></tr>
<tr><td>	10062	</td><td>	Aranua	</td><td style='text-align: right;'><?php echo number_format(	162.14	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	37.29	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	199.43	,2); ?> €</td><td>		</td></tr>
<tr><td>	10078	</td><td>	Apricot	</td><td style='text-align: right;'><?php echo number_format(	9.44	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	2.17	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	11.61	,2); ?> €</td><td>		</td></tr>
<tr><td>	10081	</td><td>	Amgi (JOSU ZUMETA)	</td><td style='text-align: right;'><?php echo number_format(	15.55	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	3.58	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	19.13	,2); ?> €</td><td>		</td></tr>
<tr><td>	10009	</td><td>	Abuelita Maria	</td><td style='text-align: right;'><?php echo number_format(	77.74	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	17.88	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	95.62	,2); ?> €</td><td>		</td></tr>
<tr><td>	10010	</td><td>	1900	</td><td style='text-align: right;'><?php echo number_format(	49.98	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	11.5	,2); ?> €</td><td style='text-align: right;'><?php echo number_format(	61.48	,2); ?> €</td><td>		</td></tr>



	 </tbody>
	 </table>

   
<?php displayFooter(); ?>
