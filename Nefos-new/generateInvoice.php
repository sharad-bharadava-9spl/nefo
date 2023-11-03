<?php
require_once 'cOnfig/connection.php';
session_start();

	   $invoice_type =$_SESSION['invoice_type']; 
	   $status = $_SESSION['status'];
	   $customer_number= $_SESSION['customer_number'];
	   $invoice_date = $_SESSION['invoice_date'];
	   $invoice_due_date = $_SESSION['invoice_due_date'];
	   $base_amount = $_SESSION['base_amount'];
	   $total_amount = $_SESSION['total_amount'];
	   $shipping = $_SESSION['shipping'];
	   $unit_price = $_SESSION['unit_price'];
	   $number_items = $_SESSION['number_items'];
	   $discount = $_SESSION['discount'];
	   $vat = $_SESSION['vat'];
	   $fees_elements = $_SESSION['fees_elements'];
	   $description = $_SESSION['description'];
	   $invNo = $_SESSION['invNo'];
	   $customer_name = 'test';


?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
	
<style type="text/css">

* { margin: 0; padding: 0; }
body { font: 14px/1.4 Arial, serif; }
#page-wrap { width: 800px; margin: 0 auto; }

textarea { border: 0; font: 14px Arial, Serif; overflow: hidden; resize: none; }
table { border-collapse: collapse; }
table td, table th { border: 1px solid black; padding: 5px; }
table#meta td{ border: none !important; }
#header { height: 15px; width: 100%; margin: 20px 0; background: #222; text-align: center; color: white; font: bold 15px Helvetica, Sans-Serif; text-decoration: uppercase; letter-spacing: 20px; padding: 8px 0px; }

#address { width: 248px; height: auto; float: left; }
/*#customer { overflow: hidden; }*/

#logo { text-align: right; float: left; position: relative; margin-top: 0px; border: 1px solid #fff; max-width: 540px; max-height: 110px; overflow: hidden; }

#logoctr { display: none; }
#logo:hover #logoctr, #logo.edit #logoctr { display: block; text-align: right; line-height: 25px; background: #eee; padding: 0 5px; }
#logohelp { text-align: left; display: none; font-style: italic; padding: 10px 5px;}
#logohelp input { margin-bottom: 5px; }
.edit #logohelp { display: block; }
.edit #save-logo, .edit #cancel-logo { display: inline; }
.edit #image, #save-logo, #cancel-logo, .edit #change-logo, .edit #delete-logo { display: none; }
#customer-title {  text-align: center; font: bold 21px 'Arial';}
#customer{ float: right; }
#meta { margin-top: 18px; width: 300px;  border: 1px solid black; }
#meta td { text-align: right;  }
#meta td.meta-head { text-align: left; }
#meta td textarea { width: 100%; height: 20px; text-align: right; }

#items { clear: both; width: 100%; margin: 30px 0 0 0; border: 1px solid black; }

#items textarea { width: 80px; height: 50px; }
#items tr.item-row td { vertical-align: top; }
#items td.description { width: 300px; }
#items td.item-name { width: 175px; }
#items td.description textarea, #items td.item-name textarea { width: 100%; }
#items td.total-line { border-right: 0; text-align: right; }
#items td.total-value { border-left: 0; padding: 10px; }
#items td.total-value textarea { height: 20px; background: none; }
#items td.balance { background: #eee; }
#items td.blank { border: 0; }
#subtotal{ float: right;  }
#terms { text-align: center; margin: 20px 0 0 0; }
#terms h5 { text-transform: uppercase; font: 13px Helvetica, Sans-Serif; letter-spacing: 10px; border-bottom: 1px solid black; padding: 0 0 8px 0; margin: 0 0 8px 0; }
#terms textarea { width: 100%; text-align: center;}

textarea:hover, textarea:focus, #items td.total-value textarea:hover, #items td.total-value textarea:focus, .delete:hover { background-color:#EEFF88; }

.delete-wpr { position: relative; }
.delete { display: block; color: #000; text-decoration: none; position: absolute; background: #EEEEEE; font-weight: bold; padding: 0px 3px; border: 1px solid; top: -6px; left: -22px; font-family: Verdana; font-size: 12px; }
</style>
</head>

<body>

	<div id="page-wrap">

		
		
		<div id="identity">

            <div id="logo">
              <img id="image" src="http://192.168.0.41/ccs/Nefos-new/invoice-logo.png" alt="logo" />
            </div>

            


		
		</div>
		
		<div style="clear:both"></div>
		<div id="address"><span>www.cannabisclub.systems - info@cannabisclub.systems</span><br>
            		<strong>Mykinlink SL</strong><br>
            		B87843504<br><br>
            		Calle Clara Del Rey 36, Planta 2, Puerta B
            		28002 Madrid
            		España<br><br>
            		<Strong>El Flamenco Cannabis Club</Strong><br>
					Calle Ricardo Gil 39 Bajo 30002 Murcia, Murcia

            </div>
            <br>

		<div id="customer">

            <div id="customer-title"><?php echo $customer_name; ?></div>

            <table id="meta">
                <tr>
                    <td class="meta-head">Numero cliente</td>
                    <td><?php echo $customer_number; ?></td>
                </tr>
                <tr>

                    <td class="meta-head">Numero factura</td>
                    <td><?php echo $invNo; ?></td>
                </tr>
                <tr>
                    <td class="meta-head">Fecha facturación</td>
                    <td><div class="due"><?php echo $invoice_date; ?></div></td>
                </tr>                
                <tr>
                    <td class="meta-head">Se vence al recibo</td>
                    <td><div class="due">Se vence al recibo</div></td>
                </tr>

            </table>
		
		</div>
		<br>
		<div id="details">
			<table id="items">
			
			  <tr>
			      <th>Concepto</th>
			      <th>Cantidad</th>
			      <th>Precio</th>
			      <th>Descuento</th>
			      <th>Total</th>
			  </tr>
			  
			  <tr class="item-row">
			      <td>Software: September 2020</td>
			      <td></td>
			      <td></td>
			      <td></td>
			      <td><span class="price"><?php echo $base_amount; ?> €</span></td>
			  </tr>
			  

			  
			
			</table>
		</div>
			<br>
		<div id="subtotal">	
			<table>
				<tr>
				      
				      <td colspan="4" class="total-line">Subtotal</td>
				      <td class="total-value"><div id="subtotal"><?php echo $base_amount; ?> €</div></td>
				  </tr>
				  <tr>

				      
				      <td colspan="4" class="total-line">IVA (21%)</td>
				      <td class="total-value"><div id="total"><?php echo $vat; ?> €</div></td>
				  </tr>
			</table>
			<br>
			<table>
				<tr>
					<td colspan="4">
						<strong>A pagar</strong>
					</td>				
					<td>
						<strong><?php echo $total_amount; ?> €</strong>
					</td>
				</tr>
			</table>
		</div>	

		<!-- <div id="terms">
		  <h5>Terms</h5>
		  <textarea>NET 30 Days. Finance Charge of 1.5% will be made on unpaid balances after 30 days.</textarea>
		</div> -->
	
	</div>
	
</body>

</html>