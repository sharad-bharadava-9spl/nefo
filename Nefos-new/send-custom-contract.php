<?php

require_once 'cOnfig/connection.php';
require_once 'cOnfig/viewv6.php';
require_once 'cOnfig/authenticate.php';
require_once 'cOnfig/languages/common.php';

session_start();

$accessLevel = '3';

// Authenticate & authorize
authorizeUser($accessLevel);
  $validationScript = <<<EOD
    $(document).ready(function() {
      var validator = $("#registerForm").submit(function() {
            // update underlying textarea before submit validation
            tinyMCE.triggerSave();
        }).validate({
            ignore: "",
           rules: {
             email:{
                email: true
             },
             description: {
                required: true
             }
           },
          messages:{
              
          },
            errorPlacement: function(error, element) {
                if ( element.is(":radio") || element.is(":checkbox") || element.is("textarea")){
                     error.appendTo(element.next());
                } else {
                    return true;
                }
            },

        });
        validator.focusInvalid = function() {
            // put focus on tinymce on submit validation
            if (this.settings.focusInvalid) {
                try {
                    var toFocus = $(this.findLastActive() || this.errorList.length && this.errorList[0].element || []);
                    if (toFocus.is("textarea")) {
                        tinyMCE.get(toFocus.attr("id")).focus();
                    } else {
                        toFocus.filter(":visible").focus();
                    }
                } catch (e) {
                    // ignore IE throwing errors when focusing hidden elements
                }
            }
        }
      tinymce.init({
        selector: '#contract',
        height :'400',
        plugins: "code",
        // update validation status on change
        onchange_callback: function(editor) {
            tinyMCE.triggerSave();
            $("#" + editor.id).valid();
        }
    });

  }); // end ready
EOD;

pageStart("Send Custom Contract", NULL, $validationScript, "psales", "dispensepre", "Send Custom Contract", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
?>

<center>

<form id="registerForm" action="send-custom-contract-process.php" method="POST">
    
    <div id="mainbox-no-width">
     <div id="mainboxheader">
      New Contract
     </div>
     <div class='boxcontent'>
             <table class="padded">
              <tr>
               <td>Email:</td>
               <td>
                <input type="text" name="email" class='defaultinput' required />
               </td>
              </tr>              
              <tr>
               <td>Subject:</td>
               <td>
                <input type="text" name="subject" class='defaultinput' required />
               </td>
              </tr>
              <tr>
                  <td style="vertical-align: middle;">Email Content:</td>
                  <td>
                      <textarea name="description" id="contract" class="defaultinput" style="height: 120px;" required></textarea>
                  </td>
              </tr>

             </table>
                
     
            </div>
        </div>
            <br />
                <button type="submit" name="add_contract" class='cta1'>Submit</button>
</form>

</center>
<br />
<script type="text/javascript" src="scripts/tinymce.min.js"></script>
<?php displayFooter(); ?>

