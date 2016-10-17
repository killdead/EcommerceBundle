//add element to collection form
$('.add-product').on('click', function(event) {
  event.preventDefault();
  var prototype = $(this).prev().data('prototype');
  var index = $(this).prev().children().length;
  prototype = prototype.replace(/\__name__/g, index);
  $(this).prev().append($.parseHTML(prototype));
});

$('.add-image').on('click', function(event) {
  event.preventDefault();

  var prototype = $(this).prev().data('prototype');
  var index = $(this).prev().children().length;
  prototype = prototype.replace(/\__name__/g, index);
  $(this).prev().append($.parseHTML(prototype));
});

//delete a product version
$(document).on('click', '.delete-product', function(event) {
  event.preventDefault();
  $(this).prev().remove();
  $(this).remove();
})

$(document).on('click', '.delete-image', function(event) {
  event.preventDefault();
  $(this).parent().parent().remove();
});


$(document).ready(function() {
  //DETECT CHANGES ON "PRICE"
  $('.price').on('change key paste keyup', function() {
    var price_with_dots =  parseFloat($(this).val().replace(',', '.'));
    var tax_rate = $(this).data('tax-rate'); 
    var price_plus_taxes =  price_with_dots * tax_rate; 
    $('.price_plus_taxes').val(accounting.formatNumber(price_plus_taxes, 2, '', ',')); 
  }).trigger('change');
});

//DETECT CHANGES ON "PRICE PLUS TAXES"
$('.price_plus_taxes').on('change key paste keyup', function() {
  var price_with_dots =  parseFloat($(this).val().replace(',', '.'));
  var tax_rate = $(this).data('tax-rate'); 
  var price =  price_with_dots / tax_rate; 
  $('.price').val(accounting.formatNumber(price, 2, '', ',')); 
});
