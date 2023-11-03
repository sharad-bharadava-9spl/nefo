var blank_cart = [];
   sessionStorage.setItem('barshoppingCart', JSON.stringify(blank_cart));
 var shoppingCart = (function() {
  cart = [];

    // Constructor
  function Item(product_id, cat_id, cat_name, name, units, gifts, price, count) {
    this.product_id = product_id;
    this.cat_id = cat_id;
    this.cat_name = cat_name;
    this.name = name;
    this.units = units;
    this.gifts = gifts;
    this.price = price;
    this.count = count;
  }

  // Save cart
  function saveCart() {
    sessionStorage.setItem('barshoppingCart', JSON.stringify(cart));
  }
  
    // Load cart
  function loadCart() {
    cart = JSON.parse(sessionStorage.getItem('barshoppingCart'));
  }
  if (sessionStorage.getItem("barshoppingCart") != null) {
    loadCart();
  }
  var obj = {};
  // Add Product to cart
  obj.addItemToCart = function(product_id, cat_id, cat_name, name, units, gifts, price, count){
    for(var item in cart) {
      if(cart[item].product_id === product_id) {
        cart[item].cat_id = cat_id;
        cart[item].cat_name = cat_name;
        cart[item].count = count;
        cart[item].name = name;
        cart[item].units = units; 
        cart[item].gifts = gifts; 
        cart[item].price = price;
        saveCart();
        return;
      }
    }
    var item = new Item(product_id, cat_id, cat_name, name, units, gifts, price, count);
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
        output += "<tr>"
          + "<td class='dispensetd'>" + cartArray[i].name + "</td>" 
          + "<td class='dispensetd'>" + cartArray[i].cat_name + "</td>" 
          + "<td class='dispensetd'>" + cartArray[i].units + " u</td>"
          + "<td class='dispensetd'>" + cartArray[i].gifts + " u</td>"
          + "<td class='dispensetd'>" + cartArray[i].price + " "+currencyoperator+"</td>"
          + "<td><img src='images/delete.png' data-productId="+cartArray[i].product_id+"  width='17' class='delete-item' id=del_"+cartArray[i].current_id+"></td>"
          +  "</tr>";
      }
     $('.show_cart_data').html(output);
     $('#cart_count').html(shoppingCart.totalCount());
     console.log(shoppingCart.totalCart());
  }

  // add or remove itmes to cart on input change
  $(document).on("change keyup keypress",".calc2,.calc3,.calc4", function(event){
     var idSplit,unit_val,euro_val;
     var this_val = $(this).val();
/*    if($(event.target).hasClass('calc3')){
         idSplit = 'grcalc';
    }else if($(event.target).hasClass('calc2')){
         idSplit = 'eurcalc';
    }*/
      var this_id = $(this).attr('id');
     idVal = this_id.split(/([0-9]+)/);
      var id = idVal[1];
      var idSplit = idVal[0]; 
      euro_val = $("#eurcalc"+id).val();
      unit_val = $("#grcalc"+id).val();
      var product_id = $("#sales_"+id+"_purchaseid").val();
      var category_id = $("input[name='sales["+id+"][category]']").val();
      var gift_val = $("#grcalcB"+id).val();
      // get product name
          var product_name = $("input[name='sales["+id+"][name]']").val();
           var category_name =  $("#barcat"+category_id).prev(".bartitle").children('h3').text();
//          $.ajax({
//              type:"post",
//              url:"bar-getproductName.php?product_id="+product_id,
//              datatype:"text",
//              async: false,
//              success:function(data)
//              {
//                   product_name = data;
//              }
//           });
          
   
    
    if(this_val != ''  && euro_val != '' && unit_val != ''){
       shoppingCart.addItemToCart(product_id, category_id, category_name, product_name, unit_val, gift_val, euro_val, 1);
       displayCart();
       console.log(cart);
    }else{
           euro_val = 0;
           unit_val = 0;
      if(idSplit != 'grcalcB' && gift_val == ''){
          shoppingCart.removeItemFromCartAll(product_id);
           displayCart();
          console.log(cart);
        }else{
           euro_val = 0;
           unit_val = 0;
           if(gift_val != ''){
              shoppingCart.addItemToCart(product_id, category_id, category_name, product_name, unit_val, gift_val, euro_val, 1);
             displayCart();
           }else{
              shoppingCart.removeItemFromCartAll(product_id);
              displayCart();
           }
          
        }
    }
    
  });

  // add item on touch buttons click
  $(document).on("click",".touch_plus, .touch_minus", function(event){
     var idSplit,unit_val,euro_val;
     var this_val = $(this).val();
/*    if($(event.target).hasClass('touch_plus')){
         idSplit = 'plus';
    }else if($(event.target).hasClass('touch_minus')){
         idSplit = 'minus';
    }*/
      var this_id = $(this).attr('id');
      idVal = this_id.split(/([0-9]+)/);
      var id = idVal[1];
      var idSplit = idVal[0]; 
/*      var this_id = $(this).attr('id');
      var idVal =  this_id.split(idSplit);
      var id = idVal[1];*/
      euro_val = $("#eurcalc"+id).val();
      unit_val = $("#grcalc"+id).val();
      var product_id = $("#sales_"+id+"_purchaseid").val();
      var category_id = $("input[name='sales["+id+"][category]']").val();
      var gift_val = $("#grcalcB"+id).val();
      // get product name
       var product_name = $("input[name='sales["+id+"][name]']").val();
        /*  var product_name = null;
          $.ajax({
              type:"post",
              url:"bar-getproductName.php?product_id="+product_id,
              datatype:"text",
              async: false,
              success:function(data)
              {
                   product_name = data;
              }
           });*/
          var category_name =  $("#barcat"+category_id).prev(".bartitle").children('h3').text();
   
    
    if(euro_val != '' && unit_val != ''){
       shoppingCart.addItemToCart(product_id, category_id, category_name, product_name, unit_val, gift_val, euro_val, 1);
       displayCart();
       console.log(cart);
    }else{
           euro_val = 0;
           unit_val = 0;
      if(idSplit != 'grcalcB' && gift_val == ''){
          shoppingCart.removeItemFromCartAll(product_id);
           displayCart();
          console.log(cart);
        }else{
           euro_val = 0;
           unit_val = 0;
           if(gift_val != ''){
              shoppingCart.addItemToCart(product_id, category_id, category_name, product_name, unit_val, gift_val, euro_val, 1);
             displayCart();
           }else{
              shoppingCart.removeItemFromCartAll(product_id);
              displayCart();
           }
          
        }
    }
    
  });
  // Add item on real grams change
/*  $(document).on("change keyup keypress",".calc5", function(event){
      var this_val = $(this).val();
      var this_id = $(this).attr('id');
      var idVal =  this_id.split('realgrcalc');
      var id = idVal[1];
      var euro_val = $("#eurcalc"+id).val();
      var gram_val = $("#grcalc"+id).val();
      var realGram_val = $("#realgrcalc"+id).val();
      var product_id = $("#sales_"+id+"_productid").val();
      var category_id = $("#sales_"+id+"_category").val();
    if(this_val != '' && euro_val != '' && gram_val != ''){
       shoppingCart.addItemToCart(product_id, gram_val, realGram_val, euro_val, 1);
       displayCart();
       console.log(cart);
    }
    
  });*/
    function CartgetItems()
{
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
  
      var sumDisc = 0;
  
      $("input[type=checkbox]:checked").each(function(){
        sumDisc += parseInt($(this).val());
      });
    $('#totDiscount').html("(" + sumDisc + "%)");
    $('#totDiscountInput').val(sumDisc);
    
    var appliedDisc = (100 - sumDisc) / 100;
    
    var tempPrice = rsumB * appliedDisc;
    
    var eurdisc = $('#eurdiscount').val();
    
    var newPrice = tempPrice - eurdisc;   
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
  $('#grcalcTOT2').val(sumC);
  
}

   function CartcomputeTot() {
          var a = $('#realCredit').val();
          var b = $('#eurcalcTOT').val();
          var total = a - b;
          var roundedtotal = total.toFixed(2);
          $('#newcredit').val(roundedtotal);
        $('#realNewCredit').val(roundedtotal);
        }
    
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
              $('#grcalc'+id).val('');
              $('#eurcalc'+id).val('');
              $('#grcalcB'+id).val('');
              CartgetItems();
              CartcomputeTot();
            
            shoppingCart.removeItemFromCart(product_id);
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