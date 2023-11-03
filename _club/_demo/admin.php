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
   <td><a href="bar-open-purchases.php"><?php echo $lang['open-purchases']; ?></a></td>
  </tr>
  <tr>
   <td><a href="bar-closed-purchases.php"><?php echo $lang['closed-purchases']; ?></a></td>
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
  <tr>
   <td><a href="bar-product-movements.php"><?php echo $lang['add-movements']; ?></a></td>
  </tr>
 </table>   
 </div>
</div>

<div class="adminbox2">
<div class="adminbox" style="width: 888px;">
 <center><img src="images/admin-users.png" /></center>
 <h3><?php echo $lang['global-members']; ?></h3>
 <table>
 <tr>
 <td>
 <table class='admintable' style="margin-left: 20px;">
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
<?php if ($_SESSION['presignup'] == 0) { ?>
   <tr>
   <td><a href="pre-reg-info.php"><?php echo $lang['pre-registered']; ?>&nbsp;<sup style='color: red; font-size: 11px; font-weight: 600;'><?php echo $lang['new']; ?></sup></a></td>
  </tr>
<?php } else if ($_SESSION['presignup'] == 1) { ?>
   <tr>
   <td><a href="pre-reg.php"><?php echo $lang['pre-registered']; ?></a></td>
  </tr>
<?php } ?>
 </table>
 </td>
 <td>  
 <table class='admintable' style="margin-left: 64px;">
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
 </table>   
 </td>
 <td>  
 <table class='admintable' style="margin-left: 64px;">
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
  <tr>
   <td><a href="entrance-payments.php">Entrance Fee Payments</a></td>
  </tr>  
  <tr>
   <td><a href="queue.php">Member Queue</a></td>
  </tr>
 </table>   
 </td>
  </tr>
 </table>   
</div>
<br />
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
<?php
	if ($_SESSION['openAndClose'] == 2) {
		
   		echo "<tr><td><a href='close-day-pre.php'>{$lang['admin-closeday']}</a></td></tr>";
   		
	} else if ($_SESSION['openAndClose'] == 3) {
		
   		echo "<tr><td><a href='open-day-pre.php'>{$lang['admin-openday']}</a></td></tr>
   			  <tr><td><a href='close-day-pre.php'>{$lang['admin-closeday']}</a></td></tr>";
   		      
	} else if ($_SESSION['openAndClose'] == 4) {
		
   		echo "<tr><td><a href='open-day-pre.php'>{$lang['admin-openday']}</a></td></tr>
   			  <tr><td><a href='close-shift-pre.php'>{$lang['close-shift']}</a></td></tr>
   		      <tr><td><a href='open-shift-pre.php'>{$lang['start-shift']}</a></td></tr>
			  <tr><td><a href='close-shift-and-day-pre.php'>{$lang['close-shift-and-day']}</a></td></tr>";
   		      
	}
   
?>
  <tr>
   <td><a href="bank-money.php"><?php echo $lang['bank-money']; ?></a></td>
  </tr>
  <tr>
   <td><a href="purge.php"><?php echo $lang['reset-software']; ?></a></td>
  </tr>
  <tr>
   <td><a href="global-discounts.php"><?php echo $lang['global-discounts']; ?></a></td>
  </tr>  
  <tr>
   <td><a href="help-center.php">Help Center</a></td>
  </tr>  
  <tr>
   <td><a href="transform-product.php">Transform Product</a></td>
  </tr> 
  <tr>
   <td><a href="app-requests.php">App Member Requests</a></td>
  </tr>  
  <tr>
   <td><a href="app-notifications.php">App Notifications</a></td>
  </tr>
  <tr>
   <td><a href="mobile-app.php">Mobile app<sup style="color: red; font-size: 11px; font-weight: 600;"> NEW</sup></a></td>
  </tr>
  <?php 
      if($accessLevel == 1){
        ?>
        <tr>
          <td><a href="admin-access.php">Pages Access Settings</a></td>
        </tr>
  <?php    }
  ?>
 </table>   
