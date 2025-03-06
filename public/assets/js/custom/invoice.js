(function ($) {
    "use strict";
    var stateSelector;
    var invoiceTypes = JSON.parse($(".invoiceTypes").val());
    var typesHtml = "";
    Object.entries(invoiceTypes).forEach((type) => {
        typesHtml +=
            '<option value="' + type[1].id + '">' + type[1].name + "</option>";
    });
    $("#createReminderModal").on("click", function () {
        $("#showFormNoticeModal").modal("toggle");
    });

    $("#add").on("click", function () {
        var selector = $("#createNewInvoiceModal");
        selector.find(".is-invalid").removeClass("is-invalid");
        selector.find(".error-message").remove();
        selector.modal("show");
        selector.find("form").trigger("reset");
    });

    $(document).on("click", ".add-field", function () {
        $(this)
            .closest("form")
            .find(".multi-fields")
            .append(
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
                            <label class="amount-label label-text-title color-heading font-medium mb-2">Amount</label>
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
            );
    });

    $(document).on("click", ".remove-field", function () {
        $(this).parent(".multi-field").remove();
    });

    $(document).on("click", ".edit", function () {
        stateSelector = $(".edit_modal");
        let detailsUrl = $(this).data("detailsurl");
        commonAjax("GET", detailsUrl, getDataEditRes, getDataEditRes);
    });

    function getDataEditRes(response) {
        var selector = $(".edit_modal");
        selector.find(".is-invalid").removeClass("is-invalid");
        selector.find(".error-message").remove();
        selector.modal("show");
        selector.find("input[name=id]").val(response.data.invoice.id);
        selector.find("input[name=name]").val(response.data.invoice.name);
        selector
            .find("select[name=property_id]")
            .val(response.data.invoice.property_id);
        getPropertyUnits(response.data.invoice.property_id);
        setTimeout(() => {
            selector
                .find("select[name=property_unit_id]")
                .val(response.data.invoice.property_unit_id);
        }, 2000);
        selector.find("select[name=month]").val(response.data.invoice.month);
        selector
            .find("input[name=due_date]")
            .val(response.data.invoice.due_date);

        var html = "";
        Object.entries(response.data.items).forEach((item) => {
            var typesHtml = "";
            Object.entries(invoiceTypes).forEach((type) => {
                if (type[1].id == item[1].invoice_type_id) {
                    typesHtml +=
                        '<option value="' +
                        type[1].id +
                        '" selected>' +
                        type[1].name +
                        "</option>";
                } else {
                    typesHtml +=
                        '<option value="' +
                        type[1].id +
                        '">' +
                        type[1].name +
                        "</option>";
                }
            });
            html += `<div class="multi-field border-bottom pb-25 mb-25">
                        <input type="hidden" name="invoiceItem[id][]" class="" value="${item[1].id}">
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
                                    <label class="amount-label label-text-title color-heading font-medium mb-2">Amount</label>
                                    <input type="text" name="invoiceItem[amount][]" class="form-control invoiceItem-amount" placeholder="Amount" value="${item[1].amount}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="label-text-title color-heading font-medium mb-2">Description</label>
                                    <textarea class="form-control invoiceItem-description" name="invoiceItem[description][]" placeholder="Description">${item[1].description}</textarea>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Inner Form Box End -->
                        <button type="button" class="remove-field red-color">Remove</button>
                    </div>`;
        });
        selector.find(".multi-fields").html(html);
    }

    $(document).on("click", ".payStatus", function () {
        let detailsUrl = $(this).data("detailsurl");
        commonAjax("GET", detailsUrl, getDetailsShowRes, getDetailsShowRes);
    });

    function getDetailsShowRes(response) {
        const selector = $("#payStatusChangeModal");
        selector.find("input[name=id]").val(response.data.invoice.id);
        selector.find("select[name=status]").val(response.data.invoice.status);
        selector.modal("show");
    }

    window.view = function (url) {
        commonAjax("GET", url, getDetailsViewRes, getDetailsViewRes);
    };

    $(document).on("click", ".view", function () {
        let detailsUrl = $(this).data("detailsurl");
        commonAjax("GET", detailsUrl, getDetailsViewRes, getDetailsViewRes);
    });

    function getDetailsViewRes(response) {
        const selector = $("#invoicePreviewModal");
        selector.modal("show");
        let invoicePrintUrl = $("#invoicePrint").val();
        selector
            .find("#downloadInvoice")
            .attr(
                "href",
                invoicePrintUrl.replace("@", response.data.invoice.id)
            );
        selector.find(".invoiceNo").text(response.data.invoice.invoice_no);
        selector
            .find(".invoicePayDate")
            .text(dateFormat(response.data.invoice.updated_at, "YY-MM-DD"));
        selector.find(".invoiceMonth").text(response.data.invoice.month);
        var status = "Pending";
        if (response.data.invoice.status == "1") {
            status = "Paid";
        }
        selector.find(".invoiceStatus").html(status);

        selector
            .find(".tenantName")
            .html(
                response.data.tenant.first_name +
                    " " +
                    response.data.tenant.last_name
            );
        var invoiceAddHtml = "";

        invoiceAddHtml += ` <h5>${response.data.owner.print_name}</h5>
                            <h6>${response.data.owner.print_address}</h6>
                            <small>${response.data.owner.print_contact}</small>
                            `;
        selector.find(".pay-invoice-address").html(invoiceAddHtml);
        selector.find(".tenantEmail").html(response.data.tenant.email);
        selector.find(".propertyName").html(response.data.tenant.property_name);
        selector.find(".unitName").html(response.data.tenant.unit_name);

        var html = "";
        Object.entries(response.data.items).forEach((item) => {
            var typeName;
            Object.entries(invoiceTypes).forEach((type) => {
                if (type[1].id == item[1].invoice_type_id) {
                    typeName = type[1].name;
                }
            });
            html += `<tr>
                        <td>${typeName}</td>
                        <td>${item[1].description}</td>
                        <td class="invoice-tbl-last-field">${currencyPrice(
                            item[1].amount
                        )}</td>
                        <td class="invoice-tbl-last-field">${currencyPrice(
                            item[1].tax_amount
                        )}</td>
                        <td class="invoice-tbl-last-field">${currencyPrice(
                            parseFloat(
                                parseFloat(item[1].amount) +
                                    parseFloat(item[1].tax_amount)
                            ).toFixed(2)
                        )}</td>
                    </tr>`;
        });
        selector.find("#invoiceItems").html(html);
        selector
            .find(".total")
            .html(currencyPrice(response.data.invoice.amount));
        if (response.data.order != null) {
            selector
                .find(".orderDate")
                .html(dateFormat(response.data.order.created_at, "YYYY-MM-DD"));
            selector
                .find(".orderPaymentTitle")
                .html(response.data.order.gatewayTitle ?? "Cash");
            selector
                .find(".orderPaymentId")
                .html(response.data.order.payment_id);
            selector
                .find(".orderTotal")
                .html(currencyPrice(response.data.order.total));
        } else {
            selector.find(".orderDate").html();
            selector.find(".orderPaymentTitle").html();
            selector.find(".orderPaymentId").html();
            selector.find(".orderTotal").html();
        }
    }

    $(document).on("change", ".property_id", function () {
        stateSelector = $(this);
        if (stateSelector.val() == "All") {
            var html =
                '<option value="All" selected readonly>All Tenants</option>';
            stateSelector
                .closest(".modal")
                .find(".propertyUnitSelectOption")
                .html(html);
        } else {
            getPropertyUnits(stateSelector.val());
        }
    });

    function getPropertyUnits(property_id) {
        var getPropertyUnitsRoute = $("#getPropertyUnitsRoute").val();
        commonAjax(
            "GET",
            getPropertyUnitsRoute,
            getPropertyUnitsRes,
            getPropertyUnitsRes,
            { property_id: property_id }
        );
    }

    function getPropertyUnitsRes(response) {
        var html =
            '<option value="">--Select Unit--</option><option value="All">--All Units--</option>';
        response.data.forEach(function (opt) {
            if (opt.first_name != null) {
                html +=
                    '<option value="' +
                    opt.id +
                    '">' +
                    opt.unit_name +
                    " (" +
                    opt.first_name +
                    " " +
                    opt.last_name +
                    ")</option>";
            } else {
                html +=
                    '<option value="' +
                    opt.id +
                    '">' +
                    opt.unit_name +
                    "</option>";
            }
        });
        stateSelector
            .closest(".modal")
            .find(".propertyUnitSelectOption")
            .html(html);
    }

    function updateInvoiceCounts() {
        $("#allCount").text(oTable.rows({ search: "applied" }).count());
        $("#paidCount").text(
            paidInvoiceDataTable.rows({ search: "applied" }).count()
        );
        $("#pendingCount").text(
            pendingInvoiceDataTable.rows({ search: "applied" }).count()
        );
        $("#bankPendingCount").text(
            bankPendingInvoiceDataTable.rows({ search: "applied" }).count()
        );
        $("#overdueCount").text(
            overdueInvoiceDataTable.rows({ search: "applied" }).count()
        );
    }

    $(document).ready(function () {
        // Event delegation for dynamically added select fields
        $(document).on("change", ".invoiceItem-invoice_type_id", function () {
            var selectedOption = $(this).find("option:selected");
            var optionText = selectedOption.text();
            var amountLabel = $(this)
                .closest(".multi-field")
                .find(".amount-label");
            var amountField = $(this)
                .closest(".multi-field")
                .find(".invoiceItem-amount");

            if (optionText === "Rent") {
                amountLabel.hide();
                amountField.hide().val(1);
            } else {
                amountLabel.show();
                amountField.show().val("");
            }
        });

        // Handle initial select fields
        $(".invoiceItem-invoice_type_id").trigger("change");
    });

    $(document).on("click", ".reminder", function () {
        let id = $(this).data("id");
        let reminderSelector = $("#reminderModal");
        reminderSelector.modal("show");
        reminderSelector.find("input[name=invoice_id]").val(id);
    });

    $(document).on("click", "#reminderGroup", function () {
        let reminderSelector = $("#reminderGroupModal");
        reminderSelector.modal("show");
    });

    $(document).on("change", "#checkNoticeBoardAllProperty", function () {
        stateSelector = $(this);
        if ($(this).is(":checked")) {
            stateSelector
                .closest(".modal")
                .find("#checkNoticeBoardAllUnit")
                .attr("disabled", true);
            stateSelector
                .closest(".modal")
                .find(".unit_id")
                .attr("disabled", true);
            stateSelector
                .closest(".modal")
                .find(".property_id")
                .attr("disabled", true);
            stateSelector.closest(".modal").find(".unit_id").val("");
            stateSelector.closest(".modal").find(".property_id").val("");
        } else {
            stateSelector
                .closest(".modal")
                .find(".property_id")
                .attr("disabled", false);
            if (
                stateSelector
                    .closest(".modal")
                    .find("#checkNoticeBoardAllUnit")
                    .is(":checked")
            ) {
                stateSelector
                    .closest(".modal")
                    .find("#checkNoticeBoardAllUnit")
                    .attr("disabled", false);
                stateSelector
                    .closest(".modal")
                    .find(".unit_id")
                    .attr("disabled", true);
            } else {
                stateSelector
                    .closest(".modal")
                    .find("#checkNoticeBoardAllUnit")
                    .attr("disabled", false);
                stateSelector
                    .closest(".modal")
                    .find(".unit_id")
                    .attr("disabled", false);
            }
        }
    });

    $(document).on("change", "#checkNoticeBoardAllUnit", function () {
        stateSelector = $(this);
        if ($(this).is(":checked")) {
            stateSelector
                .closest(".modal")
                .find(".unit_id")
                .attr("disabled", true);
            stateSelector.closest(".modal").find(".unit_id").val("");
        } else {
            stateSelector
                .closest(".modal")
                .find(".unit_id")
                .attr("disabled", false);
        }
    });

    var oTable;
    var paidInvoiceDataTable;
    var pendingInvoiceDataTable;
    var bankPendingInvoiceDataTable;
    var overdueInvoiceDataTable;

    $("#search_property").on("change", function () {
        let propertyValue = $(this).val();

        // Apply search filter to all tables
        oTable.search(propertyValue).draw();
        paidInvoiceDataTable.search(propertyValue).draw();
        pendingInvoiceDataTable.search(propertyValue).draw();
        bankPendingInvoiceDataTable.search(propertyValue).draw();
        overdueInvoiceDataTable.search(propertyValue).draw();
    });

    $(document).on(
        "shown.bs.tab",
        'button[data-bs-toggle="tab"]',
        function (event) {
            oTable.ajax.reload();
            paidInvoiceDataTable.ajax.reload();
            pendingInvoiceDataTable.ajax.reload();
            bankPendingInvoiceDataTable.ajax.reload();
            overdueInvoiceDataTable.ajax.reload();
        }
    );
    oTable = $("#allInvoiceDataTable").DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        responsive: true,
        ajax: $("#invoiceIndex").val(),
        order: [1, "desc"],
        ordering: false,
        autoWidth: false,
        drawCallback: function () {
            $(".dataTables_length select").addClass(
                "form-select form-select-sm"
            );
            updateInvoiceCounts();
        },
        language: {
            paginate: {
                previous:
                    '<span class="iconify" data-icon="icons8:angle-left"></span>',
                next: '<span class="iconify" data-icon="icons8:angle-right"></span>',
            },
        },
        columns: [
            { data: "invoice", name: "invoices.invoice_no" },
            { data: "property", name: "properties.name" },
            { data: "due_date", name: "invoices.due_date" },
            { data: "amount", name: "property_units.unit_name" },
            { data: "status" },
            { data: "gateway" },
            { data: "action" },
        ],
    });

    paidInvoiceDataTable = $("#paidInvoiceDataTable").DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        responsive: true,
        ajax: $("#invoicePaid").val(),
        order: [1, "desc"],
        ordering: false,
        autoWidth: false,
        drawCallback: function () {
            $(".dataTables_length select").addClass(
                "form-select form-select-sm"
            );
            updateInvoiceCounts();
        },
        language: {
            paginate: {
                previous:
                    '<span class="iconify" data-icon="icons8:angle-left"></span>',
                next: '<span class="iconify" data-icon="icons8:angle-right"></span>',
            },
        },
        columns: [
            { data: "invoice", name: "invoices.invoice_no" },
            { data: "property", name: "properties.name" },
            { data: "due_date", name: "invoices.due_date" },
            { data: "amount", name: "property_units.unit_name" },
            { data: "status" },
            { data: "action" },
        ],
    });

    pendingInvoiceDataTable = $("#pendingInvoiceDataTable").DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        responsive: true,
        ajax: $("#invoicePending").val(),
        order: [1, "desc"],
        ordering: false,
        autoWidth: false,
        drawCallback: function () {
            $(".dataTables_length select").addClass(
                "form-select form-select-sm"
            );
            updateInvoiceCounts();
        },
        language: {
            paginate: {
                previous:
                    '<span class="iconify" data-icon="icons8:angle-left"></span>',
                next: '<span class="iconify" data-icon="icons8:angle-right"></span>',
            },
        },
        columns: [
            { data: "invoice", name: "invoices.invoice_no" },
            { data: "property", name: "properties.name" },
            { data: "due_date", name: "invoices.due_date" },
            { data: "amount", name: "property_units.unit_name" },
            { data: "status" },
            { data: "action" },
        ],
    });

    bankPendingInvoiceDataTable = $("#bankPendingInvoiceDataTable").DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        responsive: true,
        ajax: $("#invoiceBankPending").val(),
        order: [1, "desc"],
        ordering: false,
        autoWidth: false,
        drawCallback: function () {
            $(".dataTables_length select").addClass(
                "form-select form-select-sm"
            );
            updateInvoiceCounts();
        },
        language: {
            paginate: {
                previous:
                    '<span class="iconify" data-icon="icons8:angle-left"></span>',
                next: '<span class="iconify" data-icon="icons8:angle-right"></span>',
            },
        },
        columns: [
            { data: "invoice", name: "invoices.invoice_no" },
            { data: "property", name: "properties.name" },
            { data: "due_date", name: "invoices.due_date" },
            { data: "amount", name: "property_units.unit_name" },
            { data: "status" },
            { data: "gateway" },
            { data: "action" },
        ],
    });

    overdueInvoiceDataTable = $("#overdueInvoiceDataTable").DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        responsive: true,
        ajax: $("#invoiceOverdue").val(),
        order: [1, "desc"],
        ordering: false,
        autoWidth: false,
        drawCallback: function () {
            $(".dataTables_length select").addClass(
                "form-select form-select-sm"
            );
            updateInvoiceCounts();
        },
        language: {
            paginate: {
                previous:
                    '<span class="iconify" data-icon="icons8:angle-left"></span>',
                next: '<span class="iconify" data-icon="icons8:angle-right"></span>',
            },
        },
        columns: [
            { data: "invoice", name: "invoices.invoice_no" },
            { data: "property", name: "properties.name" },
            { data: "due_date", name: "invoices.due_date" },
            { data: "amount", name: "property_units.unit_name" },
            { data: "status" },
            { data: "action" },
        ],
    });
})(jQuery);
