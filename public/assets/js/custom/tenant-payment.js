
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
        $(this).closest('form').find('.multi-fields').append(
            `<div class="multi-field border-bottom pb-25 mb-25">
                <input type="hidden" name="invoiceItem[id][]" class="" value="">
                <!-- Modal Inner Form Box Start -->
                <div class="modal-inner-form-box bg-off-white theme-border radius-4 p-20 mb-20">
                    <div class="row">
                        <div class="col-md-6 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">Invoice Type</label>
                            <select class="form-select flex-shrink-0 invoiceItem-invoice_type_id" name="invoiceItem[invoice_type_id][]">
                                <option value="">--Select Type--</option>
                               ${typesHtml}
                            </select>
                        </div>
                        <div class="col-md-6 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">Amount</label>
                            <input type="text" name="invoiceItem[amount][]" class="form-control invoiceItem-amount" placeholder="Amount">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label class="label-text-title color-heading font-medium mb-2">Description</label>
                            <textarea class="form-control invoiceItem-description" name="invoiceItem[description][]" placeholder="Description"></textarea>
                        </div>
                    </div>
                </div>
                <!-- Modal Inner Form Box End -->
                <button type="button" class="remove-field red-color">Remove</button>
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
