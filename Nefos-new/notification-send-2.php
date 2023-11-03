<?php
// Begin code sagar
require_once 'cOnfig/connection.php';
require_once 'cOnfig/viewv6.php';
require_once 'cOnfig/authenticate.php';
require_once 'cOnfig/languages/common.php';

session_start();
$accessLevel = '3';

// Authenticate & authorize
authorizeUser($accessLevel);
pageStart("Send notification", NULL, null, "psales", "dispensepre", "Send notification", $_SESSION['successMessage'], $_SESSION['errorMessage']);
// Group Type Add.
/*$group = array(
    1 => "Administrador",
    2 => "Trabajador",
    3 => "Voluntario",
    4 => "Contacto profesional",
    5 => "Socio",
    6 => "Interesado",
    7 => "Expulsado",
    8 => "Borrado",
    9 => "Bajado",
);*/
$group = array(
    1 => "Administrator",
    2 => "Staff",
    3 => "Volunteer",
);

	$type = $_GET['type'];
	
	if ($type == 1) {
		
		$text_en = "Click here to update your contact information";
		$text_es = "Pincha aqui para actualizar los datos de contacto del club";
		$text_ca = "Punxa aquí per actualitzar les dades de contacte de el club";
		$text_fr = "Cliquez ici pour mettre à jour vos coordonnées";
		$text_nl = "Klik hier om contactgegevens bij te werken";
		$text_it = "Fai clic qui per aggiornare i dettagli di contatto del club";
		$url = "https://ccsnubev2.com/v6/update-club.php";
		$action = "selected";
		
	}
		$lang['click-to-update-contact'];
	
	// Types: 0 = text, 1 = contact update, 2 = calendar invite, 3 = New invoice, 4 = Software update, 5 = help center ticket, 6 = Stock notification

	
?>


<center>

    <form id="registerForm" action="send-club-notification.php" method="POST">
    <input type="hidden" name="category" value="<?php echo $type; ?>" />
