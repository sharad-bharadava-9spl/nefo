<?php

require_once 'cOnfig/connection.php';
session_start();

if(isset($_POST['view'])){
    $notification_limit = '';
  if($_SESSION['barStockAlert'] == 1 && $_SESSION['dispensegStockAlert'] == 1 && $_SESSION['dispenseuStockAlert'] == 1){
       $notification_limit = '';
  }else if($_SESSION['barStockAlert'] == 1 && $_SESSION['dispensegStockAlert'] == 1){
      $notification_limit = 'AND note_type IN (1,2)';
  }else if($_SESSION['barStockAlert'] == 1 && $_SESSION['dispenseuStockAlert'] == 1){
      $notification_limit = 'AND note_type IN (0,2)';
  }else if($_SESSION['dispensegStockAlert'] == 1 && $_SESSION['dispenseuStockAlert'] == 1){
      $notification_limit = 'AND note_type IN (0,1)';
  }else if($_SESSION['barStockAlert'] == 1){
     $notification_limit = 'AND note_type = 2';
  }else if($_SESSION['dispensegStockAlert'] == 1){
     $notification_limit = 'AND note_type = 1';
  }else if($_SESSION['dispenseuStockAlert'] == 1){
     $notification_limit = 'AND note_type = 0';
  }else{
     $notification_limit = 'AND note_type NOT IN (0,1,2)';
  }

if($_POST["view"] != '')
{
   $update_query = "UPDATE stock_notifications SET status = 1 WHERE status=0 $notification_limit";
     try
    {
        $result = $pdo3->prepare("$update_query");
        $result->execute();
    }
    catch (PDOException $e)
    {
        $error = 'Error fetching stock: ' . $e->getMessage();
        echo $error;
        exit();
    }
}

    $query = "SELECT * FROM stock_notifications WHERE 1 $notification_limit ORDER BY id DESC"; 
     try
    {
        $result = $pdo3->prepare("$query");
        $result->execute();
    }
    catch (PDOException $e)
    {
        $error = 'Error fetching stock2: ' . $e->getMessage();
        echo $error;
        exit();
    }
$output = '';

if($result->rowCount() > 0)
{
  $i= 0;
  while($row = $result->fetch())
  {
      $purchase_id = $row['purchase_id'];
      $category_id = $row['category_id'];
      $stock = $row['stock'];
      $internal_stash = $row['internal_stash'];
      $external_stash = $row['external_stash'];
      $note_type = $row['note_type'];

      // get product name
      if($note_type == 0 || $note_type == 1){
            $productType = 'Dispense';
                if($category_id == 1){
                    $getProduct = "SELECT a.name from flower a,purchases b WHERE b.purchaseid = $purchase_id AND a.flowerid = b.productid";
                }else if($category_id == 2){
                    $getProduct = "SELECT a.name from extract a,purchases b WHERE a.extractid =  b.productid AND b.purchaseid = $purchase_id";
                }else{
                    $getProduct = "SELECT a.name from products a,purchases b  WHERE a.productid = b.productid AND b.purchaseid = $purchase_id";
                }
                
                  try
                  {
                      $product_result = $pdo3->prepare("$getProduct");
                      $product_result->execute();
                  }
                  catch (PDOException $e)
                  {
                      $error = 'Error fetching product name: ' . $e->getMessage();
                      echo $error;
                      exit();
                  }
                  $product_row = $product_result->fetch();
                     $productName = $product_row['name'];  
                //  notification messages
                if($note_type == 0){
                    $unit_type = 'units';
                }else{
                   $unit_type = 'grams';
                }     
                if($internal_stash > 0 && $external_stash >0){
                    $msg_class = 'success';
                    $msg = 'You have '.$internal_stash.' '.$unit_type.' in internal stash and '.$external_stash.' '.$unit_type.' in external stash. Please add product to the dispensary!';
                }else if($internal_stash > 0){
                    $msg_class = 'success';
                    $msg = 'There is still '.$internal_stash.' '.$unit_type.' left in internal stash - please add product to the dispensary!';
                }else if($external_stash > 0){
                    $msg_class = 'success';
                    $msg = 'There is still '.$external_stash.' '.$unit_type.' left in external stash - please add product to the dispensary!';
                }else{
                     $msg_class = 'error';
                     $msg = 'Please contact the provider!';
                }     
                  $purchase_link = "purchase.php?purchaseid=".$purchase_id;   
      }else if($note_type == 2){
            $productType = 'Bar';
              $getBarProduct = "SELECT a.name from b_products a,b_purchases b  WHERE a.productid = b.productid AND b.purchaseid = $purchase_id";
                
                  try
                  {
                      $bar_product_result = $pdo3->prepare("$getBarProduct");
                      $bar_product_result->execute();
                  }
                  catch (PDOException $e)
                  {
                      $error = 'Error fetching bar product name: ' . $e->getMessage();
                      echo $error;
                      exit();
                  }
                  $bar_product_row = $bar_product_result->fetch();
                     $productName = $bar_product_row['name'];

                //  notification messages
              
                $unit_type = 'units';
                    
                if($internal_stash > 0 && $external_stash >0){
                    $msg_class = 'success';
                    $msg = 'You have '.$internal_stash.' '.$unit_type.' in internal stash and '.$external_stash.' '.$unit_type.' in external stash. Please add product to the bar!';
                }else if($internal_stash > 0){
                    $msg_class = 'success';
                    $msg = 'There is still '.$internal_stash.' '.$unit_type.' left in internal stash - please add product to the bar!';
                }else if($external_stash > 0){
                    $msg_class = 'success';
                    $msg = 'There is still '.$external_stash.' '.$unit_type.' left in external stash - please add product to the bar!';
                }else{
                     $msg_class = 'error';
                     $msg = 'Please contact the provider!';
                } 
                 $purchase_link = "bar-purchase.php?purchaseid=".$purchase_id;  
      }

      $output .= '<tr>
                    <td class="left"><div class="'.$msg_class.'"><a href="'.$purchase_link.'"><strong>'.$productName.' ('.$productType.')</strong>  -  '.$msg.'</a></div></td>
                   </tr>';
  }
 
  $i++;
   
}
else{
    $output .= '<tr><td><strong>No notification Found !</strong></td></tr>';
}

$status_query = "SELECT * FROM stock_notifications WHERE status=0 $notification_limit";
    try
    {
        $result_query = $pdo3->prepare("$status_query");
        $result_query->execute();
    }
    catch (PDOException $e)
    {
        $error = 'Error fetching stock3: ' . $e->getMessage();
        echo $error;
        exit();
    }
$count = $result_query->rowCount();   
$data = array(
   'notification' => $output,
   'unseen_notification'  => $count
);

echo json_encode($data);
die;
}
?>