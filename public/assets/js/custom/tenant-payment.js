
(function ($) {
    "use strict";
    var invoiceTypes = JSON.parse($('.invoiceTypes').val());
    var typesHtml = '';
    Object.entries(invoiceTypes).forEach((type) => {
        typesHtml += '<option value="' + type[1].id + '">' + type[1].name + '</option>';
    });

    $('#addInvoice').on('click', function () {
        var selector = $('#createNewInvoiceModal');
        selector.find('.is-invalid').removeClass('is-invalid');
        selector.find('.error-message').remove();
        selector.modal('show')
        selector.find('form').trigger('reset');
    });

    $(document).on("click", ".add-field", function () {
        // Clone must match the ow-styled first row (ow-form-section/ow-label/ow-input), carry the
        // amount-label (so selecting "Rent" hides Amount on clones too), and the + Add type link.
        $(this).closest('form').find('.multi-fields').append(
            `<div class="multi-field mb-3">
                <div class="ow-form-section mb-2">
                    <input type="hidden" name="invoiceItem[id][]" value="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="ow-label mb-0">Invoice type</label>
                                <button type="button" class="ow-link-btn" data-bs-toggle="modal" data-bs-target="#addInvoiceTypeModal">+ Add type</button>
                            </div>
                            <select class="form-select ow-input invoiceItem-invoice_type_id" name="invoiceItem[invoice_type_id][]">
                                <option value="">-- Select type --</option>
                                ${typesHtml}
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="ow-label amount-label">Amount</label>
                            <input type="number" name="invoiceItem[amount][]" class="form-control ow-input invoiceItem-amount" placeholder="0.00">
                        </div>
                        <div class="col-md-12">
                            <label class="ow-label">Description</label>
                            <textarea class="form-control ow-input invoiceItem-description" name="invoiceItem[description][]" placeholder="Optional notes…" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <button type="button" class="remove-field ow-remove-btn">Remove item</button>
            </div>`
        )
    });

    $(document).on("click", ".remove-field", function () {
        $(this).parent(".multi-field").remove();
    });

    $(document).on('click', '.payStatus', function () {
        let detailsUrl = $(this).data('detailsurl');
        commonAjax('GET', detailsUrl, getDetailsShowRes, getDetailsShowRes);
    });

    function getDetailsShowRes(response) {
        const selector = $('#payStatusChangeModal');
        selector.find('input[name=id]').val(response.data.invoice.id)
        selector.find('select[name=status]').val(response.data.invoice.status)
        selector.modal('show')
    }

    $('#allInvoicePaymentDataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        responsive: true,
        ajax: $('#route').val(),
        order: [1, 'desc'],
        ordering: false,
        autoWidth: false,
        drawCallback: function () {
            $(".dataTables_length select").addClass("form-select form-select-sm");
        },
        language: {
            'paginate': {
                'previous': '<span class="iconify" data-icon="icons8:angle-left"></span>',
                'next': '<span class="iconify" data-icon="icons8:angle-right"></span>'
            }
        },
        columns: [
            { "data": 'DT_RowIndex', "name": 'DT_RowIndex', orderable: false, searchable: false },
            { "data": "property_name", "name": "properties.name" },
            { "data": "unit_name", "name": "property_units.unit_name" },
            { "data": "month" },
            { "data": "invoice", },
            { "data": "created_at" },
            { "data": "due_date" },
            { "data": "amount", "name": "amount" },
            { "data": "status", "name": "status" },
        ]
    });
})(jQuery)
