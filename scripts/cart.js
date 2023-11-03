if(dispenseFlag != 1){
  var blank_cart = [];
   sessionStorage.setItem('shoppingCart', JSON.stringify(blank_cart));
}
console.log(realWeight);
 var shoppingCart = (function() {

  cart = [];

    // Constructor
  function Item(product_id, current_id, cat_id, cat_name, name, grams, gifts, realGrams, price, count, ppg, purchaseid, discType, discPercentage, discAmount, discQuant) {
    this.productid = product_id;
    this.current_id = current_id;
    this.category = cat_id;
    this.cat_name = cat_name;
    this.name = name;
    this.grams = grams;
    this.grams2 = gifts;
    this.realGrams = realGrams;
    this.euro = price;
    this.count = count;
    this.ppg = ppg;
    this.purchaseid = purchaseid;
    this.discType = discType;
    this.discPercentage = discPercentage;
    this.discAmount = discAmount;
    this.discQuant = discQuant;
  }

  // Save cart
  function saveCart() {
    sessionStorage.setItem('shoppingCart', JSON.stringify(cart));
  }
  
    // Load cart
  function loadCart() {
    cart = JSON.parse(sessionStorage.getItem('shoppingCart'));
  }
  if (sessionStorage.getItem("shoppingCart") != null) {
    loadCart();
  }
  var obj = {};
  // Add Product to cart
  obj.addItemToCart = function(product_id, current_id, cat_id, cat_name, name, grams, gifts, realGrams, price, count, ppg, purchaseid, discType, discPercentage, discAmount, discQuant){
    for(var item in cart) {
      if(cart[item].purchaseid === purchaseid) {
        cart[item].productid = product_id;
        cart[item].current_id = current_id;
        cart[item].category = cat_id;
        cart[item].cat_name = cat_name;
        cart[item].count = count;
        cart[item].name = name;
        cart[item].grams = grams; 
        cart[item].grams2 = gifts; 
        cart[item].realGrams = realGrams;
        cart[item].euro = price;
        cart[item].ppg = ppg;
        cart[item].discType = discType;
        cart[item].discPercentage = discPercentage;
        cart[item].discAmount = discAmount;
        cart[item].discQuant = discQuant;
        saveCart();
        return;
      }
    }
    var item = new Item(product_id, current_id, cat_id, cat_name, name, grams, gifts, realGrams, price, count, ppg, purchaseid, discType, discPercentage, discAmount, discQuant);
    cart.push(item);
    saveCart();
 }

   // Set count from item
  obj.setCountForItem = function(product_id, count) {
    for(var i in cart) {
      if (cart[i].purchaseid === product_id) {
        cart[i].count = count;
        break;
      }
    }
  };
  // Remove item from cart
  obj.removeItemFromCart = function(product_id) {
      for(var item in cart) {
        if(cart[item].purchaseid == product_id) {
          cart[item].count --;
          console.log(cart[item].count);
          if(cart[item].count === 0) {
            cart.splice(item, 1);
          }
          break;
        }
    }
    saveCart();
  }
// Remove all items from cart
  obj.removeItemFromCartAll = function(product_id) {
    for(var item in cart) {
      if(cart[item].purchaseid === product_id) {
        cart.splice(item, 1);
        break;
      }
    }
    saveCart();
  }
    // Count cart 
  obj.totalCount = function() {
    var totalCount = 0;
    for(var item in cart) {
      totalCount += cart[item].count;
    }
    return totalCount;
  }

   // Total cart
  obj.totalCart = function() {
    var totalCart = 0;
    for(var item in cart) {
      totalCart += cart[item].euro * cart[item].count;
    }
    return Number(totalCart.toFixed(2));
  }


  // List cart
  obj.listCart = function() {
    var cartCopy = [];
    for(i in cart) {
      item = cart[i];
      itemCopy = {};
      for(p in item) {
        itemCopy[p] = item[p];

      }
      itemCopy.total = Number(item.euro * item.count).toFixed(2);
      cartCopy.push(itemCopy)
    }
    return cartCopy;
  }
    return obj;
})();

