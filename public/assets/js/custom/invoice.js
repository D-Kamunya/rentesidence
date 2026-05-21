(function ($) {
    "use strict";

    // ── Shared state ──────────────────────────────────────────
    var stateSelector;
    var invoiceTypes = JSON.parse($(".invoiceTypes").val());

    var typesHtml = "";
    Object.entries(invoiceTypes).forEach(function (type) {
        typesHtml += '<option value="' + type[1].id + '">' + type[1].name + "</option>";
    });

    // ── DataTable instances — declared upfront so all handlers
    //    can safely reference them after init completes. ────────
    var oTable                   = null;
    var paidInvoiceDataTable     = null;
    var pendingInvoiceDataTable  = null;
    var bankPendingInvoiceDataTable = null;
    var overdueInvoiceDataTable  = null;

    function allTables() {
        return [oTable, paidInvoiceDataTable, pendingInvoiceDataTable,
                bankPendingInvoiceDataTable, overdueInvoiceDataTable];
    }

    function tablesReady() {
        return allTables().every(function (t) { return t !== null; });
    }

    // ── Count badges ──────────────────────────────────────────
    function updateInvoiceCounts() {
        if (!tablesReady()) return;
        $("#allCount").text(oTable.rows({ search: "applied" }).count());
        $("#paidCount").text(paidInvoiceDataTable.rows({ search: "applied" }).count());
        $("#pendingCount").text(pendingInvoiceDataTable.rows({ search: "applied" }).count());
        $("#bankPendingCount").text(bankPendingInvoiceDataTable.rows({ search: "applied" }).count());
        $("#overdueCount").text(overdueInvoiceDataTable.rows({ search: "applied" }).count());
    }

    // ── Shared DataTable config ───────────────────────────────
    var dtDefaults = {
        processing: true,
        serverSide: true,
        pageLength: 25,
        responsive: true,
        ordering:   false,
        autoWidth:  false,
        drawCallback: function () {
            $(".dataTables_length select").addClass("form-select form-select-sm");
            updateInvoiceCounts();
        },
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next:     "<i class='mdi mdi-chevron-right'>",
            },
        },
    };

    // ── Init all five tables ──────────────────────────────────
    // NOTE: billing-center-datatables.init.js must NOT also init
    // these tables — keep that file empty or remove it entirely.

    function ajaxWithFilters(url) {
        return {
            url: url,
            data: function (d) {
                d.filter_property = $('#search_property').val();
                d.filter_month    = $('#search_month').val();
                d.filter_search   = $('#invoiceSearch').val();
            }
        };
    }

    oTable = $("#allInvoiceDataTable").DataTable($.extend({}, dtDefaults, {
        ajax: ajaxWithFilters($("#invoiceIndex").val()),
        columns: [
            { data: "invoice",  name: "invoices.invoice_no" },
            { data: "property", name: "property" },
            { data: "month",    name: "invoices.month" },
            { data: "due_date", name: "invoices.due_date" },
            { data: "amount",   name: "invoices.amount" },
            { data: "status" },
            { data: "gateway" },
            { data: "action",   orderable: false },
        ],
    }));

    paidInvoiceDataTable = $("#paidInvoiceDataTable").DataTable($.extend({}, dtDefaults, {
        ajax: ajaxWithFilters($("#invoicePaid").val()),
        columns: [
            { data: "invoice",  name: "invoices.invoice_no" },
            { data: "property", name: "property" },
            { data: "month",    name: "invoices.month" },
            { data: "due_date", name: "invoices.due_date" },
            { data: "amount",   name: "invoices.amount" },
            { data: "status" },
            { data: "gateway" },
            { data: "action",   orderable: false },
        ],
    }));

    pendingInvoiceDataTable = $("#pendingInvoiceDataTable").DataTable($.extend({}, dtDefaults, {
        ajax: ajaxWithFilters($("#invoicePending").val()),
        columns: [
            { data: "invoice",  name: "invoices.invoice_no" },
            { data: "property", name: "property" },
            { data: "month",    name: "invoices.month" },
            { data: "due_date", name: "invoices.due_date" },
            { data: "amount",   name: "invoices.amount" },
            { data: "status" },
            { data: "action",   orderable: false },
        ],
    }));

    bankPendingInvoiceDataTable = $("#bankPendingInvoiceDataTable").DataTable($.extend({}, dtDefaults, {
        ajax: ajaxWithFilters($("#invoiceBankPending").val()),
        columns: [
            { data: "invoice",  name: "invoices.invoice_no" },
            { data: "property", name: "property" },
            { data: "month",    name: "invoices.month" },
            { data: "due_date", name: "invoices.due_date" },
            { data: "amount",   name: "invoices.amount" },
            { data: "status" },
            { data: "gateway" },
            { data: "action",   orderable: false },
        ],
    }));

    overdueInvoiceDataTable = $("#overdueInvoiceDataTable").DataTable($.extend({}, dtDefaults, {
        ajax: ajaxWithFilters($("#invoiceOverdue").val()),
        columns: [
            { data: "invoice",  name: "invoices.invoice_no" },
            { data: "property", name: "property" },
            { data: "month",    name: "invoices.month" },
            { data: "due_date", name: "invoices.due_date" },
            { data: "amount",   name: "invoices.amount" },
            { data: "status" },
            { data: "action",   orderable: false },
        ],
    }));

    // ── New invoice ───────────────────────────────────────────
    $("#add").on("click", function () {
        var selector = $("#createNewInvoiceModal");
        selector.find(".is-invalid").removeClass("is-invalid");
        selector.find(".error-message").remove();
        selector.find("form").trigger("reset");
        selector.modal("show");
    });

    // ── Add invoice item row ──────────────────────────────────
    $(document).on("click", ".add-field", function () {
        $(this).closest("form").find(".multi-fields").append(
            '<div class="multi-field mb-3">' +
                '<div class="ow-form-section mb-2">' +
                    '<input type="hidden" name="invoiceItem[id][]" value="">' +
                    '<div class="row">' +
                        '<div class="col-md-6 mb-3">' +
                            '<label class="ow-label">Invoice type</label>' +
                            '<select class="form-select ow-input invoiceItem-invoice_type_id" name="invoiceItem[invoice_type_id][]">' +
                                '<option value="">-- Select type --</option>' +
                                typesHtml +
                            '</select>' +
                        '</div>' +
                        '<div class="col-md-6 mb-3">' +
                            '<label class="ow-label amount-label">Amount</label>' +
                            '<input type="number" name="invoiceItem[amount][]" class="form-control ow-input invoiceItem-amount" placeholder="0.00">' +
                        '</div>' +
                    '</div>' +
                    '<div class="row">' +
                        '<div class="col-md-12">' +
                            '<label class="ow-label">Description</label>' +
                            '<textarea class="form-control ow-input invoiceItem-description" name="invoiceItem[description][]" placeholder="Optional notes…" rows="2"></textarea>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<button type="button" class="remove-field ow-remove-btn">Remove item</button>' +
            '</div>'
        );
    });

    $(document).on("click", ".remove-field", function () {
        $(this).closest(".multi-field").remove();
    });

    // ── Edit invoice ──────────────────────────────────────────
    $(document).on("click", ".edit", function () {
        stateSelector = $(".edit_modal");
        var detailsUrl = $(this).data("detailsurl");
        commonAjax("GET", detailsUrl, getDataEditRes, getDataEditRes);
    });

    function getDataEditRes(response) {
        var selector = $(".edit_modal");
        selector.find(".is-invalid").removeClass("is-invalid");
        selector.find(".error-message").remove();
        selector.find("input[name=id]").val(response.data.invoice.id);
        selector.find("input[name=name]").val(response.data.invoice.name);
        selector.find("select[name=property_id]").val(response.data.invoice.property_id);
        getPropertyUnits(response.data.invoice.property_id);
        setTimeout(function () {
            selector.find("select[name=property_unit_id]").val(response.data.invoice.property_unit_id);
        }, 2000);
        selector.find("select[name=month]").val(response.data.invoice.month);
        selector.find("input[name=due_date]").val(response.data.invoice.due_date);

        var html = "";
        Object.entries(response.data.items).forEach(function (item) {
            var itemTypesHtml = "";
            Object.entries(invoiceTypes).forEach(function (type) {
                var selected = type[1].id == item[1].invoice_type_id ? " selected" : "";
                itemTypesHtml += '<option value="' + type[1].id + '"' + selected + '>' + type[1].name + "</option>";
            });
            html +=
                '<div class="multi-field mb-3">' +
                    '<div class="ow-form-section mb-2">' +
                        '<input type="hidden" name="invoiceItem[id][]" value="' + item[1].id + '">' +
                        '<div class="row">' +
                            '<div class="col-md-6 mb-3">' +
                                '<label class="ow-label">Invoice type</label>' +
                                '<select class="form-select ow-input invoiceItem-invoice_type_id" name="invoiceItem[invoice_type_id][]">' +
                                    '<option value="">-- Select type --</option>' +
                                    itemTypesHtml +
                                '</select>' +
                            '</div>' +
                            '<div class="col-md-6 mb-3">' +
                                '<label class="ow-label amount-label">Amount</label>' +
                                '<input type="number" name="invoiceItem[amount][]" class="form-control ow-input invoiceItem-amount" placeholder="0.00" value="' + item[1].amount + '">' +
                            '</div>' +
                        '</div>' +
                        '<div class="row">' +
                            '<div class="col-md-12">' +
                                '<label class="ow-label">Description</label>' +
                                '<textarea class="form-control ow-input invoiceItem-description" name="invoiceItem[description][]" placeholder="Optional notes…" rows="2">' + (item[1].description || "") + '</textarea>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="remove-field ow-remove-btn">Remove item</button>' +
                '</div>';
        });
        selector.find(".multi-fields").html(html);
        selector.modal("show");
    }

    // ── Pay status change ─────────────────────────────────────
    $(document).on("click", ".payStatus", function () {
        var detailsUrl = $(this).data("detailsurl");
        commonAjax("GET", detailsUrl, getDetailsShowRes, getDetailsShowRes);
    });

    function getDetailsShowRes(response) {
        var selector = $("#payStatusChangeModal");
        selector.find("input[name=id]").val(response.data.invoice.id);
        selector.find("select[name=status]").val(response.data.invoice.status);
        selector.modal("show");
    }

    // ── Invoice preview / view ────────────────────────────────
    window.view = function (url) {
        commonAjax("GET", url, getDetailsViewRes, getDetailsViewRes);
    };

    $(document).on("click", ".view", function () {
        var detailsUrl = $(this).data("detailsurl");
        commonAjax("GET", detailsUrl, getDetailsViewRes, getDetailsViewRes);
    });

    function getDetailsViewRes(response) {
        var selector = $("#invoicePreviewModal");
        var invoicePrintUrl = $("#invoicePrint").val();

        selector.find("#downloadInvoice").attr("href",
            invoicePrintUrl.replace("@", response.data.invoice.id)
        );

        // Header
        selector.find(".invoiceNo").text(response.data.invoice.invoice_no);
        selector.find(".invoicePayDate").text(dateFormat(response.data.invoice.updated_at, "DD MMM YYYY"));
        selector.find(".invoiceMonth").text("Period: " + response.data.invoice.month);

        // Status badge
        var isPaid = response.data.invoice.status == "1";
        var statusHtml = isPaid
            ? '<span class="ipv-status-paid"><svg width="10" height="10" viewBox="0 0 16 16" fill="none"><path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Paid</span>'
            : '<span class="ipv-status-pending"><svg width="10" height="10" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.6"/><path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>Pending</span>';
        selector.find(".invoiceStatus").html(statusHtml);

        // Tenant
        selector.find(".tenantName").text(response.data.tenant.first_name + " " + response.data.tenant.last_name);
        selector.find(".tenantEmail").text(response.data.tenant.email);
        selector.find(".tenantPhone").text(response.data.tenant.contact_number || "");
        selector.find(".propertyName").text(response.data.tenant.property_name);
        selector.find(".unitName").text(response.data.tenant.unit_name);

        // Owner / Pay To
        selector.find(".pay-invoice-address").html(
            "<p class='ipv-name'>" + (response.data.owner.print_name || "") + "</p>" +
            "<span class='ipv-line'>" + (response.data.owner.print_address || "") + "</span>" +
            "<span class='ipv-line'>" + (response.data.owner.print_contact || "") + "</span>"
        );

        // Invoice items
        var itemsHtml = "";
        Object.entries(response.data.items).forEach(function (item) {
            var typeName = "";
            Object.entries(invoiceTypes).forEach(function (type) {
                if (type[1].id == item[1].invoice_type_id) typeName = type[1].name;
            });
            var total = parseFloat(
                parseFloat(item[1].amount) + parseFloat(item[1].tax_amount)
            ).toFixed(2);
            itemsHtml +=
                "<tr>" +
                    "<td style='font-weight:500;color:#111827;'>" + typeName + "</td>" +
                    "<td style='color:#6b7280;'>" + (item[1].description || "") + "</td>" +
                    "<td class='text-end'>" + currencyPrice(item[1].amount) + "</td>" +
                    "<td class='text-end' style='color:#9ca3af;'>" + currencyPrice(item[1].tax_amount) + "</td>" +
                    "<td class='text-end' style='font-weight:500;'>" + currencyPrice(total) + "</td>" +
                "</tr>";
        });
        selector.find("#invoiceItems").html(itemsHtml);
        selector.find(".total").text(currencyPrice(response.data.invoice.amount));

        // Transaction details
        if (response.data.order != null) {
            selector.find(".orderDate").text(dateFormat(response.data.order.created_at, "DD MMM YYYY"));
            selector.find(".orderPaymentTitle").html(
                "<span style='display:inline-block;background:#f3f4f6;color:#374151;font-size:11px;font-weight:500;padding:2px 8px;border-radius:5px;'>" +
                (response.data.order.gatewayTitle || "Cash") + "</span>"
            );
            selector.find(".orderPaymentId").html(
                "<span style='font-family:monospace;font-size:11px;color:#6b7280;'>" +
                (response.data.order.payment_id || "—") + "</span>"
            );
            selector.find(".orderTotal").text(currencyPrice(response.data.order.total));
        } else {
            selector.find(".orderDate").text("—");
            selector.find(".orderPaymentTitle").text("—");
            selector.find(".orderPaymentId").text("—");
            selector.find(".orderTotal").text("—");
        }

        selector.modal("show");
    }

    // ── Property unit loader ──────────────────────────────────
    $(document).on("change", ".property_id", function () {
        stateSelector = $(this);
        if (stateSelector.val() == "All") {
            stateSelector.closest(".modal").find(".propertyUnitSelectOption")
                .html('<option value="All" selected>All tenants</option>');
        } else {
            getPropertyUnits(stateSelector.val());
        }
    });

    function getPropertyUnits(property_id) {
        commonAjax("GET", $("#getPropertyUnitsRoute").val(),
            getPropertyUnitsRes, getPropertyUnitsRes,
            { property_id: property_id, active_tenants: true }
        );
    }

    function getPropertyUnitsRes(response) {
        var html = '<option value="">-- Select unit --</option><option value="All">-- All units --</option>';
        response.data.forEach(function (opt) {
            html += '<option value="' + opt.id + '">' + opt.unit_name +
                (opt.first_name ? " (" + opt.first_name + " " + opt.last_name + ")" : "") +
                "</option>";
        });
        stateSelector.closest(".modal").find(".propertyUnitSelectOption").html(html);
    }

    // ── Invoice type → Rent hides amount field ────────────────
    $(document).on("change", ".invoiceItem-invoice_type_id", function () {
        var text        = $(this).find("option:selected").text();
        var amountLabel = $(this).closest(".multi-field").find(".amount-label");
        var amountField = $(this).closest(".multi-field").find(".invoiceItem-amount");
        if (text === "Rent") {
            amountLabel.hide();
            amountField.hide().val(1);
        } else {
            amountLabel.show();
            amountField.show();
            if (amountField.val() == 1) amountField.val("");
        }
    });

    $(".invoiceItem-invoice_type_id").trigger("change");

    // ── Reminder (single) ─────────────────────────────────────
    $(document).on("click", ".reminder", function () {
        var id = $(this).data("id");
        var sel = $("#reminderModal");
        sel.find("input[name=invoice_id]").val(id);
        sel.modal("show");
    });

    // ── Group reminder ────────────────────────────────────────
    $(document).on("click", "#reminderGroup", function () {
        $("#reminderGroupModal").modal("show");
    });

    // ── All-property checkbox ─────────────────────────────────
    $(document).on("change", "#checkNoticeBoardAllProperty", function () {
        stateSelector = $(this);
        var modal     = stateSelector.closest(".modal");
        var checked   = $(this).is(":checked");
        modal.find(".property_id").attr("disabled", checked).val(checked ? "" : modal.find(".property_id").val());
        modal.find(".unit_id").attr("disabled", checked).val(checked ? "" : modal.find(".unit_id").val());
        modal.find("#checkNoticeBoardAllUnit").attr("disabled", checked);
    });

    // ── All-unit checkbox ─────────────────────────────────────
    $(document).on("change", "#checkNoticeBoardAllUnit", function () {
        var modal   = $(this).closest(".modal");
        var checked = $(this).is(":checked");
        modal.find(".unit_id").attr("disabled", checked).val(checked ? "" : modal.find(".unit_id").val());
    });

    // ── Filter helpers ────────────────────────────────────────
    // All three filters are sent as extra params in every AJAX request
    // (via ajaxWithFilters). Calling .draw() re-fires the AJAX call with
    // the current filter values so the server applies them.
    function updateClearBtn() {
        var hasFilter = $('#search_property').val() ||
                        $('#search_month').val()    ||
                        $('#invoiceSearch').val();
        $('#clearFilters').toggle(!!hasFilter);
    }

    function redrawAll() {
        if (!tablesReady()) return;
        allTables().forEach(function (t) { t.draw(); });
    }

    // ── Property filter ───────────────────────────────────────
    $("#search_property").on("change", function () {
        redrawAll();
        updateClearBtn();
    });

    // ── Month filter ──────────────────────────────────────────
    $("#search_month").on("change", function () {
        redrawAll();
        updateClearBtn();
    });

    // ── Keyword search ────────────────────────────────────────
    $("#invoiceSearch").on("keyup", function () {
        redrawAll();
        updateClearBtn();
    });

    // ── Clear all filters ─────────────────────────────────────
    $("#clearFilters").on("click", function () {
        $('#search_property').val('');
        $('#search_month').val('');
        $('#invoiceSearch').val('');
        redrawAll();
        $(this).hide();
    });

    // ── Clear filters on tab switch ───────────────────────────
    $(document).on("shown.bs.tab", 'button[data-bs-toggle="tab"]', function () {
        $('#search_property').val('');
        $('#search_month').val('');
        $('#invoiceSearch').val('');
        $('#clearFilters').hide();
        redrawAll();
    });

})(jQuery);