accounting.settings = {
	currency: {
		symbol : "€",   // default currency symbol is '$'
		format: "%v %s", // controls output: %s = symbol, %v = value/number (can be object: see below)
		decimal : ",",  // decimal point separator
		thousand: ".",  // thousands separator
		precision : 2   // decimal places
	},
	number: {
		precision : 0,  // default precision on numbers is 0
		thousand: ",",
		decimal : "."
	}
}

//escodemos la flecha hacia abajo si la cantidad es 1
$(document).on('change', '.cart-qty', function() {
    if ($(this).val() == 1) {
      $(this).parents('.subitem').find('.down').hide();
    } else {
      $(this).parents('.subitem').find('.down').show();
    }
});

$('body').on('click', '.eliminar', function(){
  var subitem = $(this).parents('.subitem');
  var product_version_id = subitem.data("product_version_id");
  var size = subitem.data("size");
  var color_id = subitem.data("color_id");
  var element_collection_id = subitem.data("element_collection_id");
  var url_eliminar = $('#cart').data("url_eliminar");

  $.ajax({
    type: "POST",
    url: url_eliminar, 
    data: { product_version_id: product_version_id, size: size},
    success: function(response) {
      var subitem_id = subitem.data('subitem_color_id');
      response = JSON.parse(response);
      $('.subitems-container .subitem[data-subitem_color_id="' + product_version_id + '_' + size + '"]').find('.anadir-qty').html(
        '<input min="0" value="1" class="producto-qty">' +
        '<span class="anadir_subitem" ' + 'data-producto-id="' + product_version_id + '_' + size + '">' + 
          '<span class="verde">Añadir</span>' + 
        '</span>'
      );
      updateTotales(response);
      subitem.remove();
    },
  });
});

$("body").on('keyup', '.cart-qty', function() {

  if ($(this).val() == '') {
    clander($(this).parents('.subitem'), 0);
  } else {
    clander($(this).parents('.subitem'), $(this).val());
  }
});


//cambia la cantidad de un producto
$("body").on('click', '#cart .subitem .up, .down', function() {
  //aumentamos o disminuimos la cantidad
  if ($(this).hasClass('up')) {
    var new_producto_qty = parseInt($(this).parents('.subitem').find('input').val()) + 1;
  } else {
    var new_producto_qty = parseInt($(this).parents('.subitem').find('input').val()) - 1;
  }

  var subitem = $(this).parents('.subitem');
  clander(subitem, new_producto_qty);
});


function clander(aux, new_producto_qty) {
  var product_version_id = aux.data("product_version_id");
  var size = aux.data("size");
  var color_id = aux.data("color_id");

  var input_qty = aux.find('input');
  var actual_li = aux;
  var url_cantidad = $('#cart').data("url_cantidad");

  $.ajax({
    type: "POST",
    url: url_cantidad,
    data: { product_version_id: product_version_id, size: size, producto_qty: new_producto_qty },
    dataType: 'json',
    success: function(response) {
      response = JSON.parse(response);
      if (response.stock == '0') {
        alert("Lo sentimos, no tenemos tantas existencias de este producto.");
      } else {
        //establecemos la nueva cantidad del producto en el carrito
        //la llamada a "change()" es para que detecte el "1" cuando pulsamos la fecla hacia abajo
        if (new_producto_qty != 0) {
          input_qty.val(new_producto_qty).change();
        }
        //cambiamos el texto del boton "Añadir" si se han quitado productos del carrito y de nuevo hay stock 
        if (response.stock_qty != 0) {
          $('.anadir_subitem[data-product_version_id="' + product_version_id + '"]').html(
              '<button class="verde">Añadir al carro</button>'  
          );
        } else {
          //if (response.size == null) {
	    $('.anadir_subitem[data-product_version_id="' + product_version_id + '"]').html(
		'<button disabled class="rojo">Producto agotado</button>'  
	    );
          //}
        }
        //var precio_total_subitem = parseFloat(response.precio_total_subitem);
        var precio_total_subitem = (parseFloat(response.precio_total_subitem) * response.tasa_iva);

        precio_total_subitem = accounting.formatMoney(precio_total_subitem);

        actual_li.find('.precio_total_subitem').html(precio_total_subitem);
        updateTotales(response);
      }
    }
  })
}

