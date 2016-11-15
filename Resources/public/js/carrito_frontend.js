
//escodemos la flecha hacia abajo si la cantidad es 1
$(document).on('change', '.cart-qty', function() {
    if ($(this).val() == 1) {
      $(this).parents('.subitem').find('.down').hide();
    } else {
      $(this).parents('.subitem').find('.down').show();
    }
});

//REMOVE PRODUCT - REMOVE PRODUCT - REMOVE PRODUCT
$('body').on('click', '.eliminar', function(){
  var product_version_size_id = $(this).parents('.subitem').attr('data-product_version_size_id');
  var subitem = $('.subitem[data-product_version_size_id="' + product_version_size_id + '"]');  
  var product_version_size_id = subitem.data("product_version_size_id");
  var size = subitem.data("size");
  var color_id = subitem.data("color_id");
  var element_collection_id = subitem.data("element_collection_id");
  var url_eliminar = $('.urls').data("url_eliminar");

  $.ajax({
    type: "POST",
    url: url_eliminar, 
    data: { product_version_size_id: product_version_size_id, size: size},
    success: function(response) {
      response = JSON.parse(response);
      $('.subitem[data-product_version_size_id="' + product_version_size_id + '_' + size + '"]').find('.anadir-qty').html(
          '<button class="verde">Añadir al carro</button>' 
      );
      $('.nav .cart-item .tag-pill').text(response.total_number_products);
      updateTotals(response);
      subitem.remove();
    },
  });
});

//MODIFY QUANTITY PRODUCT BY ----INPUT---- - MODIFY QUANTITY PRODUCT BY ----INPUT-----
$("body").on('keyup', '.cart-qty', function() {
  var product_version_size_id = $(this).parents('.subitem').attr('data-product_version_size_id');
  var subitem = $('.subitem[data-product_version_size_id="' + product_version_size_id + '"]');  

  if ($(this).val() == '') {
    clander(subitem, 0);
  } else {
    clander(subitem, $(this).val());
  }
});


//MODIFY QUANTITY PRODUCT BY +/- IN INNER CARTS -  MODIFY QUANTITY PRODUCT BY +/- IN INNER CARTS  
$("body").on('click', '.products-list .subitem .up, .down', function() {
  //aumentamos o disminuimos la cantidad
  if ($(this).hasClass('up')) {
    var new_producto_qty = parseInt($(this).parents('.subitem').find('input').val()) + 1;
  } else {
    var new_producto_qty = parseInt($(this).parents('.subitem').find('input').val()) - 1;
  }

  var product_version_size_id = $(this).parents('.subitem').attr('data-product_version_size_id');
  var subitem = $('.subitem[data-product_version_size_id="' + product_version_size_id + '"]');  

  clander(subitem, new_producto_qty);
});


function clander(aux, new_producto_qty) {
  var product_version_size_id = aux.data("product_version_size_id");
  var size = aux.data("size");
  var color_id = aux.data("color_id");

  var input_qty = aux.find('input');
  var actual_li = aux;
  var url_cantidad = $('.urls').data("url_cantidad");

  $.ajax({
    type: "POST",
    url: url_cantidad,
    data: { product_version_size_id: product_version_size_id, size: size, producto_qty: new_producto_qty },
    dataType: 'json',
    success: function(response) {
      response = JSON.parse(response);
      if (response.stock == '0') {
        alert("Lo sentimos, no tenemos tantas existencias de este producto.");
      } else {

        //establecemos la nueva cantidad del producto en el carrito
        //la llamada a "change()" es para que detecte el "1" cuando pulsamos la fecla hacia abajo
        if (new_producto_qty != 0) {
          input_qty.val(new_producto_qty).change(); //<<<<<< change()
        }

        jander(response, product_version_size_id);

        //var precio_total_subitem = parseFloat(response.precio_total_subitem);
        var precio_total_subitem = (parseFloat(response.precio_total_subitem) * response.tasa_iva);

        precio_total_subitem = accounting.formatMoney(precio_total_subitem);

        actual_li.find('.precio_total_subitem').html(precio_total_subitem);

        $('.nav .cart-item .tag-pill').text(response.total_number_products);

        updateTotals(response);
      }
    }
  })
}


function jander(response, product_version_size_id) {
  //cambiamos el texto del boton "Añadir" si se han quitado productos del carrito y de nuevo hay stock 
  if (response.stock_qty != 0) {

    $("#size input[data-product_version_size_id='" + product_version_size_id  + "']").next('.size-stock').empty();
    $("#size input[data-product_version_size_id='" + product_version_size_id  + "']").attr('disabled', false);
    $('.anadir_subitem button').attr('disabled', false).text('Añadir al carro');

  } else {
    //if the sizes form is hidden (product without sizes), change the text of the button
    if ($('#size').is(":hidden")) {
      $('.anadir_subitem button').attr('disabled', true).text('Producto agotado');
    //if the sizes form is not hidden (product with sizes), lets disable/uncheck the radio button and add "Talla agotada"
    } else {
      $("#size input[data-product_version_size_id='" + product_version_size_id  + "']").attr('disabled', true).prop('checked', false);
      $("#size input[data-product_version_size_id='" + product_version_size_id  + "']").next('.size-stock').html('Talla agotada');
    }

  }
}


