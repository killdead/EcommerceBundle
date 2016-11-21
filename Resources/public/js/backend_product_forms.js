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
$(document).on('click', '.add-image', function(event) {
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

////////////////////// PRICES /////////////////////////////

//DETECT CHANGES ON "PRICE"

  $(document).on('change key paste keyup', "[id^=product_productVersions][id$='price']", function() {
    var price_with_dots =  parseFloat($(this).val().replace(',', '.'));
    var tax_rate = $(this).data('tax-rate'); 
    var price_plus_taxes =  price_with_dots * tax_rate; 
    var index_collection = $(this).attr('id').match(/\d+/);
    $('#product_productVersions_' + index_collection + '_price_plus_taxes').val(accounting.formatNumber(price_plus_taxes, '2', '', ',')); 
  });
  //TRIGGER IT WHEN EDITING ALSO - TRIGGER IT WHEN EDITING ALSO - 
  $("[id^=product_productVersions][id$='price']").trigger('change');

  console.log($("[id^=product_productVersions][id$='price_plus_taxes']"));

  //DETECT CHANGES ON "PRICE PLUS TAXES"
  $(document).on('change key paste keyup load', "[id^=product_productVersions][id$='price_plus_taxes']", function() {
    var price_with_dots =  parseFloat($(this).val().replace(',', '.'));
    var tax_rate = $(this).data('tax-rate'); 
    var price =  price_with_dots / tax_rate; 
    var index_collection = $(this).attr('id').match(/\d+/);
    $('#product_productVersions_' + index_collection + '_price').val(accounting.formatNumber(price, 7, '', ',')); 
  }).trigger('change');


///////////////////////////////////////////////

////////////////////// PRICES /////////////////////////////

//DETECT CHANGES ON "PRICE"

  $(document).on('change key paste keyup', "[id^=product_productVersions][id$='salePrice']", function() {
    var price_with_dots =  parseFloat($(this).val().replace(',', '.'));
    var tax_rate = $(this).data('tax-rate'); 
    var sale_price_plus_taxes =  price_with_dots * tax_rate; 
    var index_collection = $(this).attr('id').match(/\d+/);
console.log(sale_price_plus_taxes);
console.log(index_collection);
console.log($('#product_productVersions_' + index_collection + 'sale_amount_plus_taxes'));
    $('#product_productVersions_' + index_collection + '_sale_amount_plus_taxes').val(accounting.formatNumber(sale_price_plus_taxes, '2', '', ',')); 
  });
  //TRIGGER IT WHEN EDITING ALSO - TRIGGER IT WHEN EDITING ALSO - 
  $("[id^=product_productVersions][id$='salePrice']").trigger('change');

  console.log($("[id^=product_productVersions][id$='sale_price_plus_taxes']"));

  //DETECT CHANGES ON "PRICE PLUS TAXES"
  $(document).on('change key paste keyup load', "[id^=product_productVersions][id$='_sale_amount_plus_taxes']", function() {
    var price_with_dots =  parseFloat($(this).val().replace(',', '.'));
    var tax_rate = $(this).data('tax-rate'); 
    var price =  price_with_dots / tax_rate; 
    var index_collection = $(this).attr('id').match(/\d+/);
    $('#product_productVersions_' + index_collection + '_salePrice').val(accounting.formatNumber(price, 7, '', ',')); 
  }).trigger('change');


///////////////////////////////////////////////
