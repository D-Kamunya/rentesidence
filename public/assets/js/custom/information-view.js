$(document).on('click', '.view', function () {
    commonAjax('GET', $('#getInfoRoute').val(), getDataViewRes, getDataViewRes, { 'id': $(this).data('id') });
});

function getDataViewRes(response) {
    $('.image').attr('src', response.data.image)
    $('.name').html(response.data.name)
    $('.property').html(response.data.property_name)
    $('.distance').html(response.data.distance)
    var contact = response.data.contact_number || '';
    var telHref = contact.replace(/[^0-9+]/g, '');
    $('.contact_number').html(contact ? '<a href="tel:' + telHref + '">' + contact + '</a>' : '')
    $('.additional_information').html(response.data.additional_information)
}
