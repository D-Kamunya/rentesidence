"use strict";
$("#bank_id").on("change", function () {
    $("#bankDetails").removeClass("d-none");
    $("#bankDetails p").html($(this).find(":selected").data("details"));
});

$(document).on("click", ".paymentGateway", function () {
    $("#selectGateway").val($(this).data("gateway").replace(/\s+/g, ""));
    if ($("#selectGateway").val() == "mpesa") {
        $("#mpesa_account_id").attr("required", true);
    } else {
        $("#mpesa_account_id").attr("required", false);
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
    var getCurrencyAmount = "(" + $(this).find("input").val() + ")";
    $("#gatewayCurrencyAmount").text(getCurrencyAmount);
    $("#selectCurrency").val($(this).text().replace(/\s+/g, ""));
});

function showMpesaPreloader() {
    document.getElementById("mpesa-preloader").style.display = "block";
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
            $("#payBtn").attr("type", "submit");
            if (payment_form.checkValidity()) {
                if (gateway == "mpesa") {
                    showMpesaPreloader();
                    var countdown = 50; // Set the initial countdown time in seconds

                    // Update the countdown every second
                    var countdownInterval = setInterval(function () {
                        document.getElementById("countdownTimer").textContent =
                            countdown;
                        countdown--;

                        // Hide preloader when countdown reaches 0
                        if (countdown < 0) {
                            document.getElementById(
                                "trans-message"
                            ).style.display = "block";
                            clearInterval(countdownInterval);
                            document.getElementById("countdown").textContent =
                                "Oops!Time is Up!!!!";
                        }
                    }, 1000);
                }
            }
        }
    }
});
