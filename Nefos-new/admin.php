<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	pageStart($lang['title-administration'], NULL, NULL, "padmin", "index", $lang['global-administration'], $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>
<center>
<div class="adminbox2">
<div class="adminbox">
 <center><img src="images/admin-products.png" /></center>
 <h3>STOCK</h3>
 <table class='admintable'>
  <tr>
   <td><a href="bar-categories.php"><?php echo $lang['categories']; ?></a></td>
  </tr>
  <tr>
   <td><a href="bar-products.php"><?php echo $lang['global-products']; ?></a></td>
  </tr>
  <tr>
   <td><a href="bar-purchases.php"><?php echo $lang['purchases']; ?></a></td>
  </tr>  
  <tr>
   <td><a href="bar-open-stock.php">Open Stock</a></td>
  </tr>
  <tr>
   <td><a href="bar-sales.php"><?php echo $lang['sales']; ?></a></td>
  </tr>
  <tr>
   <td><a href="bar-stock.php">Stock status</a></td>
  </tr>
    <tr>
   <td><a href="hw-providers.php">HW Providers<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></td>
  </tr>
 </table>   
</div>
<br />
<div class="adminbox">
 <center><img src="images/admin-dispensary.png" /></center>
 <h3>SHOP</h3>
 <table class='admintable'>
  <tr>
   <td><a href="new-dispense.php">View menu</a></td>
  </tr>
  <tr>
   <td><a href="dispensary-history.php"><?php echo $lang['history']; ?></a></td>
  </tr>
  <tr>
   <td><a href="categories.php"><?php echo $lang['categories']; ?></a></td>
  </tr>
  <tr>
   <td><a href="products.php"><?php echo $lang['global-products']; ?></a></td>
  </tr>
  <tr>
   <td><a href="open-purchases.php"><?php echo $lang['open-purchases']; ?></a></td>
  </tr>
  <tr>
   <td><a href="closed-purchases.php"><?php echo $lang['closed-purchases']; ?></a></td>
  </tr>
  <tr>
   <td><a href="product-dispenses.php"><?php echo $lang['sales']; ?></a></td>
  </tr>
  <tr>
   <td><a href="close-purchase.php"><?php echo $lang['admin-closeproduct']; ?></a></td>
  </tr>
  <tr>
   <td><a href="part-map.php">Parts</a></td>
  </tr>
 </table>   
 </div>
</div>

<div class="adminbox2">
<div class="adminbox" style="width: 888px;">
 <center><img src="images/admin-users.png" /></center>
 <h3>Clients & Contacts</h3>
 <table>
 <tr>
 <td>
 <table class='admintable' style="margin-left: 20px;">
  <tr>
   <td><a href="clients.php">Clients</a></td>
  </tr>
  <tr>
   <td><a href="affiliations.php">Affiliations</a></td>
  </tr>
  <tr>
   <td><a href="professional-contacts.php">Professional contacts</a></td>
  </tr>
  <tr>
   <td><a href="calls.php">Call Log</a></td>
  </tr>
  <tr>
   <td><a href="prospects.php">Prospect tracking</a></td>
  </tr>
 </table>
 </td>
 <td>  
 <table class='admintable' style="margin-left: 64px;">
  <tr>
   <td><a href="trials.php">Trial status<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></td>
  </tr>
  <tr>
   <td><a href="pending-clubs.php">Pending club launches</a></td>
  </tr>
  <tr>
   <td><a href="notifications.php">Notifications</a></td>
  </tr>
  <tr>
   <td><a href="contact-updates.php">Contact updates</a></td>
  </tr>
  <tr>
   <td><a href="demo-views.php">Demo views</a></td>
  </tr>
 </table>   
 </td>
 <td>  
 <table class='admintable' style="margin-left: 64px;">
  <tr>
   <td><a href="status.php">Client status<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></td>
  </tr>
  <tr>
   <td><a href="help-section.php">Help Center</a></td>
  </tr>
  <tr>
   <td><a href="contract-status.php">Contract status<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></td>
  </tr>  
  <tr>
   <td><a href="change-requests.php">Change module<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></td>
  </tr>  
  <tr>
   <td><a href="custom-contracts.php">Custom Contract<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></td>
  </tr>  
  <tr>
   <td><a href="customer-surveys.php">Customer Survey<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></td>
  </tr>
 </table>   
 </table>   
 </td>
  </tr>
 </table>   
</div>
<br />
<div class="adminbox" style='height: 502px; margin-top: 5px;'>
 <center><img src="images/admin-products.png" /></center>
 <h3>SHOP ORDERS</h3>
 <table class='admintable'>
  <tr>
   <td><a href="orders.php">Orders</a></td>
  </tr>
  <tr>
   <td><a href="pending-orders.php">Pending orders</a></td>
  </tr>
  <tr>
   <td><a href="orders-cancelled.php">Cancelled Orders</a></td>
  </tr>
  <tr>
   <td><a href="abandoned-carts.php">Abandoned shopping carts<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></td>
  </tr>
  <tr>
   <td><a href="orders-hr.php">Orders HighRoller</a></td>
  </tr>
  <tr>
   <td><a href="orders-covid.php">Orders CovidShop</a></td>
  </tr>
 </table>   
</div>
<div class="adminbox" style='height: 502px; margin-top: 5px;'>
 <center><img src="images/admin-reports.png" /></center>
 <h3>FINANCE</h3>
 <table class='admintable'>
  <tr>
   <td><a href="expenses-nefos.php"><?php echo $lang['global-expenses']; ?> Nefos</a></a></td>
  </tr>
  <tr>
   <td><a href="expenses-mkl.php"><?php echo $lang['global-expenses']; ?> MKL</a></a></td>
  </tr>
  <tr>
   <td><a href="cutoff.php">Cutoff dashboard<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></td>
  </tr>
  <tr>
   <td><a href="justificantes.php">Justificantes<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></td>
  </tr>
  <tr>
   <td><a href="warnings.php">Warnings & Cutoffs</a></td>
  </tr>
  <tr>
   <td><a href="invoices.php">All client invoices</a></td>
  </tr>
  <tr>
   <td><a href="unpaid-invoices.php">Unpaid client invoices</a></td>
  </tr>
  <tr>
   <td><a href="unallocated.php">Unallocated payments<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></td>
  </tr>
  <tr>
   <td><a href="invoice-section.php">Invoice Section<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></td>
  </tr>
 </table>   
</div>

<div class="adminbox" style='height: 502px; margin-top: 5px;'>
 <center><img src="images/admin-reports.png" /></center>
 <h3>Usage & statistics</h3>
 <table class='admintable'>
  <tr>
   <td><a href="inactivity.php">Inactivity dashboard<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></td>
  </tr>
  <tr>
   <td><a href="misuse.php">Misuse of software</a></td>
  </tr>
  <tr>
   <td><a href="settings.php">System settings overview</a></td>
  </tr>
  <tr>
   <td><a href="special-orders.php">Orders & Appointments</a></td>
  </tr>
  <tr>
   <td><a href="launched-summary.php">Launched clubs per month<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></a></td>
  </tr>  
  <tr>
   <td><a href="language-strings.php">Language Strings<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></a></td>
  </tr>
 </table>
</div>




</div>
<?php displayFooter();


exit();

// Old admin panel:

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	pageStart($lang['title-administration'], NULL, NULL, "padmin", "index", $lang['global-administration'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	$userLvl = $_SESSION['userGroup'];



?>
<br /><br />

<div id="adminboxHolder">

<div class="adminbox">
 <center><img src="images/members-icon.png" /></center>
 <h3>Users</h3>
 <div class="linkholder">
  <ul>
   <li><a href="users.php">Users</a></li>
   <li><a href="new-user.php">New user</a></li>
  </ul>
 </div>
</div>
<div class="adminbox">
 <center><img src="images/members-icon.png" /></center>
 <h3>Clients & Contacts</h3>
 <div class="linkholder">
  <ul>
   <li><a href="clients.php">Clients</a></li>
   <li><a href="lawyers.php">Lawyers</a></li>
   <li><a href="accountants.php">Accountants</a></li>
   <li><a href="affiliations.php">Affiliations</a></li>
   <li><a href="calls.php">Call Log</a></li>
   <li><a href="list-clients.php">List - clients</a></li>
   <li><a href="list-contacts.php">List - contacts</a></li>
   <li><a href="list-combined.php">List - combined</a></li>
   <li><a href="client-addresses.php">Shipping addresses</a></li>
   <li><a href="prospects.php">Prospect tracking</a></li>
   <li><a href="pending-clubs.php">Pending club launches</a></li>
   <li><a href="notifications.php">Notifications</a></li>
   <li><a href="contact-updates.php">Contact updates</a></li>
   <li><a href="demo-views.php">Demo views</a></li>
   <li><a href="status.php">Client status<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></li>
   <!--<li><a href="notification-send.php">Send notifications</a></li>-->
  </ul>
 </div>
</div>

<div class="adminbox">
 <center><img src="images/club-administration.png" /></center>
 <h3>Expenses</h3>
 <div class="linkholder">
  <ul>
   <li><a href="expenses-nefos.php"><?php echo $lang['global-expenses']; ?> Nefos</a></li>
   <li><a href="expenses-mkl.php"><?php echo $lang['global-expenses']; ?> MKL</a></li>
  </ul>
 </div>
</div>
<!--
<div class="adminbox">
 <center><img src="images/club-administration.png" /></center>
 <h3>Invoicing</h3>
 <div class="linkholder">
  <ul>
   <li><a href="invoicing-2018-07.php">Invoicing Jul 2018</a></li>
   <li><a href="invoicing-2018-08.php">Invoicing Aug 2018</a></li>
   <li><a href="invoicing-2018-09.php">Invoicing Sep 2018</a></li>
   <li><a href="invoicing-2018-10.php">Invoicing Oct 2018</a></li>
   <li><a href="invoicing-2018-11.php">Invoicing Nov 2018</a></li>
   <li><a href="invoicing-2018-12.php">Invoicing Dec 2018</a></li>
   <li><a href="invoicing.php">Invoicing Jan 2019</a></li>
   <li><a href="invoicing-evolution.php">Invoice evolution</a></li>
  </ul>
 </div>
</div>
-->
<div class="adminbox">
 <center><img src="images/club-administration.png" /></center>
 <h3>Dashboards</h3>
 <div class="linkholder">
  <ul>
   <li><a href="VAT.php">VAT dashboard</a></li>
   <li><a href="activity.php">Activity dashboard</a></li>
   <li><a href="inactivity.php">Inactivity dashboard<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></li>
   <li><a href="warnings.php">Warnings & Cutoffs</a></li>
   <li><a href="misuse.php">Misuse of software</a></li>
   <li><a href="settings.php">System settings overview</a></li>
   <li><a href="invoices.php">All client invoices</a></li>
   <li><a href="unpaid-invoices.php">Unpaid client invoices</a></li>
   <li><a href="cutoff.php">Cutoff dashboard<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></li>
   <li><a href="justificantes.php">Justificantes<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></li>
   <li><a href="special-orders.php">Orders & Appointments</a></li>
   <li><a href="launched-summary.php">Launched clubs per month<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></li>
   <li><a href="language-strings.php">Language Strings<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></li>
   <li><a href="help-section.php">Help Center</a></li>
  </ul>
 </div>
</div>

<div class="adminbox">
 <center><img src="images/club-administration.png" /></center>
 <h3>CCS Shop</h3>
 <div class="linkholder">
  <ul>
   <li><a href="new-dispense.php">View menu</a></li>
   <li><a href="pending-orders.php">Pending orders</a></li>
   <li><a href="orders.php">Orders</a></li>
   <li><a href="orders-cancelled.php">Cancelled Orders</a></li>
   <li><a href="abandoned-carts.php">Abandoned shopping carts<sup style='color: red; font-size: 11px; font-weight: 600;'> NEW</sup></a></li>
   <li><a href="orders-hr.php">Orders HighRoller</a></li>
   <li><a href="orders-covid.php">Orders CovidShop</a></li>
   <li><a href="top-spenders.php"><?php echo $lang['admin-topspenders']; ?></a></li>
   <li><a href="dispensary-history.php"><?php echo $lang['history']; ?></a></li>
   <li><a href="products.php"><?php echo $lang['global-products']; ?></a></li>
   <li><a href="open-purchases.php"><?php echo $lang['open-purchases']; ?></a></li>
   <li><a href="closed-purchases.php"><?php echo $lang['closed-purchases']; ?></a></li>
   <li><a href="product-dispenses.php"><?php echo $lang['sales']; ?></a></li>
   <li><a href="close-purchase.php"><?php echo $lang['admin-closeproduct']; ?></a></li>
   <li><a href="categories.php"><?php echo $lang['categories']; ?></a></li>
   <li><a href="part-map.php">Parts</a></li>
  </ul>
 </div>
</div>

<div class="adminbox">
 <center><img src="images/bar-icon-3.png" /></center>
 <h3>Hardware</h3>
 <div class="linkholder">
  <ul>
   <li><a href="bar-categories.php"><?php echo $lang['categories']; ?></a></li>
   <li><a href="bar-products.php"><?php echo $lang['global-products']; ?></a></li>
   <li><a href="bar-purchases.php"><?php echo $lang['purchases']; ?></a></li>
   <li><a href="bar-providers.php"><?php echo $lang['providers']; ?></a></li>
   <li><a href="bar-sales.php"><?php echo $lang['sales']; ?></a></li>
   <li><a href="bar-top-spenders.php"><?php echo $lang['admin-topspenders']; ?></a></li>
   <li><a href="bar-history.php"><?php echo $lang['bar-history']; ?></a></li>
   <li><a href="bar-stock.php">Stock status</a></li>
  </ul>
 </div>
</div>


<!-- Volunteer-level menu -->

</div>

<?php

 displayFooter();


?>
