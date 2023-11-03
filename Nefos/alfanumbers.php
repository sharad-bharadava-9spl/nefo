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
	$selectUsers = "SELECT user_id, memberno, first_name, last_name FROM users ORDER by registeredSince ASC";
		
	$resultUser = mysql_query($selectUsers)
		or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
		

	pageStart($lang['index-members'], NULL, $memberScript, "pmembership", NULL, $lang['index-membersC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	while ($user = mysql_fetch_array($resultUser)) {

		$user_id = $user['user_id'];
		$first_name = $user['first_name'];
		$last_name = $user['last_name'];
		$memberno = $user['memberno'];
		
$pattern = array("'é'", "'è'", "'ë'", "'ê'", "'É'", "'È'", "'Ë'", "'Ê'", "'á'", "'à'", "'ä'", "'â'", "'å'", "'Á'", "'À'", "'Ä'", "'Â'", "'Å'", "'ó'", "'ò'", "'ö'", "'ô'", "'Ó'", "'Ò'", "'Ö'", "'Ô'", "'í'", "'ì'", "'ï'", "'î'", "'Í'", "'Ì'", "'Ï'", "'Î'", "'ú'", "'ù'", "'ü'", "'û'", "'Ú'", "'Ù'", "'Ü'", "'Û'", "'ý'", "'ÿ'", "'Ý'", "'ø'", "'Ø'", "'œ'", "'Œ'", "'Æ'", "'ç'", "'Ç'", "'ñ'", "'Ñ'");
$replace = array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E', 'a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O', 'i', 'i', 'i', 'I', 'I', 'I', 'I', 'I', 'u', 'u', 'u', 'u', 'U', 'U', 'U', 'U', 'y', 'y', 'Y', 'o', 'O', 'a', 'A', 'A', 'c', 'C', 'n', 'N'); 

$first_name = preg_replace($pattern, $replace, $first_name);
$last_name = preg_replace($pattern, $replace, $last_name);

		
		$memberInitials = strtoupper(substr($first_name, 0,1)) . strtoupper(substr($last_name, 0,1));
		$memberDigit = 1;
		
		$memberno = $memberInitials . $memberDigit;
		
		
		$memberMatch = 'false';
		
		while ($memberMatch == 'false') {
			
			// We've gotta check if the member number is available!
			$query = "SELECT memberno FROM users WHERE memberno = '$memberno'";
			
			$result = mysql_query($query)
				or handleError($lang['error-membershipnumberload'],"");
				
			if (mysql_num_rows($result) == 0) {
				
				$memberMatch = 'true';
				
			} else {
				
				// Means the number is taken, so increase by 1 and try again
				$memberDigit = $memberDigit + 1;
				$memberno = $memberInitials . $memberDigit;
				
			}
			
		}
		
		$updateUser = "UPDATE users SET memberno = '$memberno' WHERE user_id = $user_id";
		
		mysql_query($updateUser);
		
		echo "$i $first_name $last_name<br />$updateUser<br /><br />";
	
		$i++;
	  
  	}
?>

	 </tbody>
	 </table>

<?php  displayFooter(); ?>
