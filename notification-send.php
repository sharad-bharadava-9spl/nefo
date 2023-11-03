<?php
// created by konstant for notification panel on 28-06-2022
require_once 'cOnfig/connection.php';
require_once 'cOnfig/view.php';
require_once 'cOnfig/authenticate.php';
require_once 'cOnfig/languages/common.php';

//session_start();
$accessLevel = '3';

// Authenticate & authorize
authorizeUser($accessLevel);

$validationScript = <<<EOD
    $(document).ready(function() {
            
      $('#registerForm').validate({
          rules: {

        }, // end rules
          errorPlacement: function(error, element) {
             if ( element.is(":radio") || element.is(":checkbox")){
                 error.appendTo(element.parent());
            } else {
                return true;
            }
        }
         
         
      }); // end validate


  }); // end ready
EOD;

pageStart("Send notification", NULL, $validationScript, "pmembership", NULL, "Send notification", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
?>

<center>
    <form id="registerForm" action="send-notification-process.php" enctype="multipart/form-data" method="POST">
        <div id="mainbox-no-width">
             <div id="mainboxheader">
              New notification
             </div>
            <div class='boxcontent'>
                <table class="padded">
                      <tr>
                       <td><strong>Title:</strong></td>
                       <td>
                        <input type="text" name="title" class='defaultinput' required  />
                       </td>
                      </tr>         
                       <tr>
                       <td style="vertical-align:middle;"><strong>Content:</strong></td>
                       <td>
                        <textarea name="content" class='defaultinput' required style="height: 200px;"></textarea>
                       </td>
                      </tr>           
                      <tr>
                       <td><strong>Image:</strong></td>
                       <td>
                        <input type="file" name="note_image" class='defaultinput'  />
                       </td>
                      </tr>
                      <tr>
                        <td><strong>Notification Type:</strong></td>
                        <td>
                            <input type="radio" name="note_type" value="1" required> Success
                            <input type="radio" name="note_type" value="2"> Info
                            <input type="radio" name="note_type" value="3"> Alert
                            <input type="radio" name="note_type" value="4"> Warning
                        </td>
                      </tr>
                 </table>
                    
            </div>
        </div>
        <br />
        <button type="submit" class='cta1'>Send</button>
    </form>

</center>

<?php displayFooter(); ?>

