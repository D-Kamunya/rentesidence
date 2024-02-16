$('#add').on('click', function () {
    var selector = $('#addDomainModal');
    selector.find('.is-invalid').removeClass('is-invalid');
    selector.find('.error-message').remove();
    selector.modal('show');
});

$(document).on('click', '.edit', function () {
    commonAjax('GET', $('#getDomainInfoRoute').val(), getDataEditRes, getDataEditRes, { 'id': $(this).data('id') });
});

function getDataEditRes(response) {
    var selected = $('#editDomainModal');
    selected.modal('show')
    selected.find('input[name=custom]').val(response.custom)
}
