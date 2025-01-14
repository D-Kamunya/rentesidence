var getCurrencySymbol = $("#getCurrencySymbol").val();
var allCurrency = JSON.parse($("#allCurrency").val());

$(document).on("click", ".edit", function (e) {
    commonAjax(
        "GET",
        $("#getInfoRoute").val(),
        getDataEditRes,
        getDataEditRes,
        { id: $(this).data("id") }
    );
});

$(document).on("click", ".update", function (e) {
    var slug = $("#editModal").find(".slug").val();
    if (slug == "mpesa") {
        var allMpesaAccountsType = $(".mpesa-account-type")
            .map(function () {
                return $(this).val();
            })
            .get();
        for (var i = 0; i < allMpesaAccountsType.length; i++) {
            var accountType = allMpesaAccountsType[i];
            var passkeyInput = $(".passkey").eq(i);
            passkeyInput.prop("required", true);
            // Check if the current account type is PAYBILL
            if (accountType === "PAYBILL") {
                // Find the corresponding elements based on the index
                var paybillInput = $(".paybill-number").eq(i);
                var accountNameInput = $(".account-name").eq(i);

                // Set the required attribute
                paybillInput.prop("required", true);
                accountNameInput.prop("required", true);
            } else if (accountType === "TILLNUMBER") {
                // Find the corresponding elements based on the index
                var tillInput = $(".till-number").eq(i);

                // Set the required attribute
                tillInput.prop("required", true);
            }
        }
    }
});

