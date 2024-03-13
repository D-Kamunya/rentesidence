$("#add").on("click", function () {
    var selector = $("#addModal");
    selector.find(".is-invalid").removeClass("is-invalid");
    selector.find(".error-message").remove();
    selector.find("form").trigger("reset");
    selector.modal("show");
    showPriceInput(selector, 2);
});

$(document).on(
    "input",
    ".max_unit,input[name=per_monthly_price], input[name=per_yearly_price]",
    function () {
        var selector = $(this).closest("form");
        var max_unit = selector.find("input[name=max_unit").val();
        if (parseInt(max_unit) < 1) {
            quantity = 1;
        }
        var per_monthly_price = selector
            .find("input[name=per_monthly_price]")
            .val();
        var per_yearly_price = selector
            .find("input[name=per_yearly_price]")
            .val();
        var totalMonthlyPrice = 0;
        var totalYearlyPrice = 0;
        if (parseInt(max_unit) > 0) {
            totalMonthlyPrice = Number(per_monthly_price) * parseInt(max_unit);
            totalYearlyPrice = Number(per_yearly_price) * parseInt(max_unit);
        }
        selector.find(".monthly_price").val(totalMonthlyPrice);
        selector.find(".yearly_price").val(totalYearlyPrice);
    }
);

$(document).on("click", ".edit", function () {
    commonAjax(
        "GET",
        $("#packageInfoRoute").val(),
        getDataEditRes,
        getDataEditRes,
        { id: $(this).data("id") }
    );
});

function getDataEditRes(response) {
    var selector = $("#editModal");
    selector.find(".is-invalid").removeClass("is-invalid");
    selector.find(".error-message").remove();
    selector.find("input[name=id]").val(response.data.id);
    selector.find("input[name=name]").val(response.data.name);
    selector.find("select[name=pricing_type]").val(response.data.type);
    selector
        .find("input[name=per_monthly_price]")
        .val(response.data.per_monthly_price);
    selector
        .find("input[name=per_yearly_price]")
        .val(response.data.per_yearly_price);
    showPriceInput(selector, response.data.type);
    selector.find("input[name=max_property]").val(response.data.max_property);
    selector.find("input[name=max_unit]").val(response.data.max_unit);
    selector.find("input[name=max_tenant]").val(response.data.max_tenant);

    if (response.data.customer_limit == -1) {
        selector.find("input[name=customer_limit]").val(0);
        selector.find("select[name=customer_limit_type]").val(2);
    } else {
        selector
            .find("input[name=customer_limit]")
            .val(response.data.customer_limit);
    }

    if (response.data.max_maintainer == -1) {
        selector.find("select[name=maintainer_limit_type]").val(2);
        selector.find("input[name=max_maintainer]").prop("disabled", true);
        selector.find("input[name=max_maintainer]").val(0);
    } else {
        selector.find("select[name=maintainer_limit_type]").val(1);
        selector.find("input[name=max_maintainer]").prop("disabled", false);
        selector
            .find("input[name=max_maintainer]")
            .val(response.data.max_maintainer);
    }

    if (response.data.max_invoice == -1) {
        selector.find("select[name=invoice_limit_type]").val(2);
        selector.find("input[name=max_invoice]").prop("disabled", true);
        selector.find("input[name=max_invoice]").val(0);
    } else {
        selector.find("select[name=invoice_limit_type]").val(1);
        selector.find("input[name=max_invoice]").prop("disabled", false);
        selector.find("input[name=max_invoice]").val(response.data.max_invoice);
    }

    if (response.data.max_auto_invoice == -1) {
        selector.find("select[name=auto_invoice_limit_type]").val(2);
        selector.find("input[name=max_auto_invoice]").prop("disabled", true);
        selector.find("input[name=max_auto_invoice]").val(0);
    } else {
        selector.find("select[name=auto_invoice_limit_type]").val(1);
        selector.find("input[name=max_auto_invoice]").prop("disabled", false);
        selector
            .find("input[name=max_auto_invoice]")
            .val(response.data.max_auto_invoice);
    }

    selector
        .find("select[name=ticket_support]")
        .val(response.data.ticket_support);
    selector
        .find("select[name=notice_support]")
        .val(response.data.notice_support);
    selector.find("select[name=status]").val(response.data.status);
    selector.find("select[name=is_trail]").val(response.data.is_trail);
    selector.find("select[name=is_default]").val(response.data.is_default);
    selector.find("input[name=monthly_price]").val(response.data.monthly_price);
    selector.find("input[name=yearly_price]").val(response.data.yearly_price);
    selector.modal("show");
}

$(document).on("change", ".pricing_type", function () {
    selector = $(this).closest("form");
    pricing_type = selector.find("select[name=pricing_type]").val();
    showPriceInput(selector, pricing_type);
});

function showPriceInput(selector, pricing_type) {
    if (pricing_type == 1) {
        selector.find(".property_type_price").removeClass("d-none");
        selector.find(".unit_type_price").addClass("d-none");
        selector.find(".tenant_type_price").addClass("d-none");
    } else if (pricing_type == 2) {
        selector.find(".property_type_price").addClass("d-none");
        selector.find(".unit_type_price").removeClass("d-none");
        selector.find(".tenant_type_price").addClass("d-none");
    } else if (pricing_type == 3) {
        selector.find(".property_type_price").addClass("d-none");
        selector.find(".unit_type_price").addClass("d-none");
        selector.find(".tenant_type_price").removeClass("d-none");
    }
}

$(document).on(
    "change",
    "select[name=maintainer_limit_type],select[name=invoice_limit_type],select[name=auto_invoice_limit_type]",
    function () {
        var selector = $(this).closest("div");
        if ($(this).val() == 1) {
            selector.find("input").prop("disabled", false);
        } else {
            selector.find("input").val(0);
            selector.find("input").prop("disabled", true);
        }
    }
);

$("#allDataTable").DataTable({
    processing: true,
    serverSide: true,
    pageLength: 25,
    responsive: true,
    ajax: $("#packageIndexRoute").val(),
    order: [1, "desc"],
    ordering: false,
    autoWidth: false,
    drawCallback: function () {
        $(".dataTables_length select").addClass("form-select form-select-sm");
    },
    language: {
        paginate: {
            previous:
                '<span class="iconify" data-icon="icons8:angle-left"></span>',
            next: '<span class="iconify" data-icon="icons8:angle-right"></span>',
        },
    },
    columns: [
        { data: "name", name: "name" },
        { data: "monthly_price", name: "monthly_price" },
        { data: "yearly_price", name: "yearly_price" },
        { data: "per_monthly_price", name: "per_monthly_price" },
        { data: "per_yearly_price", name: "per_yearly_price" },
        { data: "status" },
        { data: "trail" },
        { data: "action" },
    ],
});