</div>


<div class="adminbox">
 <center><img src="images/admin-reports.png" /></center>
 <h3><?php echo $lang['reports']; ?></h3>
 <table class='admintable'>
  <tr>
   <td><a href="status.php"><?php echo $lang['status']; ?></a></td>
  </tr>
  <tr>
   <td><a href='shifts.php'><?php echo $lang['shifts']; ?></a>
   </td>
  </tr>
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
   <td><a href="log.php"><?php echo $lang['log']; ?></a></td>   
  </tr>
  <tr>
   <td><a href="today.php"><?php echo $lang['till-today']; ?></a></td>
  </tr>
  <tr>
   <td><a href="financial-summary.php"><?php echo $lang['financial-summary']; ?></a></td>
  </tr>
  <tr>
   <td><a href="card-purchases.php"><?php echo $lang['chips-sold']; ?></a></td>
  </tr>
  <tr>
   <td><a href="product-losses.php"><?php echo $lang['losses-from-discounts-gifts']; ?></a></td>
  </tr>
<?php if ($_SESSION['workertracking'] == 1) { ?>   <tr><td><a href="worker-tracking.php"><?php echo $lang['worker-tracking']; ?></a></td></tr> <?php } ?>
  <tr>
   <td><a href="worker-module.php?user_id=<?php echo $_SESSION['user_id'] ?>"><?php echo $lang['worker-module'] ?></a></td>
  </tr>
 </table>   
</div>


</div>



<!-- Volunteer-level menu -->
<?php } else if ($userLvl < 4)  { ?>
<center>
<div class="adminbox2">
<div class="adminbox">
 <center><img src="images/admin-dispensary.png" /></center>
 <h3><?php echo $lang['global-dispensary']; ?></h3>
 <table class='admintable'>
  <tr>
   <td><a href="dispenses.php"><?php echo $lang['global-dispenses']; ?></a></td>
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
   <td><a href="bar-open-purchases.php"><?php echo $lang['open-purchases']; ?></a></td>
  </tr>
  <tr>
   <td><a href="bar-closed-purchases.php"><?php echo $lang['closed-purchases']; ?></a></td>
  </tr>
  <tr>
   <td><a href="bar-close-purchase.php"><?php echo $lang['admin-closeproduct']; ?></a></td>
  </tr>
  <tr>
   <td><a href="bar-sales.php"><?php echo $lang['sales']; ?></a></td>
  </tr>
  <tr>
   <td><a href="bar-product-movements.php"><?php echo $lang['add-movements']; ?></a></td>
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
   <td><a href="member-signups.php"><?php echo $lang['admin-signups']; ?></a></td>
  </tr>
  <tr>
   <td><a href="members-full.php"><?php echo $lang['admin-memberlist']; ?></a></td>
  </tr>
  <tr>
   <td><a href="non-members.php"><?php echo $lang['non-members']; ?></a></td>
  </tr>
<?php if ($_SESSION['presignup'] == 0) { ?>
   <tr>
   <td><a href="pre-reg-info.php"><?php echo $lang['pre-registered']; ?>&nbsp;<sup style='color: red; font-size: 11px; font-weight: 600;'><?php echo $lang['new']; ?></sup></a></td>
  </tr>
<?php } else if ($_SESSION['presignup'] == 1) { ?>
   <tr>
   <td><a href="pre-reg.php"><?php echo $lang['pre-registered']; ?></a></td>
  </tr>
<?php } ?>
  <tr>
   <td><a href="email.php"><?php echo $lang['admin-email']; ?></a></td>
  </tr>
  <tr>
   <td><a href="only-email.php"><?php echo $lang['email-list']; ?></a></td>
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
   <td><a href="expenses.php"><?php echo $lang['global-expenses']; ?></a></td>
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
   <td><a href="today.php"><?php echo $lang['till-today']; ?></a></td>
  </tr>
 </table>   
</div>


<?php }
