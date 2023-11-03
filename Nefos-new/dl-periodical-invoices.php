<?php
require_once 'cOnfig/connection.php';
require_once 'cOnfig/viewv6.php';
require_once 'cOnfig/authenticate.php';
require_once 'cOnfig/languages/common.php';

$accessLevel = '3';
	
// Authenticate & authorize
authorizeUser($accessLevel);
    $memberScript = <<<EOD

        $(document).ready(function() {
            $( "#datepicker" ).datepicker({
                     dateFormat: "dd-mm-yy"
                });
            $( "#datepicker2" ).datepicker({
               dateFormat: "dd-mm-yy"
            }); 
        });
EOD;
 function rrmdir($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir);
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
           rrmdir($dir. DIRECTORY_SEPARATOR .$object);
         else
           unlink($dir. DIRECTORY_SEPARATOR .$object); 
       } 
     }
     rmdir($dir); 
   } 
 }

function is_dir_empty($dir) {
  if (!is_readable($dir)) return null; 
  return (count(scandir($dir)) == 2);
}

 $tmp_inv_folder = 'tmp_invoices';
 rrmdir($tmp_inv_folder);
// Check if 'entre fechas' was utilised
if (!empty($_POST['untilDate'])) {
    
    $limitVar = "";
    
    $fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
    $untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
    
    $timeLimit = "AND DATE(invdate) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
    $limitVar = "";
        
}else{
    $timeLimit = '';
}
//  code to filter the brand from invoices
if(isset($_POST['brand']) && !empty($_POST['brand'])){
    $selectedBrandArr = $_POST['brand'];
    $selectedBrand =  "'" . implode( "', '", $selectedBrandArr ) . "'";
    $brand_limit = "AND brand IN ($selectedBrand)";
}else{
    $brand_limit = 'AND brand IN (-1)';
}

unset($_SESSION['curr_time']);
if(isset($_POST['download'])){

    // Query to look up invoices
    $selectInvoices = "SELECT invno, customer, brand FROM invoices WHERE 1 $timeLimit $brand_limit ORDER by invdate DESC"; 

        try
        {
            $result = $pdo->prepare("$selectInvoices");
            $result->execute();
        }
        catch (PDOException $e)
        {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
        }
        $invoice_folder ='../../../ccsnubev2_com/v6/invoices/';
        //$invoice_folder ='invoices/';
        $time = time();
       
        
        $_SESSION['curr_time']  = $time;
        $curr_time = $_SESSION['curr_time'];
        
        $tmp_inv_folder = "tmp_invoices";
        if(!is_dir($tmp_inv_folder)){
            mkdir($tmp_inv_folder, 0777, true);
        }
        $invCount = $result->rowCount();
        if($invCount > 0){
            while($inv_row = $result->fetch()){
                $inv_pdf = $inv_row['customer']."-".$inv_row['invno']."-".$inv_row['brand'].".pdf";
                $inv_pdf = preg_replace('/\s+/', '', $inv_pdf);
                $inv_file = $invoice_folder.$inv_pdf;
                if(file_exists($inv_file)){
                    $destination = $tmp_inv_folder."/".$inv_pdf; 
                    copy($inv_file, $destination);
                }
            }

        if(is_dir_empty($tmp_inv_folder)){
            $_SESSION['errorMessage'] = "No invoice file found to download !";
            header("Location:dl-periodical-invoices.php");
            die();
        }

        $rootPath = realpath($tmp_inv_folder);
        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($tmp_inv_folder."/facturas-".$curr_time.".zip", ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );



        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
        	    if (!strstr($file,'.html') && !strstr($file,'.zip') && !strstr($file,'.php')) {
        	        // Get real and relative path for current file
        	        $filePath = $file->getRealPath();
        	        $relativePath = substr($filePath, strlen($rootPath) + 1);
        	
        	        // Add current file to archive
        	        $zip->addFile($filePath, $relativePath);
                }
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();
            header('Content-Type: application/zip');
            header("Content-Disposition: attachment; filename='facturas-".$curr_time.".zip'");
            header('Content-Length: ' . filesize($zipname));
            header("Location: ".$tmp_inv_folder."/facturas-".$curr_time.".zip");
            ignore_user_abort(true);
            if (connection_aborted()) {
                rrmdir($tmp_inv_folder);
            }
/*        $_SESSION['successMessage'] = "Invoices downloaded !";
        header("Location:dl-periodical-invoices.php");
        die(); */         
    }else{
        $_SESSION['errorMessage'] = "No invoice found to download !";
        header("Location:dl-periodical-invoices.php");
        die();
    } 

}


    pageStart("Download Invoices", NULL, $memberScript, "pmembership", NULL, "Download Invoices", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
<center>
    <a href='invoice-section.php' class='cta1'>Invoice Section</a>
</center>
<center>
    <div id="filterbox">
     <div id="mainboxheader"> Filter </div>
         <div class="boxcontent">
                <form action="" method="POST" style="display: inline-block;">
                    <input type="text" id="datepicker" name="fromDate" autocomplete="off" class="sixDigit defaultinput" placeholder="From date" required="">
                     <input type="text" id="datepicker2" name="untilDate" autocomplete="off" class="sixDigit defaultinput" placeholder="To date" required=""><br>
                 
                    <div style="display: inline-block; text-align: left; float: left; padding-right: 32px;">
                        &nbsp;<strong>Brand:</strong><br> <br> 
                        <div class="fakeboxholder firstbox">    
                         <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                          Amazon
                          <input type="checkbox" name="brand[]" id="accept1" value="Amazon" checked="">
                          <div class="fakebox"></div>
                         </label>
                        </div>
                        <br>
                        <br>                        
                        <div class="fakeboxholder firstbox">    
                         <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                          SW
                          <input type="checkbox" name="brand[]" id="accept1" value="SW" checked="">
                          <div class="fakebox"></div>
                         </label>
                        </div>
                        <br>
                        <br>
                        <div class="fakeboxholder"> 
                         <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                          HW
                          <input type="checkbox" name="brand[]" id="accept2" value="HW" checked="">
                          <div class="fakebox"></div>
                         </label>
                        </div>
                        <br>
                        <br>
                        <div class="fakeboxholder"> 
                         <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                          SW - Commission
                          <input type="checkbox" name="brand[]" id="accept3" value="SW - Commission" checked="">
                          <div class="fakebox"></div>
                         </label>
                        </div>
                        <br>
                        <br>
                        <input type="hidden" name="download" value="1">
                        <center>
                         <button type="submit" class="cta2">Download</button>
                        </center>
                        </div>
                </form>
        </div>
    </div>
</center>
<?php    
displayFooter();