function displayCart(){
     var cartArray = shoppingCart.listCart();
     console.log(cartArray);
      var output = "";
      for(var i in cartArray) {
        var grams =   cartArray[i].grams;
        var realGrams =   cartArray[i].realGrams;
        var gifts =   cartArray[i].grams2;
        var category_id =   cartArray[i].category;
        var realGrams_txt = "";
        if(realGrams == null || realGrams == ''){
           realGrams = '0';
        }        
        if(grams == null || grams == ''){
           grams = '0';
        }        
        if(gifts == null || gifts == ''){
           gifts = '0';
        }
        if(domain == 'demo'){
          if(realGrams != '' && realGrams != 0){
              realGrams_txt = " (" + realGrams + "g )";
          }
          var sort_type = 0;
          if(category_id != '1' || category_id != '2'){
             sort_type = $("#menu"+category_id).prev("h3").children("#sort_type"+category_id).val();
          }
          var quant_td = "";
          if(grams != "" && grams != 0){
            quant_td = "<td class='mini_bottom'><strong>" + grams + " " + realGrams_txt + " </strong></td>";
          }
          output += "<tr>"
                        + "<td colspan ='6'><span class='cart_text'><strong>"+ cartArray[i].name +" </strong></span></td></tr><tr>"
                        + quant_td
                        + "<td class='mini_bottom'><strong>" + cartArray[i].euro + " "+currencyoperator+"</strong></td>"
                        + "<td class='mini_bottom' style='width:80px;'><img src='images/pencil.png' data-productId="+cartArray[i].purchaseid+" data-catid = "+cartArray[i].category+" data-sorttype = "+sort_type+"  width='15' height='15' class='edit-item mini_edit' id=edit_"+cartArray[i].current_id+">&nbsp;<img src='images/delete.png' data-productId="+cartArray[i].purchaseid+"  width='15' class='delete-item mini_delete' id=del_"+cartArray[i].current_id+"></td>"
                        +"<td class='hrLine'></td>"
                  +"</tr>";
        }else{
          if(realWeight == 1){
            output += "<tr>"
              + "<td class='dispensetd'>" + cartArray[i].name + "</td>" 
              + "<td class='dispensetd'>" + cartArray[i].cat_name + "</td>" 
              + "<td class='dispensetd'>" + grams + "</td>"
              + "<td class='dispensetd'>" + realGrams + "</td>"
              + "<td class='dispensetd'>" + gifts + "</td>"
              + "<td class='dispensetd'>" + cartArray[i].euro + " "+currencyoperator+"</td>"
              + "<td><img src='images/delete.png' data-productId="+cartArray[i].purchaseid+"  width='17' class='delete-item' id=del_"+cartArray[i].current_id+"></td>"
              +  "</tr>";
            }else{
              output += "<tr>"
              + "<td class='dispensetd'>" + cartArray[i].name + "</td>" 
              + "<td class='dispensetd'>" + cartArray[i].cat_name + "</td>" 
              + "<td class='dispensetd'>" + grams + " </td>"
              + "<td class='dispensetd'>" + gifts + "</td>"
              + "<td class='dispensetd'>" + cartArray[i].euro + " "+currencyoperator+"</td>"
              + "<td><img src='images/delete.png' data-productId="+cartArray[i].purchaseid+"  width='17' class='delete-item' id=del_"+cartArray[i].current_id+"></td>"
              +  "</tr>";
            }
        }
      }
     $('.show_cart_data').html(output);
     $('#cart_count').html(shoppingCart.totalCount());
     $("#save_cart_data").val(JSON.stringify(cartArray));
     console.log(shoppingCart.totalCart());
  }

  // add or remove itmes to cart on input change
  $(document).on("change keyup blur keypress",".calc,.calc2,.calc3,.calc5,.calc4,.calc6", function(event){
     var idSplit,gram_val,euro_val,realGram_val,unit_val,sort_type,idVal;
     var this_val = $(this).val();
     var this_id = $(this).attr('id');
        idVal = this_id.split(/([0-9]+)/);
     var idSplit = idVal[0];   
 /*   if($(event.target).hasClass('calc') || $(event.target).hasClass('calc3')){
        console.log(idSplit);
         idSplit = 'grcalc';
    }else if($(event.target).hasClass('calc2')){
         idSplit = 'eurcalc';
    }else if($(event.target).hasClass('calc5')){
         idSplit = 'realgrcalc';
    }*/
      var id = idVal[1];
      setTimeout(function(){
        var product_id = $("#sales_"+id+"_productid").val();
        var category_id = $("#sales_"+id+"_category").val();
        if(category_id != '1' || category_id != '2'){
           sort_type = $("#menu"+category_id).prev("h3").children("#sort_type"+category_id).val();
        }
        gram_val = $("#grcalc"+id).val();
         var disc_price = $("#discount_"+id+"_amount").val();
         if(disc_price != ''){
             euro_val = disc_price;
         }else{
          euro_val = $("#eurcalc"+id).val();
        }
        realGram_val = $("#realgrcalc"+id).val();
        var gift_val = $("#grcalcB"+id).val();

        // get product name
            var product_name = $("input[name='sales["+id+"][name]']").val();
            var category_name =  $("#menu"+category_id).prev("h3").text();
            var ppg =  $("#ppgcalc"+id).val();
            var purchaseid =  $("#sales_"+id+"_purchaseid").val();
            var discType =  $("input[name='sales["+id+"][discType]']").val();
            var discPercentage =  $("input[name='sales["+id+"][discPercentage]']").val();
            var discAmount =  $("input[name='sales["+id+"][discAmount]']").val();
            var discQuant =  $("input[name='sales["+id+"][discQuant]']").val();

            //alert(product_name);
  //          $.ajax({
  //              type:"post",
  //              url:"getproductName.php?product_id="+product_id+"&category_id="+category_id,
  //              datatype:"text",
  //              //async: false,
  //              success:function(data)
  //              {
  //                   product_name = data;
  //              }
  //           });
            
     
      
      if(euro_val != '' && (gram_val != '' || unit_val != '' )){
           shoppingCart.addItemToCart(product_id, id, category_id, category_name, product_name, gram_val, gift_val, realGram_val, euro_val, 1, ppg, purchaseid, discType, discPercentage, discAmount, discQuant);
           displayCart();
         console.log(cart);
      }else{
         console.log(idSplit);
         if(idSplit != 'realgrcalc' && idSplit != 'grcalcB' && gift_val == ''){
            shoppingCart.removeItemFromCartAll(purchaseid);
             displayCart();
          }else{
             euro_val = 0;
             gram_val = 0;
             unit_val = 0;
             if(gift_val != ''){

                    shoppingCart.addItemToCart(product_id, id, category_id, category_name, product_name, gram_val, gift_val, realGram_val, euro_val, 1, ppg, purchaseid, discType, discPercentage, discAmount, discQuant);
                        displayCart();
                
               console.log(cart);
             }else{
                shoppingCart.removeItemFromCartAll(purchaseid);
                displayCart();
             }
            
          }
      }
    }, 700);
  });

  // remove item from cart