function getDataEditRes(response) {
    const selector = $("#editModal");
    selector.find(".gateway-input").removeClass("d-none");
    selector.modal("show");
    selector.find(".is-invalid").removeClass("is-invalid");
    selector.find(".error-message").remove();
    $("#id").val(response.data.gateway.id);
    selector.find(".image").attr("src", response.data.image);
    selector.find(".title").val(response.data.gateway.title);
    selector.find(".slug").val(response.data.gateway.slug);
    selector.find("select[name=status]").val(response.data.gateway.status);
    selector.find("select[name=mode]").val(response.data.gateway.mode);
    selector.find("input[name=key]").val(response.data.gateway.key);
    var gatewaySettings = JSON.parse($("#gatewaySettings").val());
    let currentGateway = gatewaySettings[response.data.gateway.slug];

    if (typeof currentGateway == "undefined") {
        currentGateway = [];
    } else {
        selector.find(".gateway-input").addClass("d-none");
    }

    currentGateway.forEach((option) => {
        if (option.name == "url" && option.is_show == 1) {
            selector
                .find("input[name=url]")
                .parent()
                .find(".label-text-title")
                .text(option.label);
            $("#gateway-url").removeClass("d-none");
        } else if (option.name == "key" && option.is_show == 1) {
            selector
                .find("input[name=key]")
                .parent()
                .find(".label-text-title")
                .text(option.label);
            $("#gateway-key").removeClass("d-none");
        } else if (option.name == "secret" && option.is_show == 1) {
            selector
                .find("input[name=secret]")
                .parent()
                .find(".label-text-title")
                .text(option.label);
            $("#gateway-secret").removeClass("d-none");
        }
    });

    selector.find("input[name=secret]").val(response.data.gateway.secret);
    selector.find("input[name=url]").val(response.data.gateway.url);

    if (response.data.gateway.slug == "bank") {
        selector.find(".url-div").hide();
        selector.find(".key-secret-div").hide();
        selector.find(".mpesa-div").hide();
        selector.find(".bank-div").show();
        var banks = response.data.banks;
        var bankHtml = "";
        if (banks.length > 0) {
            Object.entries(banks).map(function (bank) {
                var isSelected = "";
                if (bank[1].status == 1) {
                    isSelected = "selected";
                } else {
                    isSelected = "";
                }

                bankHtml += `<div class="multi-bank bg-white radius-4 theme-border p-20 pb-0 mb-25">
                                <div class="row">
                                    <div class="col-6 mb-20">
                                        <input type="hidden" name="bank[id][]" value="${
                                            bank[1].id
                                        }">
                                        <label for="name" class="label-text-title color-heading font-medium mb-2">Bank Name</label>
                                        <input type="text" name="bank[name][]" class="form-control bank-name" id="name" placeholder="Bank Name" value="${
                                            bank[1].name
                                        }">
                                    </div>
                                    <div class="col-6 mb-20">
                                        <label for="name" class="label-text-title color-heading font-medium mb-2">Status</label>
                                        <select name="bank[status][]" class="form-control bank-status" id="status">
                                            <option value="1" ${
                                                bank[1].status == 1
                                                    ? "selected"
                                                    : ""
                                            }>Active</option>
                                            <option value="0" ${
                                                bank[1].status == 0
                                                    ? "selected"
                                                    : ""
                                            }>Deactive</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-20">
                                        <label for="name" class="label-text-title color-heading font-medium mb-2">Bank Details</label>
                                        <textarea name="bank[details][]" id="bank_details" class="form-control">${
                                            bank[1].details
                                        }</textarea>
                                    </div>
                                    <div class="row mb-20">
                                        <div class="col-12 text-end"><button type="button" class="red-color remove-bank" title="Remove">Remove</button></div>
                                    </div>
                                </div>
                            </div>`;
            });
        }

        $(".bank-div-append").html(bankHtml);
    } else if (response.data.gateway.slug == "mpesa") {
        selector.find(".url-div").hide();
        selector.find(".key-secret-div").hide();
        selector.find(".bank-div").hide();
        selector.find(".mpesa-div").show();

        var mpesaAccounts = response.data.mpesaAccounts;
        var mpesaHtml = "";
        if (mpesaAccounts.length > 0) {
            mpesaHtml += ``;

            Object.entries(mpesaAccounts).map(function (mpesaAccount) {
                var isSelected = "";
                if (mpesaAccount[1].status == 1) {
                    isSelected = "selected";
                } else {
                    isSelected = "";
                }
                mpesaHtml += `<div class="multi-mpesa-accounts bg-white radius-4 theme-border p-20 pb-0 mb-25">
                            <div class="row mb-20">
                                <div class="col-6 mb-20">
                                    <input type="hidden" name="mpesaAccount[id][]" value="${
                                        mpesaAccount[1].id
                                    }">
                                    <label for="name" class="label-text-title color-heading font-medium mb-2">Account Type</label>
                                    <select name="mpesaAccount[account_type][]" class="form-control mpesa-account-type" id="account-type" readonly>
                                        <option value="${
                                            mpesaAccount[1].account_type
                                        }" selected>${
                    mpesaAccount[1].account_type
                }</option>
                                    </select>
                                </div>
                                <div class="col-6 mb-20">
                                    <label for="name" class="label-text-title color-heading font-medium mb-2">Status</label>
                                    <select name="mpesaAccount[status][]" class="form-control mpesa-account-status" id="mpesa-account-status">
                                        <option value="1" ${
                                            mpesaAccount[1].status == 1
                                                ? "selected"
                                                : ""
                                        }>Active</option>
                                        <option value="0" ${
                                            mpesaAccount[1].status == 0
                                                ? "selected"
                                                : ""
                                        }>Deactive</option>
                                    </select>
                                </div>`;

                // Check the account type and display corresponding fields
                if (mpesaAccount[1].account_type === "PAYBILL") {
                    mpesaHtml += `<div class="col-6 mb-20 paybill-fields">
                                                    <label for="name" class="label-text-title color-heading font-medium mb-2">Paybill Number</label>
                                                    <input type="text" name="mpesaAccount[paybill_number][]" class="form-control paybill-number" id="paybill-number" placeholder="Paybill Number" value="${mpesaAccount[1].paybill}">
                                                    </div>
                                                <div class="col-6 mb-20 paybill-fields">
                                                    <label for="name" class="label-text-title color-heading font-medium mb-2">Account Name</label>
                                                    <input type="text" name="mpesaAccount[account_name][]" class="form-control account-name" id="account-name" placeholder="Account Name" value="${mpesaAccount[1].account_name}">
                                                </div>
                                                <input type="hidden" name="mpesaAccount[till_number][]" class="form-control till-number" id="till-number" placeholder="Till Number" value="${mpesaAccount[1].till_number}">
                                                `;
                } else if (mpesaAccount[1].account_type === "TILLNUMBER") {
                    mpesaHtml += `<div class="col-6 mb-20 till-number-fields">
                                                    <label for="name" class="label-text-title color-heading font-medium mb-2">Till Number</label>
                                                    <input type="text" name="mpesaAccount[till_number][]" class="form-control till-number" id="till-number" placeholder="Till Number" value="${mpesaAccount[1].till_number}">
                                                </div>
                                                    
                                                <input type="hidden" name="mpesaAccount[paybill_number][]" class="form-control paybill-number" id="paybill-number" placeholder="Paybill Number" value="${mpesaAccount[1].paybill}">
                                                <input type="hidden" name="mpesaAccount[account_name][]" class="form-control account-name" id="account-name" placeholder="Account Name" value="${mpesaAccount[1].account_name}">
                                                `;
                }

                mpesaHtml += `<div class="row mb-20">
                                <div class="col-12 text-end"><button type="button" class="red-color remove-mpesa-account" title="Remove">Remove</button></div>
                            </div>
                    </div>
                </div>`;
            });
        }

        $(".mpesa-div-append").html(mpesaHtml);
    } else {
        if (response.data.gateway.slug == "cash") {
            selector.find(".url-div").hide();
            selector.find(".key-secret-div").hide();
            selector.find(".bank-div").hide();
            selector.find(".mpesa-div").hide();
        } else {
            selector.find(".url-div").show();
            selector.find(".key-secret-div").show();
            selector.find(".bank-div").hide();
            selector.find(".mpesa-div").hide();
        }
    }
    var html = "";
    response.data.currencies.map(function (data) {
        html +=
            '<div class="input-group mb-3 currency-conversation-rate">' +
            '<select name="currency[]" class="form-control currency" required>';
        Object.entries(allCurrency).forEach((currency) => {
            if (currency[0] == data.currency) {
                html +=
                    '<option value="' +
                    currency[0] +
                    '" selected>' +
                    currency[1] +
                    "</option>";
            } else {
                html +=
                    '<option value="' +
                    currency[0] +
                    '">' +
                    currency[1] +
                    "</option>";
            }
        });
        html +=
            "</select>" +
            '<span class="input-group-text">1  ' +
            getCurrencySymbol +
            " = </span>" +
            '<input type="number" step="any" min="0" name="conversion_rate[]" value="' +
            data.conversion_rate +
            '" class="form-control" required>' +
            '<input type="hidden" step="any" min="0" name="currency_id[]" value="' +
            data.id +
            '" class="form-control" required>' +
            '<span class="input-group-text append_currency">' +
            data.currency +
            "</span>" +
            '<button type="button" class="removedItem font-24 ms-3 text-danger mr-5" title="Remove"><i class="ri-delete-bin-6-line"></i></button>' +
            "</div>";
    });
    $("#currencyConversionRateSection").html(html);
}

