"use strict";
$("#bank_id").on("change", function () {
    $("#bankDetails").removeClass("d-none");
    $("#bankDetails p").html($(this).find(":selected").data("details"));
});

$(document).on("click", ".paymentGateway", function () {
    var selectGateway = $(this).data("gateway").replace(/\s+/g, "");
    $("#selectGateway").val(selectGateway);
    if (selectGateway == "bank") {
        $("#bank_id").val("");
        $("#bankDetails").addClass("d-none");
        $("#bank_slip").val("");
        $("#payBtn").removeClass("d-none");
        $("#bank_slip").attr("required", true);
        $("#bank_id").attr("required", true);
        $("#gatewayCurrencyAmount").text("");
        $("#mpesaGatewayCurrencyAmount").text("");
        $("#mpesaPayBtn").addClass("d-none");
        $("#mpesa_account_id").attr("required", false);
        $("#mpesaAccountAppend").addClass("d-none");
    } else if (selectGateway == "mpesa") {
        $("#mpesa_account_id").val("");
        $("#mpesa_selectGateway").val(selectGateway);
        $("#mpesa_selectCurrency").val("");
        $("#mpesaAccountAppend").removeClass("d-none");
        // $("#mpesaPayBtn").removeClass("d-none");
        // $("#gatewayCurrencyAmount").text("Via STK");
        // $("#mpesaGatewayCurrencyAmount").text("");
        $("#mpesa_account_id").attr("required", true);
        $("#bank_slip").attr("required", false);
        $("#bank_id").attr("required", false);
        $("#bankAppend").addClass("d-none");
    } else {
        $("#bank_slip").attr("required", false);
        $("#payBtn").removeClass("d-none");
        $("#bank_id").attr("required", false);
        $("#mpesa_account_id").attr("required", false);
        $("#gatewayCurrencyAmount").text("");
        $("#mpesaGatewayCurrencyAmount").text("");
        $("#mpesaPayBtn").addClass("d-none");
    }
    $("#selectCurrency").val("");
    commonAjax(
        "GET",
        $("#getCurrencyByGatewayRoute").val(),
        getCurrencyRes,
        getCurrencyRes,
        { id: $(this).find("input").val() }
    );
});

function getCurrencyRes(response) {
    var html = "";
    var invoiceAmount = parseFloat($("#invoiceAmount").val()).toFixed(2);
    Object.entries(response.data).forEach((currency) => {
        let currencyAmount = currency[1].conversion_rate * invoiceAmount;
        html += `<tr>
                    <td>
                        <div class="custom-radiobox gatewayCurrencyAmount">
                            <input type="radio" name="gateway_currency_amount" id="${
                                currency[1].id
                            }" class="" value="${gatewayCurrencyPrice(
            currencyAmount,
            currency[1].symbol
        )}">
                            <label for="${currency[1].id}">${
            currency[1].currency
        }</label>
                        </div>
                    </td>
                    <td><h6 class="tenant-invoice-tbl-right-text text-end">${gatewayCurrencyPrice(
                        currencyAmount,
                        currency[1].symbol
                    )}</h6></td>
                </tr>`;
    });
    $("#currencyAppend").html(html);
}

$(document).on("click", ".gatewayCurrencyAmount", function () {
    var gateway = $("#selectGateway").val();
    var getCurrencyAmount = "(" + $(this).find("input").val() + ")";
    $("#gatewayCurrencyAmount").text(getCurrencyAmount);
    // if (gateway === "mpesa") {
    //     $("#gatewayCurrencyAmount").text("Via STK " + getCurrencyAmount);
    //     $("#mpesaGatewayCurrencyAmount").text(getCurrencyAmount);
    //     document.getElementById("mpesa-amount").textContent = getCurrencyAmount;
    // } else {
    $("#gatewayCurrencyAmount").text(getCurrencyAmount);
    // }
    $("#selectCurrency").val($(this).text().replace(/\s+/g, ""));
    $("#mpesa_selectCurrency").val($(this).text().replace(/\s+/g, ""));
});

function showMpesaPreloader() {
    document.getElementById("mpesa-preloader").style.display = "block";
}

function hideMpesaPreloader() {
    document.getElementById("mpesa-preloader").style.display = "none";
}

