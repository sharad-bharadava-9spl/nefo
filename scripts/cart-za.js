if(dispenseFlag != 1){
  var blank_cart = [];
   sessionStorage.setItem('shoppingCart', JSON.stringify(blank_cart));
}
console.log(realWeight);
 var shoppingCart = (function() {

  cart = [];

    // Constructor
  function Item(product_id, current_id, cat_id, cat_name, name, units, grams, gifts, realGrams, price, count) {
    this.product_id = product_id;
    this.current_id = current_id;
    this.cat_id = cat_id;
    this.cat_name = cat_name;
    this.name = name;
    this.units = units;
    this.grams = grams;
    this.gifts = gifts;
    this.realGrams = realGrams;
    this.price = price;
    this.count = count;
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
  obj.addItemToCart = function(product_id, current_id, cat_id, cat_name, name, units, grams, gifts, realGrams, price, count){
    for(var item in cart) {
      if(cart[item].product_id === product_id) {
        cart[item].current_id = current_id;
        cart[item].cat_id = cat_id;
        cart[item].cat_name = cat_name;
        cart[item].count = count;
        cart[item].name = name;
        cart[item].units = units;
        cart[item].grams = grams; 
        cart[item].gifts = gifts; 
        cart[item].realGrams = realGrams;
        cart[item].price = price;
        saveCart();
        return;
      }
    }
    var item = new Item(product_id, current_id, cat_id, cat_name, name, units, grams, gifts, realGrams, price, count);
    cart.push(item);
    saveCart();
 }

   // Set count from item
  obj.setCountForItem = function(product_id, count) {
    for(var i in cart) {
      if (cart[i].product_id === product_id) {
        cart[i].count = count;
        break;
      }
    }
  };
  // Remove item from cart
  obj.removeItemFromCart = function(product_id) {
      for(var item in cart) {
        if(cart[item].product_id == product_id) {
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
      if(cart[item].product_id === product_id) {
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
      totalCart += cart[item].price * cart[item].count;
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
      itemCopy.total = Number(item.price * item.count).toFixed(2);
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
        var units =   cartArray[i].units;
        var grams =   cartArray[i].grams;
        var realGrams =   cartArray[i].realGrams;
        var gifts =   cartArray[i].gifts;
        if(realGrams == null || realGrams == ''){
           realGrams = '0';
        }        
        if(grams == null || grams == ''){
           grams = '0';
        }        
        if(units == null || units == ''){
           units = '0';
        }        
        if(gifts == null || gifts == ''){
           gifts = '0';
        }
        if(realWeight == 1){
          output += "<tr>"
            + "<td class='dispensetd'>" + cartArray[i].name + "</td>" 
            + "<td class='dispensetd'>" + cartArray[i].cat_name + "</td>" 
            + "<td class='dispensetd'>" + grams + " g</td>"
            + "<td class='dispensetd'>" + units + " u</td>"
            + "<td class='dispensetd'>" + realGrams + " g</td>"
            + "<td class='dispensetd'>" + gifts + "</td>"
            + "<td class='dispensetd'>" + cartArray[i].price + " "+currencyoperator+"</td>"
            + "<td><img src='images/delete.png' data-productId="+cartArray[i].product_id+"  width='17' class='delete-item' id=del_"+cartArray[i].current_id+"></td>"
            +  "</tr>";
          }else{
            output += "<tr>"
            + "<td class='dispensetd'>" + cartArray[i].name + "</td>" 
            + "<td class='dispensetd'>" + cartArray[i].cat_name + "</td>" 
            + "<td class='dispensetd'>" + grams + " g</td>"
            + "<td class='dispensetd'>" + units + " u</td>"
            + "<td class='dispensetd'>" + gifts + "</td>"
            + "<td class='dispensetd'>" + cartArray[i].price + " "+currencyoperator+"</td>"
            + "<td><img src='images/delete.png' data-productId="+cartArray[i].product_id+"  width='17' class='delete-item' id=del_"+cartArray[i].current_id+"></td>"
            +  "</tr>";
          }
      }
     $('.show_cart_data').html(output);
     $('#cart_count').html(shoppingCart.totalCount());
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
        var product_id = $("#sales_"+id+"_purchaseid").val();
        var category_id = $("#sales_"+id+"_category").val();
        if(category_id != '1' || category_id != '2'){
           sort_type = $("#menu"+category_id).prev("h3").children("#sort_type"+category_id).val();
        }
        console.log(sort_type);
        if(sort_type == null || sort_type == 1){
          gram_val = $("#grcalc"+id).val();
        }else{
          unit_val = $("#grcalc"+id).val();
        }
         var vol_price = $("#volume_"+id+"_discount").val();
         if(vol_price != ''){
             euro_val = vol_price;
         }else{
          euro_val = $("#eurcalc"+id).val();
        }
        realGram_val = $("#realgrcalc"+id).val();
        var gift_val = $("#grcalcB"+id).val();

        // get product name
            var product_name = $("input[name='sales["+id+"][name]']").val();
            var category_name =  $("#menu"+category_id).prev("h3").text();
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
           shoppingCart.addItemToCart(product_id, id, category_id, category_name, product_name,unit_val, gram_val, gift_val, realGram_val, euro_val, 1);
           displayCart();
         console.log(cart);
      }else{
         console.log(idSplit);
         if(idSplit != 'realgrcalc' && idSplit != 'grcalcB' && gift_val == ''){
            shoppingCart.removeItemFromCartAll(product_id);
             displayCart();
          }else{
             euro_val = 0;
             gram_val = 0;
             unit_val = 0;
             if(gift_val != ''){

                    shoppingCart.addItemToCart(product_id, id, category_id, category_name, product_name,unit_val, gram_val, gift_val, realGram_val, euro_val, 1);
                        displayCart();
                
               console.log(cart);
             }else{
                shoppingCart.removeItemFromCartAll(product_id);
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