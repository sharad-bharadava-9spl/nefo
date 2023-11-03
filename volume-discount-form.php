  <!-- // KONSTANT CODE UPDATE BEGIN -->
 <div class='infobox fullwidth'>
  <h3 class="smallerFont"><?php echo $lang['volume-discounts']; ?></h3>
  <table>
<?php 
    $volumeDiscounts = "SELECT * FROM volume_discounts WHERE purchaseid = $purchaseid";
    $result = $pdo3->prepare("$volumeDiscounts");
    $result->execute();
    while ($rs = $result->fetch()) { ?>
    <tr>
        <td><?php echo $lang['units']; ?></td>
        <td><input type='number' lang='nb' class='fourDigit' name='volume_unit[]' value="<?php echo $rs['units'];?>" /></td>
        <td><?php echo $lang['add-total']; ?></td>
        <td><input type="number" lang="nb" class="fourDigit" name="volume_unit_price[]" value="<?php echo $rs['amount'];?>" /> <?php echo $_SESSION['currencyoperator'] ?></td>
    </tr>
        
<?php } ?>      
   <tr id="volumeDiv">
    <td><?php echo $lang['units']; ?></td>
    <td><input type='number' lang='nb' class='fourDigit' name='volume_unit[]' /></td>
    <td><?php echo $lang['add-total']; ?></td>
    <td><input type="number" lang="nb" class="fourDigit" name="volume_unit_price[]" /> <?php echo $_SESSION['currencyoperator'] ?></td>
   <br>
   </tr>
    <tr>
       <td colspan="4">
           <button type="button" onclick="addMoreDiscount()"><?php echo $lang['add-more']; ?></button> 
       </td>
    </tr>
  </table>
   </div>
  <br />
  <!-- // KONSTANT CODE UPDATE END -->