<div id="mainbox-no-width">
 <div id="mainboxheader">
  New notification
 </div>
 <div class='boxcontent'>
         <table class="padded">
          <tr>
           <td>Choose club:</td>
           <td>
                <select class="defaultinput" id="multiple_selection" name="userSelect[]" required multiple="multiple">
                    <?php
                    // Query to look up pre-registered users:
                    $selectUsers = "SELECT c.id, c.brand, c.registeredSince, c.number, c.shortName, c.city, c.status, c.type, c.private, s.statusName, c.contract FROM customers c, customerstatus s WHERE c.status = s.id AND c.number NOT LIKE ('9%') ORDER by c.number ASC";
                    try {
                        $results = $pdo3->prepare("$selectUsers");
                        $results->execute();
                    } catch (PDOException $e) {
                        $error = 'Error fetching user: ' . $e->getMessage();
                        echo $error;
                        exit();
                    }
                    while ($user = $results->fetch()) {
 	                        $number = $user['number'];
 	                        
                       if ($user['statusName'] != 'Dead' && $number != 0) {
	                        
	                        // Check to see if database exists
	                        
							$query = "SELECT domain, db_pwd FROM db_access WHERE customer = '$number'";
							//echo "$query<br />";
							try
							{
								$result = $pdo->prepare("$query");
								$result->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									//exit();
							}
													
							$row = $result->fetch();
								$db_pwd = $row['db_pwd'];
								$domain = $row['domain'];
								$db_name = "ccs_" . $domain;
								$db_user = $db_name . "u";
								
								
							$query = "SHOW DATABASES LIKE '$db_name'";
							try
							{
								$result = $pdoFULL->prepare("$query");
								$result->execute();
								$data = $result->fetchAll();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									exit();
							}
								
							if ($data) {
								
                            $user_row = sprintf(
                                "<option value='%d'>%s</option>",
                                $user['id'],
                                $user['shortName']
                            );
                            echo $user_row;
								
								
							}
								/*
								
								echo "<br />$domain $number $db_name $db_user $db_pwd<br />";
						
								try	{
							 		$pdoN = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
							 		$pdoN->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
							 		$pdoN->exec('SET NAMES "utf8"');
								}
								catch (PDOException $e)	{
							  		$output = 'Unable to connect to the database server: ' . $e->getMessage();
							 		echo "$output<br />";
							 		$exclude .= $exclude . "$number ,";
							 		//exit();
								}
								//echo "H: $exclude";
								
	                        
                            $user_row = sprintf(
                                "<option value='%d'>%s</option>",
                                $user['id'],
                                $user['shortName']
                            );
                            echo $user_row;
                            */
                        }
                    }
                    
                   //  echo "HER: $exclude";
                    ?>
               </select>
          </td>
          </tr>
          <tr>
           <td>Choose groups:</td>
           <td>
            <!-- Group Type STSRT  -->
            <select class="defaultinput" id="group_multiple_selection" name="group[]" multiple="multiple">
                <?php foreach ($group as $key => $value) { ?>
                    <option value='<?php echo $key ?>'><?php echo $value ?></option>
                <?php } ?>
            </select>
           </td>
          </tr>
          <tr>
           <td>English:</td>
           <td>
            <input type="text" name="notification" class='defaultinput' required value="<?php echo $text_en; ?>" />
           </td>
          </tr>
          <tr>
           <td>Spanish:</td>
           <td>
            <input type="text" name="notification_es" class='defaultinput' required value="<?php echo $text_es; ?>" />
           </td>
          </tr>
          <tr>
           <td>Catalan:</td>
           <td>
            <input type="text" name="notification_ca" class='defaultinput' required value="<?php echo $text_ca; ?>" />
           </td>
          </tr>
          <tr>
           <td>French:</td>
           <td>
            <input type="text" name="notification_fr" class='defaultinput' required value="<?php echo $text_fr; ?>" />
           </td>
          </tr>
          <tr>
           <td>Dutch:</td>
           <td>
            <input type="text" name="notification_nl" class='defaultinput' required value="<?php echo $text_nl; ?>" />
           </td>
          </tr>
          <tr>
           <td>Italian:</td>
           <td>
            <input type="text" name="notification_it" class='defaultinput' required value="<?php echo $text_it; ?>" />
           </td>
          </tr>
         </table>
            
            <?php if ($type == 1) { ?>
            <br /><br />
            
            <select name="type" id="type_of_notification" class="fakeInput defaultinput" >
                <option value="0" >Normal</option>
                <option value="1" <?php echo $action; ?>>Action</option>
            </select>
            <br/><br/>
            <input type="text" name="url" id="action_link" class='defaultinput' placeholder="Paste URL here" value="<?php echo $url; ?>">
            <!-- Group Type END  -->
            <br />
            <br />
            <?php } ?>
        </div>
        </div>
        <br />
            <button type="submit" class='cta1'><?php echo $lang['global-select']; ?></button>
    </form>

</center>
<br />
<?php displayFooter(); ?>
<!-- End code sagar -->
<!-- Start code sagar ############ For Multi-Selection  -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/jquery.sumoselect/3.0.2/sumoselect.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.sumoselect/3.0.2/jquery.sumoselect.min.js"></script>
<script>
    $(document).ready(function() {
        $('#multiple_selection').SumoSelect({
            selectAll: true,
            search: true,
            searchText: 'Select Club.'
        });
        $('#group_multiple_selection').SumoSelect({
            selectAll: true,
            search: true,
            searchText: 'Select Club Group.'
        });

        //$('#action_link').hide();
        $('#type_of_notification').on('change', function(){
	
            if( $('#type_of_notification').val() == 'normal' ) {
                $('#action_link').hide();
            } else {
                $('#action_link').show();
            }
        });

    });
</script>
<!-- End code sagar -->
