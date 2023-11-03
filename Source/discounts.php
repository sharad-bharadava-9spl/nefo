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
	
	// Query to look up users
	$selectUsers = "SELECT COUNT(user_id) FROM users WHERE memberno <> '0' AND userGroup < 7";
	$rowCount = $pdo3->query("$selectUsers")->fetchColumn();
	
	
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.discount, u.discountBar FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND memberno <> '0' AND u.userGroup < 7 ORDER by u.memberno ASC LIMIT 1000";
	

	try
	{
		$results = $pdo3->prepare("$selectUsers");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
		
	
		
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
		    
			$("#xllink").click(function(){

			  $("#dayByDay").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "Socios",
			    filename: "Socios" //do not include extension
		
			  });
		
			});
		    
		    
		    
			$('#cloneTable').width($('#dayByDay').width());
			
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			
			
			$('#dayByDay').tablesorter({
				usNumberFormat: true
			}); 

		
		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;


	pageStart($lang['discounts'], NULL, $memberScript, "pmembership", NULL, $lang['discounts'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center>
	 <table id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table>
<br />
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th class='centered'>C</th>
	    <th class='centered'>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['global-dispensary']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th style='color: #a80082;'><?php echo $lang['bar']; ?></th>
	    <th style='color: #a80082;'><?php echo $lang['global-category']; ?></th>
	    <th style='color: #a80082;'><?php echo $lang['global-product']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
while ($user = $results->fetch()) {

	
	$checkCatDiscount = "SELECT SUM(discount) from catdiscounts WHERE user_id = {$user['user_id']}";
	try
	{
		$result = $pdo3->prepare("$checkCatDiscount");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$catDiscount = $row['SUM(discount)'];
		$catDiscountRaw = $row['SUM(discount)'];
		
	if ($catDiscount > 0 || $catDiscount < 0) {
		$catDiscount = $lang['global-yes'];
	} else {
		$catDiscount = '';
	}

	$checkIndDiscount = "SELECT SUM(discount) from inddiscounts WHERE user_id = {$user['user_id']}";
	try
	{
		$result = $pdo3->prepare("$checkIndDiscount");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$indDiscount = $row['SUM(discount)'];
		$indDiscountRaw = $row['SUM(discount)'];
		
	if ($indDiscount > 0 || $indDiscount < 0) {
		$indDiscount = $lang['global-yes'];
	} else {
		$indDiscount = '';
	}

	$checkCatDiscountBar = "SELECT SUM(discount) from b_catdiscounts WHERE user_id = {$user['user_id']}";
	try
	{
		$result = $pdo3->prepare("$checkCatDiscountBar");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		$catDiscountBar = $row['SUM(discount)'];
		$catDiscountBarRaw = $row['SUM(discount)'];
		
	if ($catDiscountBar > 0 || $catDiscountBar < 0) {
		$catDiscountBar = $lang['global-yes'];
	} else {
		$catDiscountBar = '';
	}

	$checkIndDiscountBar = "SELECT SUM(discount) from b_inddiscounts WHERE user_id = {$user['user_id']}";
	try
	{
		$result = $pdo3->prepare("$checkIndDiscountBar");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		$indDiscountBar = $row['SUM(discount)'];
		$indDiscountBarRaw = $row['SUM(discount)'];
		
	if ($indDiscountBar > 0 || $indDiscountBar < 0) {
		$indDiscountBar = $lang['global-yes'];
	} else {
		$indDiscountBar = '';
	}
	
	if ($user['usageType'] == '1') {
		$usageType = "<img src='images/medical.png' width='16' /><span style='display:none'>1</span>";
	} else {
		$usageType = '';
	}
	
	
	if ($starCat == 1) {
   		$userStar = "<img src='images/star-yellow.png' width='16' /><span style='display:none'>1</span>";
	} else if ($starCat == 2) {
   		$userStar = "<img src='images/star-black.png' width='16' /><span style='display:none'>2</span>";
	} else if ($starCat == 3) {
   		$userStar = "<img src='images/star-green.png' width='16' /><span style='display:none'>3</span>";
	} else if ($starCat == 4) {
   		$userStar = "<img src='images/star-red.png' width='16' /><span style='display:none'>4</span>";
	} else {
   		$userStar = "<span style='display:none'>0</span>";
	}
	
	$discountSum = $catDiscountRaw + $indDiscountRaw + $catDiscountBarRaw + $indDiscountBarRaw + $user['discount'] + $user['discountBar'];

	if ($discountSum > 0) {
	
	echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow centered' style='text-align: center;' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow centered' href='mini-profile.php?user_id=%d'>%d%%</td>
  	   <td class='clickableRow centered' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow centered' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow centered' href='mini-profile.php?user_id=%d'>%d%%</td>
  	   <td class='clickableRow centered' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow centered' href='mini-profile.php?user_id=%d'>%s</td></tr>
  	   ",
	  $user['user_id'], $userStar, $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], $usageType, $user['user_id'], $user['discount'], $user['user_id'], $catDiscount, $user['user_id'], $indDiscount, $user['user_id'], $user['discountBar'], $user['user_id'], $catDiscountBar, $user['user_id'], $indDiscountBar);
	  
  	}
  	
	  
  }
  
echo <<<EOD
   <tr id="loadMore">
  <td class="centered" colspan="11" style='border-bottom: 0;'>(1000 / $rowCount {$lang['global-members']} {$lang['loaded']})<br /><a href="#" onclick="event.preventDefault(); loadMoreDays()" style='font-size: 14px;'>{$lang['load-more']}  </a></td>
 </tr>
EOD;

?>

<script>
function loadMoreDays(){
	
	// Add 'Loading' text
	$("#loadMore").remove();
	$("#dayByDay").append("<tr id='dayLoading'><td colspan='3' class='centered'>Loading. Please wait...</td></tr>");

	// Take dayID, send it to Ajax
	var dayJSID = parseInt($("#dayID").val());	
	var totRow = parseInt($("#totrows").val());	
    $.ajax({
      type:"post",
      url:"getdiscounts.php?day="+dayJSID+"&totrows="+totRow,
      datatype:"text",
      success:function(data)
      {
			$("#dayLoading").remove();
	       	$('#dayByDay tbody').append(data);
      }
    });
	
	$("#dayID").val(dayJSID + 1000);
    
};

</script>

	 </tbody>
	 </table>
<form>
 <input type="hidden" id="dayID" value="1000" />
 <input type="hidden" id="totrows" value="<?php echo $rowCount; ?>" />
 
 
</form>


<?php  displayFooter(); ?>
