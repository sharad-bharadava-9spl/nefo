<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$tempNo = $_SESSION['tempNo'];
	$_SESSION['tempNo2'] = $_SESSION['tempNo'];
	
	$deleteSig = "DELETE FROM newscan WHERE type = '99'";
		try
		{
			$result = $pdo3->prepare("$deleteSig")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
	// Write to newsig table with temp number
	$insertSig = "INSERT INTO newscan (chip, type) VALUES ('{$_SESSION['tempNo']}', 99)";
		try
		{
			$result = $pdo3->prepare("$insertSig")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

	pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes dev-align-center", $lang['member-newmembercaps'] . " - Firma", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>
<script>
	// disable back script
	(function (global) {

	  if(typeof (global) === "undefined")
	  {
	    throw new Error("window is undefined");
	  }

	    var _hash = "!";
	    var noBackPlease = function () {
	        global.location.href += "#";

	    // making sure we have the fruit available for juice....
	    // 50 milliseconds for just once do not cost much (^__^)
	        global.setTimeout(function () {
	            global.location.href += "!";
	        }, 50);
	    };
	  
	  // Earlier we had setInerval here....
	    global.onhashchange = function () {
	        if (global.location.hash !== _hash) {
	            global.location.hash = _hash;
	        }
	    };

	    global.onload = function () {
	        
	    noBackPlease();

	    // disables backspace on page except on input fields and textarea..
	    document.body.onkeydown = function (e) {
	            var elm = e.target.nodeName.toLowerCase();
	            if (e.which === 8 && (elm !== 'input' && elm  !== 'textarea')) {
	                e.preventDefault();
	            }
	            // stopping event bubbling up the DOM tree..
	            e.stopPropagation();
	        };
	    
	    };

	})(window);		
</script>

<script>
setInterval(function()
{ 
	$.ajax({
	    url:'{$google_root}images/_<?php echo $_SESSION['domain']; ?>/sigs/<?php echo $tempNo; ?>.png',
	    type:'HEAD',
	    error: function()
	    {
	    },
	    success: function()
	    {
	        window.location.replace("new-member-1.php");			
	    }
	});
}, 3000);
</script>


<br />
<div class='actionbox-np2'>
<div class="mainboxheader"><?php echo $lang['awaiting-signature']; ?></div><br />
<div class='boxcontent'>
<img src="images/signature.gif" /><br /><br />

<a class='skipbutton' href='new-member-1.php?noSig'><?php echo $lang['skip'] ?></a>

</div>
</div>






<?php displayFooter(); ?>
