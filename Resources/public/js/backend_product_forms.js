//ADD PRODUCT VERSION - ADD PRODUCT VERSION - ADD PRODUCT VERSION
$('.add-product').on('click', function(event) {
  event.preventDefault();
  var prototype = $(this).prev().data('prototype');
  var index = $(this).prev().children().length;
  prototype = prototype.replace(/\__name__/g, index);
  $(this).prev().append($.parseHTML(prototype));
});

//DELETE PRODUCT VERSION - DELETE PRODUCT VERSION - DELETE PRODUCT VERSION - 
$(document).on('click', '.delete-product', function(event) {
  event.preventDefault();
  $(this).prev().remove();
  $(this).remove();
})

///////////////////////////////////////////////////////////////

//ADD PRODUCT VERSION IMAGE - ADD PRODUCT VERSION IMAGE 
$('.add-image').on('click', function(event) {
  event.preventDefault();

  var prototype = $(this).prev().data('prototype');
  var index = $(this).prev().children().length;
  prototype = prototype.replace(/\__name__/g, index);
  $(this).prev().append($.parseHTML(prototype));
});

//DELETE PRODUCT VERSION IMAGE - DELETE PRODUCT VERSION IMAGE - 
$(document).on('click', '.delete-image', function(event) {
  event.preventDefault();
  $(this).parent().parent().remove();
});

///////////////////////////////////////////////////////////////

//DETECT CHANGES ON "PRICE"
$(document).ready(function() {
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

///////////////////////////////////////////////

