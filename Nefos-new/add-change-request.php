<?php

require_once 'cOnfig/connection.php';
require_once 'cOnfig/viewv6.php';
require_once 'cOnfig/authenticate.php';
require_once 'cOnfig/languages/common.php';

session_start();
$accessLevel = '3';

// Authenticate & authorize
authorizeUser($accessLevel);
pageStart("Add Request", NULL, null, "psales", "dispensepre", "Add Request", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
?>

<center>

<form id="registerForm" action="change-request-process.php" method="POST">
    
    <div id="mainbox-no-width">
     <div id="mainboxheader">
      New Request Change
     </div>
     <div class='boxcontent'>
             <table class="padded">
              <tr>
               <td>Topic:</td>
               <td>
                <input type="text" name="topic" class='defaultinput' required />
               </td>
              </tr>
              <tr>
                  <td style="vertical-align: middle;">Description:</td>
                  <td>
                      <textarea name="description" class="defaultinput" style="height: 120px;" required></textarea>
                  </td>
              </tr>
              <tr>
               <td>Priority:</td>
               <td>
                <!-- Group Type STSRT  -->
                <select class="defaultinput"  name="priority" required>
                    <option value="">Choose priority</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
               </td>
              </tr>

             </table>
                
     
            </div>
        </div>
            <br />
                <button type="submit" name="add_request" class='cta1'>Submit</button>
</form>

</center>
<br />
<?php displayFooter(); ?>

