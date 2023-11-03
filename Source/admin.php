<!-- Admin-level menu -->
<?php if ($userLvl < 2) { ?>
<center>
<div class="adminbox2">
<div class="adminbox">
 <center><img src="images/admin-dispensary.png" /></center>
 <h3><?php echo $lang['global-dispensary']; ?></h3>
 <table class='admintable'>
  <tr>
   <td><a href="dispenses.php"><?php echo $lang['global-dispenses']; ?></a></td>
  </tr>
  <tr>
   <td><a href="top-spenders.php"><?php echo $lang['admin-topspenders']; ?></a></td>
  </tr>
  <tr>
   <td><a href="dispensary-history.php"><?php echo $lang['admin-revenue']; ?></a></td>
  </tr>
 </table>   
</div>
<br />
<div class="adminbox">
 <center><img src="images/admin-bar.png" /></center>
 <h3>Bar</h3>
 <table class='admintable'>
  <tr>
   <td><a href="bar-categories.php"><?php echo $lang['categories']; ?></a></td>
  </tr>
  <tr>
   <td><a href="bar-products.php"><?php echo $lang['global-products']; ?></a></td>
  </tr>
  <tr>
   <td><a href="bar-providers.php"><?php echo $lang['providers']; ?></a></td>
  </tr>
  <tr>
   <td><a href="bar-purchases.php"><?php echo $lang['purchases']; ?></a></td>
  </tr>
  <tr>
   <td><a href="bar-close-purchase.php"><?php echo $lang['admin-closeproduct']; ?></a></td>
  </tr>
  <tr>
   <td><a href="bar-sales.php"><?php echo $lang['sales']; ?></a></td>
  </tr>
  <tr>
   <td><a href="bar-top-spenders.php"><?php echo $lang['admin-topspenders']; ?></a></td>
  </tr>
  <tr>
   <td><a href="bar-history.php"><?php echo $lang['bar-history']; ?></a></td>
  </tr>
 </table>   
 </div>
</div>

<div class="adminbox">
 <center><img src="images/admin-users.png" /></center>
 <h3><?php echo $lang['global-members']; ?></h3>
 <table class='admintable'>
  <tr>
   <td><a href="member-statistics.php"><?php echo $lang['admin-statistics']; ?></a></td>
  </tr>
  <tr>
   <td><a href="members-dispensed.php"><?php echo $lang['members-dispensed-history']; ?></a></td>
  </tr>
  <tr>
   <td><a href="member-signups.php"><?php echo $lang['admin-signups']; ?></a></td>
  </tr>
  <tr>
   <td><a href="members-full.php"><?php echo $lang['admin-memberlist']; ?></a></td>
  </tr>
  <tr>
   <td><a href="non-members.php"><?php echo $lang['non-members']; ?></a></td>
  </tr>
  <tr>
   <td><a href="email.php"><?php echo $lang['admin-email']; ?></a></td>
  </tr>
  <tr>
   <td><a href="only-email.php"><?php echo $lang['email-list']; ?></a></td>
  </tr>
  <tr>
   <td><a href="discounts.php"><?php echo $lang['discounts']; ?></a></td>
  </tr>
  <tr>
   <td><a href="visits.php"><?php echo $lang['visits']; ?></a></td>
  </tr>
  <tr>
   <td><a href="member-payments.php"><?php echo $lang['membership-payments']; ?></a></td>
  </tr>
  <tr>
   <td><a href="donations.php"><?php echo $lang['global-donations']; ?></a></td>
  </tr>
  <tr>
   <td><a href="donations-per-member.php"><?php echo $lang['global-donations']; ?> total</a></td>   
  </tr>
  <tr>
   <td><a href="give-baja.php"><?php echo $lang['give-baja']; ?></a></td>
  </tr>
  <tr>
   <td><a href="usergroups.php"><?php echo $lang['usergroups']; ?></a></td>
  </tr>
  <tr>
   <td><a href="rejected.php"><?php echo $lang['reject-list']; ?></a></td>
  </tr>
  <tr>
   <td><a href="donated-dispensed.php"><?php echo $lang['donations-vs-dispenses']; ?></a></td>
  </tr>
 </table>   
</div>

<div class="adminbox">
 <center><img src="images/admin-products.png" /></center>
 <h3><?php echo $lang['global-products']; ?></h3>
 <table class='admintable'>
  <tr>
   <td><a href="categories.php"><?php echo $lang['categories']; ?></a></td>
  </tr>
  <tr>
   <td><a href="products.php"><?php echo $lang['global-products']; ?></a></td>
  </tr>
  <tr>
   <td><a href="providers.php"><?php echo $lang['providers']; ?></a></td>
  </tr>
  <tr>
   <td><a href="open-purchases.php"><?php echo $lang['open-purchases']; ?></a></td>
  </tr>
  <tr>
   <td><a href="closed-purchases.php"><?php echo $lang['closed-purchases']; ?></a></td>
  </tr>
  <tr>
   <td><a href="stock.php"><?php echo $lang['global-stock']; ?></a></td>
  </tr>
  <tr>
   <td><a href="product-movements.php"><?php echo $lang['add-movements']; ?></a></td>
  </tr>
  <tr>
   <td><a href="product-dispenses.php"><?php echo $lang['global-dispenses']; ?></a></td>
  </tr>
  <tr>
   <td><a href="close-purchase.php"><?php echo $lang['admin-closeproduct']; ?></a></td>
  </tr>
 </table>   
</div>


<div class="adminbox">
 <center><img src="images/admin-admin.png" /></center>
 <h3><?php echo $lang['admin-clubadmin']; ?></h3>
 <table class='admintable'>
  <tr>
   <td><a href="invoices.php"><?php echo $lang['my-invoices']; ?></a></td>
  </tr>
  <tr>
   <td><a href="sys-settings.php"><?php echo $lang['system-settings']; ?></a></td>
  </tr>
  <tr>
   <td><a href="expenses.php"><?php echo $lang['global-expenses']; ?></a></td>
  </tr>
  <tr>
   <td><a href="expense-categories.php"><?php echo $lang['expense-categories']; ?></a></td>
  </tr>
  <tr>
   <td><a href="status.php"><?php echo $lang['status']; ?></a></td>
  </tr>   
<?php
	if ($_SESSION['openAndClose'] == 2) {
		
   		echo "<tr><td><a href='close-day-pre.php'>{$lang['admin-closeday']}</a></td></tr>
   			  <tr><td><a href='shifts.php'>{$lang['shifts']}</a></td></tr>";
   		
	} else if ($_SESSION['openAndClose'] == 3) {
		
   		echo "<tr><td><a href='open-day-pre.php'>{$lang['admin-openday']}</a></td></tr>
   			  <tr><td><a href='close-day-pre.php'>{$lang['admin-closeday']}</a></td></tr>
   			  <tr><td><a href='shifts.php'>{$lang['shifts']}</a></td></tr>";
   		      
	} else if ($_SESSION['openAndClose'] == 4) {
		
   		echo "<tr><td><a href='open-day-pre.php'>{$lang['admin-openday']}</a></td></tr>
   			  <tr><td><a href='close-shift-pre.php'>{$lang['close-shift']}</a></td></tr>
   		      <tr><td><a href='open-shift-pre.php'>{$lang['start-shift']}</a></td></tr>
			  <tr><td><a href='close-shift-and-day-pre.php'>{$lang['close-shift-and-day']}</a></td></tr>
			  <tr><td><a href='shifts.php'>{$lang['shifts']}</a></td></tr>";
   		      
	}
   
?>
  <tr>
   <td><a href="daily-reporting.php"><?php echo $lang['daily-reports']; ?></a></td>
  </tr>
  <tr>
   <td><a href="month-report.php"><?php echo $lang['monthly-report']; ?></a></td>
  </tr>
  <tr>
   <td><a href="revenue-history.php"><?php echo $lang['revenue-history']; ?></a></td>
  </tr>
  <tr>
   <td><a href="profit-loss.php"><?php echo $lang['revenue-and-expenses']; ?></a></td>
  </tr>
  <tr>
   <td><a href="bank-money.php"><?php echo $lang['bank-money']; ?></a></td>
  </tr>
  <tr>
   <td><a href="log.php"><?php echo $lang['log']; ?></a></td>   
  </tr>
  <tr>
   <td><a href="today.php"><?php echo $lang['till-today']; ?></a></td>
  </tr>
  <tr>
   <td><a href="financial-summary.php"><?php echo $lang['financial-summary']; ?></a></td>
  </tr>
  <tr>
   <td><a href="product-losses.php"><?php echo $lang['losses-from-discounts-gifts']; ?></a></td>
  </tr>
  <tr>
   <td><a href="purge.php"><?php echo $lang['reset-software']; ?></a></td>
  </tr>
  
<?php if ($_SESSION['workertracking'] == 1) { ?>   <tr><td><a href="worker-tracking.php"><?php echo $lang['worker-tracking']; ?></a></td></tr> <?php } ?>

  <tr>
   <td><a href="global-discounts.php"><?php echo $lang['global-discounts']; ?></a></td>
  </tr>
 
 </table>   
</div>


<style>
form {
	display: inline;
	
	cursor: pointer;
}
button {
	border-radius: 3px;
	background-color: white;
	padding: 10px;
	text-align: center;
	line-height: 1.4em;
	border: 3px solid #8f006e;
	color: #a80082;
	font-size: 15px;
	padding-bottom: 5px;
	height: 100px !important;
	font-weight: 800;
	cursor: pointer;
}

</style>
<div class="adminbox2">
<form id="highroller" action="/HighRoller/index.php" method="POST">
<input type="hidden" name="domain" value="<?php echo $domain; ?>" />
<button type="submit">
<center><img src="images/shop-icon.png" /></center>
HIGH ROLLER
</button>
</form><br />
<form id="highroller" action="/CCS/index.php" method="POST">
<input type="hidden" name="domain" value="<?php echo $domain; ?>" />
<button type="submit">
<center><img src="images/shop-icon.png" /></center>
TIENDA CCS
</button>
</form>
</div>
</center>

<!-- Volunteer-level menu -->
<?php } else if ($userLvl < 4)  { ?>

<div class="adminbox">
 <center><img src="images/dispensary-icon.png" /></center>
 <h3><?php echo $lang['global-dispensary']; ?></h3>
 <div class="linkholder">
  <ul>
   <li><a href="dispenses.php"><?php echo $lang['global-dispenses']; ?></a></li>
  </ul>
 </div>
</div>

<div class="adminbox">
 <center><img src="images/members-icon.png" /></center>
 <h3><?php echo $lang['global-members']; ?></h3>
 <div class="linkholder">
  <ul>
   <li><a href="member-statistics.php"><?php echo $lang['admin-statistics']; ?></a></li>
   <li><a href="member-signups.php"><?php echo $lang['admin-signups']; ?></a></li>
   <li><a href="members-full.php"><?php echo $lang['admin-memberlist']; ?></a></li>
   <li><a href="non-members.php"><?php echo $lang['non-members']; ?></a></li>
   <li><a href="email.php"><?php echo $lang['admin-email']; ?></a></li>
   <li><a href="only-email.php"><?php echo $lang['email-list']; ?></a></li>
   <li><a href="visits.php"><?php echo $lang['visits']; ?></a></li>
   <li><a href="member-payments.php"><?php echo $lang['membership-payments']; ?></a></li>
   <li><a href="donations.php"><?php echo $lang['global-donations']; ?></a></li>
   <li><a href="donated-dispensed.php"><?php echo $lang['donations-vs-dispenses']; ?></a></li>
   <li><a href="rejected.php"><?php echo $lang['reject-list']; ?></a></li>
  </ul>
 </div>
</div>
<div class="adminbox">
 <center><img src="images/products-icon-2.png" /></center>
 <h3><?php echo $lang['global-products']; ?></h3>
 <div class="linkholder">
  <ul>
   <li><a href="categories.php"><?php echo $lang['categories']; ?></a></li>
   <li><a href="products.php"><?php echo $lang['global-products']; ?></a></li>
   <li><a href="open-purchases.php"><?php echo $lang['open-purchases']; ?></a></li>
   <li><a href="closed-purchases.php"><?php echo $lang['closed-purchases']; ?></a></li>
   <li><a href="stock.php"><?php echo $lang['global-stock']; ?></a></li>
   <li><a href="product-movements.php"><?php echo $lang['add-movements']; ?></a></li>
   <li><a href="product-dispenses.php"><?php echo $lang['global-dispenses']; ?></a></li>
   <li><a href="close-purchase.php"><?php echo $lang['admin-closeproduct']; ?></a></li>
  </ul>
 </div>
</div>

<div class="adminbox">
 <center><img src="images/club-administration.png" /></center>
 <h3><?php echo $lang['admin-clubadmin']; ?></h3>
 <div class="linkholder">
  <ul>
   <li><a href="expenses.php"><?php echo $lang['global-expenses']; ?></a></li>
<?php
	if ($_SESSION['openAndClose'] == 2) {
		
   		echo "<li><a href='close-day-pre.php'>{$lang['admin-closeday']}</a></li>";
   		
	} else if ($_SESSION['openAndClose'] == 3) {
		
   		echo "<li><a href='open-day-pre.php'>{$lang['admin-openday']}</a></li>
   			  <li><a href='close-day-pre.php'>{$lang['admin-closeday']}</a></li>";
   		      
	} else if ($_SESSION['openAndClose'] == 4) {
		
   		echo "<li><a href='open-day-pre.php'>{$lang['admin-openday']}</a></li>
   			  <li><a href='close-shift-pre.php'>{$lang['close-shift']}</a></li>
   		      <li><a href='open-shift-pre.php'>{$lang['start-shift']}</a></li>
			  <li><a href='close-shift-and-day-pre.php'>{$lang['close-shift-and-day']}</a></li>";
   		      
	}
   
?>
   <li><a href="today.php"><?php echo $lang['till-today']; ?></a></li>
  </ul>
 </div>
</div>


<div class="adminbox">
 <center><img src="images/bar-icon-2.png" /></center>
 <h3>Bar</h3>
 <div class="linkholder">
  <ul>
   <li><a href="bar-categories.php"><?php echo $lang['categories']; ?></a></li>
   <li><a href="bar-products.php"><?php echo $lang['global-products']; ?></a></li>
   <li><a href="bar-purchases.php"><?php echo $lang['purchases']; ?></a></li>
   <li><a href="bar-providers.php"><?php echo $lang['providers']; ?></a></li>
   <li><a href="bar-close-purchase.php"><?php echo $lang['admin-closeproduct']; ?></a></li>
   <li><a href="bar-sales.php"><?php echo $lang['sales']; ?></a></li>
  </ul>
 </div>
</div>

<style>
form {
	display: inline;
	
	cursor: pointer;
}
button {
	border-radius: 3px;
	background-color: white;
	padding: 10px;
	text-align: center;
	line-height: 1.4em;
	border: 3px solid #8f006e;
	color: #a80082;
	font-size: 15px;
	padding-bottom: 5px;
	height: 100px !important;
	font-weight: 800;
	cursor: pointer;
}

</style>
 <div class="linkholder" style='margin-top: 8px;'>

<form id="highroller" action="/HighRoller/index.php" method="POST">
<input type="hidden" name="domain" value="<?php echo $domain; ?>" />
<button type="submit">
<center><img src="images/shop-icon.png" /></center>
HIGH ROLLER
</button>
</form><br />
<form id="highroller" action="/CCS/index.php" method="POST">
<input type="hidden" name="domain" value="<?php echo $domain; ?>" />
<button type="submit">
<center><img src="images/shop-icon.png" /></center>
TIENDA CCS
</button>
</form>
</div>

<?php } ?>