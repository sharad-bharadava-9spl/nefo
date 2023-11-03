<?php
require_once 'cOnfig/connection.php';
$product_id = $_GET['product_id']; 
$category_id = $_GET['category_id']; 
if($category_id == 1){
    $getProduct = "SELECT a.name from flower a,purchases b WHERE b.purchaseid = $product_id AND a.flowerid = b.productid";
}else if($category_id == 2){
    $getProduct = "SELECT a.name from extract a,purchases b WHERE a.extractid =  b.productid AND b.purchaseid = $product_id";
}else{
    $getProduct = "SELECT a.name from products a,purchases b  WHERE a.productid = b.productid AND b.purchaseid = $product_id";
}
try
{
    $result = $pdo3->prepare("$getProduct");
    $result->execute();
}
catch (PDOException $e)
{
    $error = 'Error fetching product: ' . $e->getMessage();
    echo $error;
    exit();
}
$row = $result->fetch();
echo $productName = $row['name'];
die;