//PRESS "ADD TO CART" BUTTON (TO ADD AS NEW OR TO MODIFY QUANTITY) 
$('body').on('click', '.anadir_product', function(){
  //check if the user can select a size and one of them is selected
  if ($('#size').find('input').length != 1 && !$("#size input[name='size']:checked").val()) {
     alert('Tienes que seleccionar una talla, por favor.');
     return false;
  }

  var product_version_size_id = $("#size input[type='radio']:checked").data("product_version_size_id");

  //IF THE PRODUCT IS ALREADY IN THE CART, CALL THE FUNCTION TO UPDATE THE QUANTITY
  if ($(".products-list .subitem[data-product_version_size_id='" + product_version_size_id  + "']").length) {

    clander(
      $(".products-list .subitem[data-product_version_size_id='" + product_version_size_id  + "']"),
      parseInt($(this).closest('.product-container').find('.producto-qty').val()) +  parseInt($(".products-list .subitem[data-product_version_size_id='" + product_version_size_id  + "']").find('.cart-qty').val())
    );
    return false;
  }

  var subitem = $(this).parents('.subitem');
  // ESTA LINEA COMENTADA ES PARA CUANDO QUERAMOS MOSTRAR LOS ____CUADRADITOS DE LOS COLORES___ /////////
  //var producto_color = $(this).parents('.subitem').find('.cuadrado-container.actual').data('color');
  ///////////////////////////////////////////////////////////////////////////////////////////////////

  var producto_color = $(this).find('.cuadrado-container').data('color');
  var producto_qty = $('.producto-qty').val();
  var url_anadir = $(".urls").data("url_anadir");
  var size = $('#size input[type="radio"]:checked').val();

  $.ajax({
    type: "POST",
    url: url_anadir,
    data: { product_version_size_id: product_version_size_id, producto_qty: producto_qty, size: size },
    dataType: 'json',
    success: function(response) {
      response = JSON.parse(response);
      if (response.enStock) {
        var message = 'Lo sentimos, solo queda(n) ' + response.stock + ' unidad(es)' + ' de este producto.';
        alert(message);
      }

      if (response.maxPedido) {
        var message = 'Lo sentimos, puedes adquirir un máximo de ' + response.maxPedido + ' unidad(es)' + ' por pedido.';
        alert(message);
      }
      
      else {
        $(".cart").show();
        $(".products-list").show();
        $(".totales").show();
     
        if (response.stock == 0) {
          $('.anadir_subitem[data-product_version_size_id="' + product_version_size_id + '"]').html(
              '<button class="rojo" disabled>Producto agotado</button>'  
          );
          subitem.find('.producto-qty').hide();
        }
        var precio = accounting.formatMoney(response.precio * response.tasa_iva);
        var precio_total_subitem = (parseFloat(response.precio) * response.tasa_iva) * response.productoQty;

        precio_total_subitem = accounting.formatMoney(precio_total_subitem);

        var items_in_cart = $('.products-list li').first();
        if (items_in_cart.hasClass('odd'))
        {
          var clase = 'even';
        } else {
          var clase = 'odd';
        }
        //is not already in the cart
        if (response.en_carro == 'false')
        { 
          var arrow_down = '';
          if (response.productoQty == 1) {
            var arrow_down = '<div class="subitem-qty down" style="display: none"></div>';
          }
          if (response.size != undefined) {
            var size_string = ' <b>talla ' + response.size + '</b>';
          } else {
            var size_string = '';
            var size = '';
          }
  
          var product_name = $.trim(response.nombre).substring(0, 28).trim(this);
          if (response.nombre.length >= 28) {
            product_name += ' ...';
          }

          var size = $('#size input[type="radio"]:checked').val();
          $('.products-list').prepend(
            '<li class="subitem ' +  clase + '" '  +
            '" data-element_collection_id="' + response.element_collection_id +
            '" data-product_version_size_id="' + product_version_size_id + '"' +
            '" data-size="' + size + '"' +
            ' data-color_id="' + response.color_id + '">' + 
            '<div class="pull-left img-trash"><img src="/uploads/xs/' + response.image_path + '"></div>' +
              product_name + size_string +  
              '<div class="form-inline">' +
                precio + 
                '<div class="qty-container">' + 
                  '<i class="down subitem-qty fa fa-minus-circle "></i> ' + 
                  '<input class="form-control form-control-sm cart-qty" type="text" min="1" max="100" size="1" value="' + response.productoQty + '"> ' + 
                  '<i class="up subitem-qty fa fa-plus-circle "></i> ' + 
                  arrow_down +
                '</div>' +
              '</div>' +
              '<btn class="eliminar" title="Eliminar"><i class="fa fa-trash-o aria-hidden="true"></i></span>' + 
            '</li>'
          );
          $('.metodo-de-envio input[value=' + response.metodo_envio + ']').attr('checked', 'checked');
          $('.metodo-de-pago input[value=' + response.metodo_pago + ']').attr('checked', 'checked');
 
 
          $('.nav .cart-item .tag-pill').text(response.total_number_products);
          $('.left-menu-button .tag-pill').text(response.total_number_products);

        //is already in the cart
        } else {
          var subitem_en_carro = $(".products-list .subitem[data-product_version_size_id='" + product_version_size_id  + "']");
          subitem_en_carro.find('input').val(response.productoQty).change();
          subitem_en_carro.find('.precio_total_subitem').html(precio_total_subitem);

          $('.nav .cart-item .tag-pill').text(response.total_number_products);
        }
        updateTotals(response);
      }
    },
  });
});