$(document).on("click", ".remove_pr", function(event) {
    var this_id = $(this).attr('id');
    var idVal =  this_id.split('zero');
    var id = idVal[1];
    var product_id = $("#sales_"+id+"_purchaseid").val();
    shoppingCart.removeItemFromCart(product_id);
        displayCart();
});
$(document).on("click", ".delete-item", function(event) {
    var this_val = $(this).val();
    var this_id = $(this).attr('id');
    var idVal =  this_id.split('del_');
    var id = idVal[1];
    var product_id = $(this).data('productid');
    console.log(product_id);
    if(confirm("Are you sure, that you want to remove this item from cart ?")){
        shoppingCart.removeItemFromCart(product_id);

       
         console.log(cart);
              $('#grcalc'+id).val('');
              $('#eurcalc'+id).val('');
              $('#grcalcB'+id).val('');
              $('#realgrcalc'+id).val('');

              var sum = 0;
              $( '.calc' ).each( function( i , e ) {
                  var v = +$( e ).val();
                  if ( !isNaN( v ) )
                      sum += v;
              } );

                var rsum = sum.toFixed(2);
                $('#grcalcTOT').val(rsum);
                $('#grcalcTOT2').val(rsum);
                $('#grcalcTOTexp').val(rsum);

              var sumB = 0;
              $( '.calc2' ).each( function( i , e ) {
                  var vB = +$( e ).val() ;
                  if ( !isNaN( vB ) )
                      sumB += vB;
              } );

                var rsumB = sumB.toFixed(2);

                  var newPrice = rsumB;   

                $('#eurcalcTOT').val(newPrice);
                $('#eurcalcTOT2').val(newPrice);
                $('#eurcalcTOTexp').val(newPrice);

                
                
              var sumC = 0;
              $( '.calc3' ).each( function( i , e ) {
                  var vC = +$( e ).val();
                  if ( !isNaN( vC ) )
                      sumC += vC;
              } );
              $( '.calc4' ).each( function( i , e ) {
                  var vD = +$( e ).val();
                  if ( !isNaN( vD ) )
                      sumC += vD;
              } );

                var sumC = sumC.toFixed(2);
                $('#unitcalcTOT').val(sumC);
                $('#unitcalcTOT2').val(sumC);
                
                
              var sumE = 0;
              $( '.calc5' ).each( function( i , e ) {
                  var vE = +$( e ).val();
                  if ( !isNaN( vE ) )
                      sumE += vE;
              } );

              var sumF = 0;
              $( '.calc6' ).each( function( i , e ) {
                  var vF = +$( e ).val();
                  if ( !isNaN( vF ) )
                      sumF += vF;
              } );

                  sumF += sumE;
                $('#realgrcalcTOT').val(sumF);
              var a = $('#realCredit').val();
              var b = $('#eurcalcTOT').val();
              var total = ((a*1) - (b*1));
              var roundedtotal = total.toFixed(2);
              $('#newcredit').val(roundedtotal);
              $('#newcredit2').val(roundedtotal);
              $('#realNewCredit').val(roundedtotal);
             // remove item from db table 
             if(dispenseFlag == 1){
                $.ajax({
                  type:"post",
                  url:"removeCartitem.php",
                  datatype:"text",
                  data: {'purchase_id': product_id},
                  async: false,
                  success:function(data)
                  {
                      console.log(data);
                  }
               });
            }
               displayCart();
        }else{
           return false;
        }
});
$(document).on("click", ".mini_edit", function(event) {
    var this_val = $(this).val();
    var this_id = $(this).attr('id');
    var idVal =  this_id.split('edit_');
    var id = idVal[1];
    var product_id = $(this).data('productid');
    var cat_id = $(this).data('catid');
    var sort_type = $(this).data('sorttype');
    zoomTo(cat_id,product_id,sort_type);
});

// Open cart popup

$( "#cart_pop" ).dialog({
    autoOpen: false, 
    hide: "puff",
    show : "scale",
    width: 'auto',
    height: 'auto'
 });
 $( ".ship_cart" ).click(function() {
     if(!$("#cart_pop").dialog("isOpen")) {
          $("#cart_pop").dialog("open");
        } else {
          $("#cart_pop").dialog("close");
        }
 });
 displayCart();