$("#payBtn").on("click", function () {
    var gateway = $("#selectGateway").val();
    var currency = $("#selectCurrency").val();
    if (gateway == "") {
        toastr.error("Select Gateway");
        $("#payBtn").attr("type", "button");
    } else {
        if (currency == "") {
            toastr.error("Select Currency");
            $("#payBtn").attr("type", "button");
        } else {
            var payment_form = document.getElementById("pay-invoice-form");
            if (gateway == "mpesa") {
                var mpesaAccount = $("#mpesa_account_id").val();
                if (mpesaAccount == "") {
                    toastr.error("Select Mpesa Account");
                    $("#payBtn").attr("type", "button");
                } else {
                    showMpesaPreloader();
                    var formData = new FormData(payment_form);
                    fetch(payment_form.action, {
                        method: "POST",
                        body: formData,
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data["success"]) {
                                // setTimeout(() => {
                                //     window.location.href = data["data"]; // Redirect to the URL from the response
                                // }, 50000);
                                var pusher = new Pusher(
                                    window.Laravel.pusher_key,
                                    {
                                        cluster: window.Laravel.pusher_cluster,
                                    }
                                );
                                var channel = pusher.subscribe(
                                    "transaction." + data["transaction_id"]
                                );

                                channel.bind(
                                    "MpesaTransactionDeclined",
                                    function (dataa) {
                                        window.location.href =
                                            data["redirect_url"] +
                                            "&callback=true&stk_success=false";
                                    }
                                );
                                channel.bind(
                                    "MpesaTransactionProcessed",
                                    function (dataa) {
                                        window.location.href =
                                            data["redirect_url"] +
                                            "&callback=true&stk_success=true";
                                    }
                                );
                            } else {
                                hideMpesaPreloader();
                                toastr.error(data["error"]);
                            }
                        })
                        .catch((error) => {
                            hideMpesaPreloader();
                            toastr.error(error);
                        });
                }
            } else {
                $("#payBtn").attr("type", "submit");
                if (payment_form_form.checkValidity()) {
                    payment_form_form.submit();
                }
            }
        }
    }
});

$("#mpesaPayBtn").on("click", function () {
    var gateway = $("#selectGateway").val();
    var currency = $("#selectCurrency").val();
    if (gateway == "") {
        toastr.error("Select Gateway");
        $("#mpesaPayBtn").attr("type", "button");
    } else {
        if (currency == "") {
            toastr.error("Select Currency");
            $("#mpesaPayBtn").attr("type", "button");
        } else {
            var invoice_form = document.getElementById("pay-invoice-form");

            if (invoice_form.checkValidity()) {
                var selector = $("#mpesaCodePaymentMethodModal");
                selector.modal("show");
            } else {
                invoice_form.reportValidity();
            }
        }
    }
});

$(document).on("change", "#mpesa_account_id", function () {
    var selectedOption = $(this).find("option:selected");
    var textContent = selectedOption.text().trim();
    var details = selectedOption.data("details");

    $("#mpesa_code_account_id").val(selectedOption.val());
    if (details === "TILLNUMBER") {
        var tillNumber = "";
        var parts = textContent.split("- Till Number: ");
        if (parts.length > 1) {
            tillNumber = parts[1].trim();
        }
        document.getElementById("till-number").textContent = tillNumber;
        $("#mpesa-code-payment-paybill").addClass("d-none");
        $("#mpesa-code-payment-till").removeClass("d-none");
    } else if (details === "PAYBILL") {
        // Parse Paybill and Account Name
        const paybillMatch = textContent.match(/Paybill:\s*([\w\d]+)/);
        const accountNameMatch = textContent.match(/Account Name:\s*([\w\d]+)/);
        // Extract values
        const paybill = paybillMatch ? paybillMatch[1] : "";
        const accountName = accountNameMatch ? accountNameMatch[1] : "";
        document.getElementById("bs-number").textContent = paybill;
        document.getElementById("acc-number").textContent = accountName;
        $("#mpesa-code-payment-paybill").removeClass("d-none");
        $("#mpesa-code-payment-till").addClass("d-none");
    }
});

$("#mpesaCodeSubmitBtn").on("click", function () {
    var mpesaTransCode = $("#mpesaTransactionCode").val();
    if (mpesaTransCode == "") {
        toastr.error("Enter Mpesa Transaction Code");
        $("#mpesaCodeSubmitBtn").attr("type", "button");
    } else {
        $("#mpesaCodeSubmitBtn").attr("type", "submit");
    }
});