function updateNavbar() {
  var numberProducts;
  $('.products-list').each(function() {
    
    numberProducts += parseInt($(this).find('input').val());
  })
}

$('.envio').on('click',function(){
  var envio = $(".envio[name=envio]:checked").val();
  var url_envio = $(".urls").data("url_envio"); 
  //si se elije "48 horas" la opcion Contrareembolso desaparece
/*
  if(envio == 2)
  {
    $('.pago.transferencia').prop('checked', true);
    $('.pago.contra').attr('disabled', 'disabled');
    $('label.pago.contra').css('opacity', '0.5');
  }else{
    $('.pago.contra').attr('disabled', false);
    $('label.pago.contra').css('opacity', '1');
  }
*/
  $.ajax({
    type: "POST",
    url: url_envio,
    data: { metodo_envio: envio },
    dataType: 'json',
    success: function(response) {
      response = JSON.parse(response);
      updateTotals(response);
    }
  });
});

$('.pago').on('click', function(){
  var pago = $(".pago[name=pago]:checked").val();
  var url_pago = $('.urls').data("url_pago");
  $.ajax({
    type: "POST",
    url: url_pago,
    data: { metodo_pago: pago },
    dataType: 'json',
    success: function(response) {
      response = JSON.parse(response);
      //$('.transferencia').attr('checked', 'checked');
      updateTotals(response);

/*
      if (response.metodo_pago == 2) {
        $('.contrareembolso').show();
      }  else {
        $('.contrareembolso').hide();
      }
*/
    }
  });
});

function updateTotals(response)
{
    var subtotal = accounting.formatMoney(response.subtotal);

    var tasa_iva = (parseFloat(response.tasa_iva) - 1) * 0;
    var tasa_re = (parseFloat(response.tasa_re) - 1) * 100;

    var tasa_iva = Math.round(tasa_iva);
    var tasa_iva = Math.floor(tasa_iva);

    tasa_re = tasa_re.toFixed(1);

    var iva = parseFloat(response.iva); 
    var re = parseFloat(response.re); 

    iva_string = accounting.formatMoney(response.iva); 
    re_string = accounting.formatMoney(response.re); 

    var contrareembolso = accounting.formatMoney(response.contrareembolso);

    var total = accounting.formatMoney(response.total);

    subtotal = accounting.formatMoney(response.subtotal);
    $('.totales .subtotal').html(subtotal);

    $('.totales .iva.etiq').html('IVA ' + tasa_iva + '%');
    $('.totales .iva_del_subtotal').html(iva_string);

    $('.totales .re.etiq').html('R.E. ' + tasa_re + '%');
    $('.totales .re_del_subtotal').html(re_string);

    $('.totales .contrareembolso .cantidad').html(contrareembolso);

    $('.totales .total').html(total);

/*
    if(response.metodo_envio == 3)
    {
      $('.gratis').show(); 
      $('.depago').hide(); 
      $('#r1').prop('checked', false);
      $('#r1').attr('disabled', 'disabled');
      $('#r1').prev().css('opacity', '0.5');
      $('#r2').prop('checked', false);
      $('#r2').attr('disabled', 'disabled');
      $('#r2').prev().css('opacity', '0.5');
      $('#r4').prop('checked', false);
      $('#r4').attr('disabled', 'disabled');
      $('#r4').prev().css('opacity', '0.5');
    } else {
      $('.depago').show(); 
      $('.gratis').hide(); 
      var resto = 250 - response.subtotal; 
      resto = accounting.formatMoney(resto); 

      $('.faltan').html(resto);

      $('#r1').attr('disabled', false);
      $('#r2').attr('disabled', false);
      $('#r4').attr('disabled', false);
      $('#cart input:radio[name=envio]').val([response.metodo_envio]);
      $('#r1').prev().css('opacity', 1);
      $('#r2').prev().css('opacity', 1);
      $('#r4').prev().css('opacity', 1);
    }
*/
}

