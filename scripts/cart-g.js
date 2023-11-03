if(dispenseFlag != 1){
  var blank_cart = [];
   sessionStorage.setItem('shoppingCart', JSON.stringify(blank_cart));
}
console.log(realWeight);
 var shoppingCart = (function() {

  cart = [];

    // Constructor
  function Item(product_id, current_id, cat_id, cat_name, name, units, grams, gram_gifts, unit_gifts, realGrams, price, count) {
    this.product_id = product_id;
    this.current_id = current_id;
    this.cat_id = cat_id;
    this.cat_name = cat_name;
    this.name = name;
    this.units = units;
    this.grams = grams;
    this.gram_gifts = gram_gifts;
    this.unit_gifts = unit_gifts;
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
  obj.addItemToCart = function(product_id, current_id, cat_id, cat_name, name, units, grams, gram_gifts, unit_gifts, realGrams, price, count){
    for(var item in cart) {
      if(cart[item].product_id === product_id) {
        cart[item].current_id = current_id;
        cart[item].cat_id = cat_id;
        cart[item].cat_name = cat_name;
        cart[item].count = count;
        cart[item].name = name;
        cart[item].units = units;
        cart[item].grams = grams; 
        cart[item].gram_gifts = gram_gifts; 
        cart[item].unit_gifts = unit_gifts; 
        cart[item].realGrams = realGrams;
        cart[item].price = price;
        saveCart();
        return;
      }
    }
    var item = new Item(product_id, current_id, cat_id, cat_name, name, units, grams, gram_gifts, unit_gifts, realGrams, price, count);
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
    var sumDisc = 0;
    $("input[type=checkbox]:checked").each(function(){
        sumDisc += parseInt($(this).val());
      });
    $('#totDiscount').html("(" + sumDisc + "%)");
    $('#totDiscountInput').val(sumDisc);
    
    var appliedDisc = (100 - sumDisc) / 100;
    
    var tempPrice = Number(totalCart.toFixed(2)) * appliedDisc;
    var eurdisc = $('#eurdiscount').val();
    
    var newPrice = tempPrice - eurdisc;
    var euroTotal = Math.round((newPrice + Number.EPSILON) * 100) / 100
    $("#eurcalcTOT").val(Number(euroTotal.toFixed(2)));
    return Number(euroTotal.toFixed(2));
  }  
// total gram

  obj.totalGram = function() {
    var totalGram = 0;
    for(var item in cart) {
      var cart_grams = 0;
      var cart_gram_gifts  = 0;
      if(cart[item].grams != null){
          cart_grams = cart[item].grams;
      }      
      if(cart[item].gram_gifts != null){
          cart_gram_gifts = cart[item].gram_gifts;
      }
      totalGram += Number(cart_grams) + Number(cart_gram_gifts);
    }
    if(totalGram == null){
        totalGram = 0;
    }
    console.log(Number(totalGram.toFixed(2)));
    $("#grcalcTOT").val(Number(totalGram.toFixed(2)));
    return Number(totalGram.toFixed(2));
  }  

// total unit

  obj.totalUnit = function() {
    var totalUnit = 0;
    for(var item in cart) {
      var cart_units = 0;
      var cart_unit_gifts  = 0;
      if(cart[item].units != null){
          cart_units = cart[item].units;
      }      
      if(cart[item].unit_gifts != null){
          cart_unit_gifts = cart[item].unit_gifts;
      }
      totalUnit += Number(cart_units) + Number(cart_unit_gifts);
    }
    if(totalUnit == null){
        totalUnit = 0;
    }
     console.log(Number(totalUnit.toFixed(2)));
    $("#unitcalcTOT").val(Number(totalUnit.toFixed(2)));
    return Number(totalUnit.toFixed(2));
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
     var gifts;
      var output = "";
      for(var i in cartArray) {
        var units =   cartArray[i].units;
        var grams =   cartArray[i].grams;
        var realGrams =   cartArray[i].realGrams;
        var gram_gifts =   cartArray[i].gram_gifts;
        var unit_gifts =   cartArray[i].unit_gifts;
        if(realGrams == null || realGrams == ''){
           realGrams = '0';
        }        
        if(grams == null || grams == ''){
           grams = '0';
        }        
        if(units == null || units == ''){
           units = '0';
        }
        if(gram_gifts != null){
            gifts = gram_gifts + " g";
        }
        if(unit_gifts != null){
           gifts = unit_gifts+ " u";
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
 // $(document).on("change keyup blur keypress click",".calc,.calc2,.calc3,.calc5,.calc4,.calc6, .cart_btn", function(event){
  $(document).on("click",".cart_btn", function(event){
     event.preventDefault();
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
        var disc_quant = $("#volume_"+id+"_discount_amount").val();
        if(disc_quant != ''){
             quant_val = disc_quant;
         }else{
          quant_val = $("#grcalc"+id).val();
        }
        if(sort_type == null || sort_type == 1){
          gram_val = quant_val;
        }else{
          unit_val = quant_val;
        }
         var disc_price = $("#volume_"+id+"_discount").val();
         if(disc_price != ''){
             euro_val = disc_price;
         }else{
          euro_val = $("#eurcalc"+id).val();
        }
        realGram_val = $("#realgrcalc"+id).val();
        if(gram_val != null){
          var gram_gift_val = $("#grcalcB"+id).val();
          var gift_val = gram_gift_val;
        }
        if(unit_val != null){
           var unit_gift_val = $("#grcalcB"+id).val();
            var gift_val = unit_gift_val;
        }
        if(realWeight == 1 && (sort_type == 1 || sort_type == null) && gram_val != '' && gram_val != null && realGram_val == ''){
            alert('Please insert real gram value first!');
            return false;
        }

        var quant_val = $("#grcalc"+id).val();
       
/*        if(gift_val == '' && euro_val == '' && quant_val == ''){
           alert("please enter valid input!");
           return false;
        }*/

        // get product name
            var product_name = $("input[name='sales["+id+"][name]']").val();
            var category_name =  $("#menu"+category_id).prev("h3").text();
            if(gift_val != '' || euro_val != '' || quant_val != ''){
              $("#add_cart"+id).text("Update cart").addClass('update');
            }else{
              if(realWeight == 1 && (sort_type == 1 || sort_type == null)){
                $("#add_cart"+id).html("<img src='images/add-to-cart.png' style='vertical-align:sub;'/> Add to cart").removeClass('update');
              }else{
                $("#add_cart"+id).html("<img src='images/add-to-cart.png' style='vertical-align:sub;'/>").removeClass('update');
              }
            }
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
           shoppingCart.addItemToCart(product_id, id, category_id, category_name, product_name,unit_val, gram_val, gram_gift_val, unit_gift_val, realGram_val, euro_val, 1);
           displayCart();
           shoppingCart.totalCart();
           shoppingCart.totalGram();
           shoppingCart.totalUnit();
         console.log(cart);
      }else{
         console.log(idSplit);
         if(idSplit != 'realgrcalc' && idSplit != 'grcalcB' && gift_val == ''){
            shoppingCart.removeItemFromCartAll(product_id);
             displayCart();
              shoppingCart.totalCart();
              shoppingCart.totalGram();
               shoppingCart.totalUnit();
          }else{
             euro_val = 0;
             gram_val = 0;
             unit_val = 0;
             if(gift_val != ''){

                    shoppingCart.addItemToCart(product_id, id, category_id, category_name, product_name,unit_val, gram_val, gram_gift_val, unit_gift_val, realGram_val, euro_val, 1);
                        displayCart();
                         shoppingCart.totalCart();
                          shoppingCart.totalGram();
                          shoppingCart.totalUnit();
               console.log(cart);
             }else{
                shoppingCart.removeItemFromCartAll(product_id);
                displayCart();
                 shoppingCart.totalCart();
                 shoppingCart.totalGram();
                 shoppingCart.totalUnit();
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
    $("#add_cart"+id).html("<img src='images/add-to-cart.png' style='vertical-align:sub;'/>").removeClass('update');
        displayCart();
         shoppingCart.totalCart();
         shoppingCart.totalGram();
         shoppingCart.totalUnit();
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
              $("#add_cart"+id).html("<img src='images/add-to-cart.png' style='vertical-align:sub;'/>").removeClass('update');
              $('#grcalc'+id).val('');
              $('#eurcalc'+id).val('');
              $('#grcalcB'+id).val('');
              $('#realgrcalc'+id).val('');
        /* console.log(cart);

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
                $('#unitcalcTOT2').val(sumC);*/
                
                
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
               shoppingCart.totalCart();
               shoppingCart.totalGram();
               shoppingCart.totalUnit();

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

 $("#discountholder, #eurdiscount").on('click keyup keypress blur',function(){
     displayCart();
     shoppingCart.totalCart();
    shoppingCart.totalGram();
    shoppingCart.totalUnit();
 });
 displayCart();
 shoppingCart.totalCart();
shoppingCart.totalGram();
shoppingCart.totalUnit();