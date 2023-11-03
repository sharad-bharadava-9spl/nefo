<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	session_start();
	$accessLevel = '3';
	
	 if(isset($_SESSION['lang'])){
	 	$current_lang = $_SESSION['lang'];
	 }else{
	 	$current_lang = 'en';
	 }

	
	
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
		    
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "Help-videos",
			    filename: "Help-videos" //do not include extension
		
			  });
		
			});
		    
		    
		    
			$('#cloneTable').width($('#mainTable').width());
			
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					}
				}
			}); 

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});

	function delete_element(delete_id){
      	 if(confirm('Are you sure to delete this page ?')){
      	 	 window.location = "page-manager.php?did="+delete_id;
      	 }
      }
		
EOD;
// delete videos

	if(isset($_GET['did'])){
		// delete department
		$id= $_GET['did'];

		$deletePage = "DELETE FROM admin_page_details where id = $id";
			try
			{
				$results = $pdo3->prepare("$deletePage");
				$results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			$_SESSION['successMessage'] = "Page deleted successfully!";
			header("location: page-manager.php");
			exit();
	}

	pageStart("Page Management", NULL, $memberScript, "pmembership", NULL, "Page Management", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center>
	<a href='new-page.php' class='cta'>Add New Page</a>
</center>

	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table>
<br />
<?php 
     // select videos
	$selectPages = "SELECT * from admin_page_details order by id DESC";
		try
		{
			$result = $pdo3->prepare("$selectPages");
			$result->execute();
			
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
 ?>

	 <table class='default' id='mainTable'>
		  <thead>	
		   <tr style='cursor: pointer;'>
		    <th>Page Title</th>
		    <th>Page Link</th>
		    <th>Category</th>
		    <th>Show Menu in admin ?</th>
		    <th>Actions</th>
		   </tr>
		  </thead>
		  <tbody>
		<?php
		$i= 0;

		   while($page = $result->fetch()){
		   		$pageid = $page['id'];
		   		$page_title = $page['page_title'];
		   		$page_link = $page['page_link'];
		   		$category = $page['category'];
		   		$admin_menu = $page['admin_menu'];

		   		if($admin_menu == 1){
		   			$show_menu = 'Yes';
		   		}else{
		   			$show_menu = "No";
		   		}

		   		$page_row =	sprintf("
			  	  <tr>
			  	   <td class='clickableRow' href='edit-page.php?pageid=%d'>%s</td>
			  	   <td class='clickableRow' href='edit-page.php?pageid=%d'>%s</td>
			  	   <td class='clickableRow' href='edit-page.php?pageid=%d'>%s</td>
			  	   <td class='clickableRow' href='edit-page.php?pageid=%d'>%s</td>
			  	   <td style='text-align: center;'><a href='edit-page.php?pageid=%d'><img src='images/edit.png' height='15' title='Editar' /></a>&nbsp;&nbsp;<a href='javascript:void(0)' onClick='delete_element(%d)' ><img src='images/delete.png' height='15' title='Delete Video' /></a></td>
				  </tr>",
				  $pageid, $page_title, $pageid, $page_link, $pageid, $category, $pageid, $show_menu, $pageid, $pageid
				  );
				  $i++;
				  echo $page_row;
		   }
        ?>
		  </tbody>
	 </table>
<?php  displayFooter();