$(document).on("change", ".mpesa-account-type", function (e) {
    var selectedType = $(this).val();
    // Show/hide fields based on the selected account_type
    if (selectedType === "PAYBILL") {
        $(".paybill-fields").show();
        $(".till-number-fields").hide();
    } else if (selectedType === "TILLNUMBER") {
        $(".paybill-fields").hide();
        $(".till-number-fields").show();
    }
});

$(".add-currency").on("click", function (e) {
    var html = "";
    html +=
        '<div class="input-group mb-3 currency-conversation-rate">' +
        '<select name="currency[]" class="form-control currency" required>';
    Object.entries(allCurrency).forEach((currency) => {
        html +=
            '<option value="' + currency[0] + '">' + currency[1] + "</option>";
    });
    html +=
        "</select>" +
        '<span class="input-group-text">1  ' +
        getCurrencySymbol +
        " = </span>" +
        '<input type="number" step="any" min="0" name="conversion_rate[]" value="" class="form-control" required>' +
        '<input type="hidden" step="any" min="0" name="currency_id[]" value="" class="form-control" required>' +
        '<span class="input-group-text append_currency"></span>' +
        '<button type="button" class="removedItem font-24 ms-3 text-danger mr-5" title="Remove"><i class="ri-delete-bin-6-line"></i></button>' +
        "</div>";
    $("#currencyConversionRateSection").append(html);
    $(".currency").trigger("change");
});