$('body').on('click', '.anadir_subitem', function(){
  var subitem = $(this).parents('.subitem');
  var product_version_id = $(this).data("product_version_id");
  // ESTA LINEA COMENTADA ES PARA CUANDO QUERAMOS MOSTRAR LOS ____CUADRADITOS DE LOS COLORES___ /////////
  //var producto_color = $(this).parents('.subitem').find('.cuadrado-container.actual').data('color');
  ///////////////////////////////////////////////////////////////////////////////////////////////////

  var producto_color = $(this).find('.cuadrado-container').data('color');
  var producto_qty = $('.producto-qty').val();
  var url_anadir = $("#cart").data("url_anadir");
  var size = $('#size input[type="radio"]:checked').val();

  $.ajax({
    type: "POST",
    url: url_anadir,
    data: { product_version_id: product_version_size_id, producto_qty: producto_qty, size: size },
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
        $("#cart").show();
        $(".totales").show();

        if (response.stock == 0) {
          $('.anadir_subitem[data-product_version_id="' + product_version_id + '"]').html(
              '<button class="rojo" disabled>Producto agotado</button>'  
          );
          subitem.find('.producto-qty').hide();
        }
        var precio = accounting.formatMoney(response.precio * response.tasa_iva);
        var precio_total_subitem = (parseFloat(response.precio) * response.tasa_iva) * response.productoQty;

        precio_total_subitem = accounting.formatMoney(precio_total_subitem);

        var items_in_cart = $('#cart ul li').first();
        if (items_in_cart.hasClass('odd'))
        {
          var clase = 'even';
        } else {
          var clase = 'odd';
        }
        if (response.en_carro == 'false')
        { 
          var arrow_down = '';
          if (response.productoQty == 1) {
            var arrow_down = '<div class="subitem-qty down" style="display: none"></div>';
          }
          if (response.size != undefined) {
            var size_string = ' talla ' + response.size;
          } else {
            var size_string = '';
            var size = '';
          }


          var size = $('#size input[type="radio"]:checked').val();
          $('#cart ul').prepend(
            '<li class="list-group-item subitem ' +  clase + '" '  +
            '" data-element_collection_id="' + response.element_collection_id +
            '" data-product_version_id="' + product_version_id + '"' +
            '" data-size="' + size + '"' +
            ' data-color_id="' + response.color_id + '">' + 
            '<div class="movilin-container"><img src="/images/movilin.svg"></div>' +
              response.nombre + size_string + '<br>' + precio + ' X ' + 
              '<i class="down subitem-qty fa fa-minus-circle "></i>&nbsp' + 
              '<input type="text" class="cart-qty" min="1" max="100" size="1" value="' + response.productoQty + '">' + 
              '&nbsp<div style="display: inline-block; width: 20px; height: 12px">' +
                '<i class="up subitem-qty fa fa-plus-circle "></i>' + 
                arrow_down +
              '</div>' +
              'unid. = ' +
              '<span class="precio_total_subitem">' + precio_total_subitem + '</span>' +
              '<span class="eliminar" title="Eliminar">X</span>' + 
            '</li>'
          );
          $('.metodo-de-envio input[value=' + response.metodo_envio + ']').attr('checked', 'checked');
          $('.metodo-de-pago input[value=' + response.metodo_pago + ']').attr('checked', 'checked');

        } else {
          var subitem_en_carro = $("#cart .subitem[data-size='" + size  + "'].subitem[data-product_version_id='" + product_version_id  + "']");
          subitem_en_carro.find('input').val(response.productoQty).change();
          subitem_en_carro.find('.precio_total_subitem').html(precio_total_subitem);
        }
        updateTotales(response);
      }
    },
  });
});

$('.envio').on('click',function(){
  var envio = $(".envio[name=envio]:checked").val();
  var url_envio = $("#cart").data("url_envio"); 
  //si se elije "48 horas" la opcion Contrareembolso desaparece
  if(envio == 2)
  {
    $('.pago.transferencia').prop('checked', true);
    $('.pago.contra').attr('disabled', 'disabled');
    $('label.pago.contra').css('opacity', '0.5');
  }else{
    $('.pago.contra').attr('disabled', false);
    $('label.pago.contra').css('opacity', '1');
  }
  $.ajax({
    type: "POST",
    url: url_envio,
    data: { metodo_envio: envio },
    dataType: 'json',
    success: function(response) {
      response = JSON.parse(response);
      updateTotales(response);
    }
  });
});

$('.pago').on('click', function(){
  var pago = $(".pago[name=pago]:checked").val();
  var url_pago = $('#cart').data("url_pago");
  $.ajax({
    type: "POST",
    url: url_pago,
    data: { metodo_pago: pago },
    dataType: 'json',
    success: function(response) {
      response = JSON.parse(response);
      $('.transferencia').attr('checked', 'checked');
      updateTotales(response);

      if (response.metodo_pago == 2) {

        $('.contrareembolso').show();
      }  else {
        $('.contrareembolso').hide();
      }
    }
  });
});

function updateTotales(response)
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
    $('#cart .subtotal').html(subtotal);

    $('#cart .iva.etiq').html('IVA ' + tasa_iva + '%');
    $('#cart .iva_del_subtotal').html(iva_string);

    $('#cart .re.etiq').html('R.E. ' + tasa_re + '%');
    $('#cart .re_del_subtotal').html(re_string);

    $('#cart .contrareembolso .cantidad').html(contrareembolso);

    $('#cart .total').html(total);

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
}