$(document).on("click", ".removedItem", function () {
    $(this).closest(".currency-conversation-rate").remove();
});

$(document).on("change", ".currency", function () {
    $(this)
        .closest(".currency-conversation-rate")
        .find(".append_currency")
        .text($(this).val());
});

// Bank
$(".add-bank").on("click", function () {
    $(".bank-div-append").append(addBank());
});

// Mpesa
$(".add-mpesa").on("click", function () {
    $(".mpesa-div-append").append(addMpesaAccount());
});

$(document).on("click", ".remove-bank", function () {
    $(this).closest(".multi-bank").remove();
});

$(document).on("click", ".remove-mpesa-account", function () {
    $(this).closest(".multi-mpesa-accounts").remove();
});

function addBank() {
    return `<div class="multi-bank bg-white radius-4 theme-border p-20 pb-0 mb-25">
                <div class="row">
                    <div class="col-6 mb-20">
                        <input type="hidden" name="bank[id][]" value="">
                        <label for="name" class="label-text-title color-heading font-medium mb-2">Bank Name</label>
                        <input type="text" name="bank[name][]" class="form-control bank-name" id="name" placeholder="Bank Name" value="">
                    </div>
                    <div class="col-6 mb-20">
                        <label for="name" class="label-text-title color-heading font-medium mb-2">Status</label>
                        <select name="bank[status][]" class="form-control bank-status" id="status">
                            <option value="1">Active</option>
                            <option value="0">Deactivate</option>
                        </select>
                    </div>
                    <div class="col-12 mb-20">
                        <label for="name" class="label-text-title color-heading font-medium mb-2">Bank Details</label>
                        <textarea name="bank[details][]" id="bank_details" class="form-control"></textarea>
                    </div>
                    <div class="row mb-20">
                        <div class="col-12 text-end"><button type="button" class="red-color remove-bank" title="Remove">Remove</button></div>
                    </div>
                </div>
            </div>`;
}

function addMpesaAccount() {
    return `<div class="multi-mpesa-accounts bg-white radius-4 theme-border p-20 pb-0 mb-25">
                <div class="row mb-20">
                    <div class="col-6 mb-20">
                        <input type="hidden" name="mpesaAccount[id][]" value="">
                        <label for="name" class="label-text-title color-heading font-medium mb-2">Account Type</label>
                        <select name="mpesaAccount[account_type][]" class="form-control mpesa-account-type" id="account-type">
                            <option value="PAYBILL">PAYBILL</option>
                            <option value="TILLNUMBER">TILL NUMBER</option>
                        </select>
                    </div>
                    <div class="col-6 mb-20">
                        <label for="name" class="label-text-title color-heading font-medium mb-2">Status</label>
                        <select name="mpesaAccount[status][]" class="form-control mpesa-account-status" id="mpesa-account-status">
                            <option value="1" >Active</option>
                            <option value="0" >Deactive</option>
                        </select>
                    </div>
                    <div class="col-6 mb-20 paybill-fields">
                        <label for="name" class="label-text-title color-heading font-medium mb-2">Paybill Number</label>
                            <input type="text" name="mpesaAccount[paybill_number][]" class="form-control paybill-number" id="paybill-number" placeholder="Paybill Number" value="">
                    </div>
                    <div class="col-6 mb-20 paybill-fields">
                        <label for="name" class="label-text-title color-heading font-medium mb-2">Account Name</label>
                            <input type="text" name="mpesaAccount[account_name][]" class="form-control account-name" id="account-name" placeholder="Account Name" value="">
                    </div>
                    <div class="col-6 mb-20 till-number-fields" style="display: none;">
                        <label for="name" class="label-text-title color-heading font-medium mb-2">Till Number</label>
                                <input type="text" name="mpesaAccount[till_number][]" class="form-control till-number" id="till-number" placeholder="Till Number" value="">
                    </div>
                    <div class="row mb-20">
                        <div class="col-12 text-end"><button type="button" class="red-color remove-mpesa-account" title="Remove">Remove</button></div>
                    </div>
                </div>
            </div>`;
}
