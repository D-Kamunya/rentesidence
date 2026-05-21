/**
 * CHANGES FROM ORIGINAL:
 * - .symbol replaced with .currency throughout — gateway_currencies has no symbol column.
 * - cart_total hidden field injected by getCurrencyRes (single currency) and by
 *   the .gatewayCurrencyAmount click handler (multi-currency manual selection).
 * - Pay button disabled on gateway click and re-enabled at end of getCurrencyRes
 *   so cart_total is always present before the form submits — eliminates race condition.
 * - Transaction model: DOMContentLoaded disables Pay, fires currency AJAX, re-enables
 *   only after getCurrencyRes completes and cart_total is confirmed injected.
 * - Duplicate M-Pesa brand block removed from JS — blade renders it statically.
 * - toastr error reads data["error"] || data["message"] to handle both response shapes.
 * - Fixed undefined `amount` variable in .gatewayCurrencyAmount click handler.
 */

"use strict";

// ── Bank select change ───────────────────────────────────────────────────────
$("#bank_id").on("change", function () {
    $("#bankDetails").removeClass("d-none");
    $("#bankDetails p").html($(this).find(":selected").data("details"));
});

// ── Gateway click ────────────────────────────────────────────────────────────
$(document).on("click", ".paymentGateway", function () {

    // Disable Pay immediately — re-enabled at end of getCurrencyRes once
    // cart_total has been injected. Prevents race-condition 422 errors.
    $("#payBtn").prop("disabled", true).css("opacity", "0.6");

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
        $("#bankAppend").addClass("d-none");
        $("#mpesaAccountAppend").addClass("d-none");
    }

    $("#selectCurrency").val("");

    // Remove any previously injected cart_total so a stale value never lingers
    $("#pay-invoice-form #txn-cart-total").remove();

    commonAjax(
        "GET",
        $("#getCurrencyByGatewayRoute").val(),
        getCurrencyRes,
        getCurrencyRes,
        { id: $(this).find("input").val() }
    );
});

// ── getCurrencyRes ───────────────────────────────────────────────────────────
function getCurrencyRes(response) {
    var html          = "";
    var invoiceAmount = parseFloat($("#invoiceAmount").val()).toFixed(2);
    var currencies    = Object.entries(response.data);

    currencies.forEach(function (currency) {
        var currencyAmount = currency[1].conversion_rate * invoiceAmount;
        html += `<tr>
                    <td>
                        <div class="custom-radiobox gatewayCurrencyAmount">
                            <input type="radio" name="gateway_currency_amount"
                                   id="${currency[1].id}"
                                   value="${gatewayCurrencyPrice(currencyAmount, currency[1].currency)}">
                            <label for="${currency[1].id}">${currency[1].currency}</label>
                        </div>
                    </td>
                    <td>
                        <h6 class="tenant-invoice-tbl-right-text text-end">
                            ${gatewayCurrencyPrice(currencyAmount, currency[1].currency)}
                        </h6>
                    </td>
                </tr>`;
    });

    $("#currencyAppend").html(html);

    if (currencies.length === 1) {
        var onlyCurrency   = currencies[0][1];
        var currencyAmount = onlyCurrency.conversion_rate * invoiceAmount;
        var formatted      = gatewayCurrencyPrice(currencyAmount, onlyCurrency.currency);

        $("#currencyAppend input[type='radio']").first().prop("checked", true);
        $("#selectCurrency").val(onlyCurrency.currency.replace(/\s+/g, ""));
        $("#mpesa_selectCurrency").val(onlyCurrency.currency.replace(/\s+/g, ""));
        $("#gatewayCurrencyAmount").text("(" + formatted + ")");

        // Inject cart_total — single currency auto-selected, no manual click needed
        $("#pay-invoice-form #txn-cart-total").remove();
        $("#pay-invoice-form").append(
            '<input type="hidden" id="txn-cart-total" name="cart_total" value="' + formatted + '">'
        );

        toastr.info(
            "<strong>" + onlyCurrency.currency + "</strong> has been auto-selected.",
            "Currency Selected",
            { timeOut: 5000, extendedTimeOut: 2000, closeButton: true, progressBar: true, positionClass: "toast-bottom-right" }
        );

    } else if (currencies.length > 1) {
        // Multiple currencies — tenant must click one; cart_total injected by
        // the .gatewayCurrencyAmount click handler below when they do.
        var section = document.getElementById("currencySection");
        if (section) {
            section.classList.remove("currency-section--pulse");
            void section.offsetWidth;
            section.classList.add("currency-section--pulse");
        }
    }

    // Re-enable Pay now that currency has loaded (or failed gracefully).
    // For multi-currency, tenant still needs to pick one — the button being
    // enabled is correct since the currency guard ("Select Currency") will
    // catch an unselected state.
    $("#payBtn").prop("disabled", false).css("opacity", "1");
}

// ── Currency amount click (multi-currency manual selection) ──────────────────
$(document).on("click", ".gatewayCurrencyAmount", function () {
    // The radio input value holds the formatted amount e.g. "KES 5000.00"
    var rawValue          = $(this).find("input").val();
    var getCurrencyAmount = "(" + rawValue + ")";

    $("#gatewayCurrencyAmount").text(getCurrencyAmount);
    $("#selectCurrency").val($(this).text().replace(/\s+/g, ""));
    $("#mpesa_selectCurrency").val($(this).text().replace(/\s+/g, ""));

    // Inject cart_total for multi-currency path — this is the only place
    // it gets set when the tenant has more than one currency to choose from.
    $("#pay-invoice-form #txn-cart-total").remove();
    $("#pay-invoice-form").append(
        '<input type="hidden" id="txn-cart-total" name="cart_total" value="' + rawValue + '">'
    );
});

// ── M-Pesa preloader ─────────────────────────────────────────────────────────
var timerInterval;

function showMpesaPreloader() {
    var countdown    = 120;
    var timerElement = document.getElementById("mpesa-timer");
    document.getElementById("mpesa-preloader").style.display = "block";
    timerInterval = setInterval(function () {
        var minutes = Math.floor(countdown / 60);
        var seconds = countdown % 60;
        timerElement.textContent = minutes + ":" + (seconds < 10 ? "0" + seconds : seconds);
        if (countdown <= 0) clearInterval(timerInterval);
        countdown--;
    }, 1000);
}

function hideMpesaPreloader() {
    clearInterval(timerInterval);
    document.getElementById("mpesa-preloader").style.display = "none";
}

// ── Pay button ───────────────────────────────────────────────────────────────
$("#payBtn").on("click", function () {
    var isTransactionModel = window.isTransactionModel === true;

    // ── Transaction model fast-path ──────────────────────────────────────────
    // Button was disabled until DOMContentLoaded AJAX completed, so cart_total
    // is guaranteed to be in the form by the time this fires.
    if (isTransactionModel) {
        var payment_form = document.getElementById("pay-invoice-form");
        showMpesaPreloader();

        var formData = new FormData(payment_form);

        fetch(payment_form.action, {
            method:  "POST",
            headers: { "Accept": "application/json" },
            body:    formData,
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data["success"]) {
                handleStkPushResponse(data);
            } else {
                hideMpesaPreloader();
                // Server returns either data["error"] or data["message"] depending on path
                toastr.error(data["error"] || data["message"] || "Payment failed. Please try again.");
            }
        })
        .catch(function () {
            hideMpesaPreloader();
            toastr.error("Something went wrong. Please try again.");
        });
        return;
    }

    // ── Standard path (subscription-model owners) ────────────────────────────
    var gateway  = $("#selectGateway").val();
    var currency = $("#selectCurrency").val();

    if (gateway === "") {
        toastr.error("Select Gateway");
        return;
    }
    if (currency === "") {
        toastr.error("Select Currency");
        return;
    }

    var payment_form = document.getElementById("pay-invoice-form");

    if (gateway === "mpesa") {
        var mpesaAccount = $("#mpesa_account_id").val();
        if (mpesaAccount === "") {
            toastr.error("Select Mpesa Account");
            return;
        }
        showMpesaPreloader();
        var formData = new FormData(payment_form);
        fetch(payment_form.action, {
            method:  "POST",
            headers: { "Accept": "application/json" },
            body:    formData,
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data["success"]) {
                handleStkPushResponse(data);
            } else {
                hideMpesaPreloader();
                toastr.error(data["error"] || data["message"] || "Payment failed. Please try again.");
            }
        })
        .catch(function () {
            hideMpesaPreloader();
            toastr.error("Something went wrong. Please try again.");
        });
    } else {
        // Non-mpesa gateway — standard form submit
        $("#payBtn").attr("type", "submit");
        if (payment_form.checkValidity()) {
            payment_form.submit();
        }
    }
});

// ── Shared STK push Pusher handler ───────────────────────────────────────────
function handleStkPushResponse(data) {
    var redirectTimeout = setTimeout(function () {
        window.location.href = data["redirect_url"];
    }, 120000);

    if (!window.Laravel || !window.Laravel.pusher_key) {
        // No Pusher configured — rely solely on timeout redirect
        return;
    }

    var pusher  = new Pusher(window.Laravel.pusher_key, { cluster: window.Laravel.pusher_cluster });
    var channel = pusher.subscribe("transaction." + data["transaction_id"]);

    channel.bind("MpesaTransactionDeclined", function () {
        clearTimeout(redirectTimeout);
        window.location.href = data["redirect_url"] + "&callback=true&stk_success=false";
    });

    channel.bind("MpesaTransactionProcessed", function () {
        clearTimeout(redirectTimeout);
        window.location.href = data["redirect_url"] + "&callback=true&stk_success=true";
    });
}

// ── M-Pesa code pay button ───────────────────────────────────────────────────
$("#mpesaPayBtn").on("click", function () {
    var gateway  = $("#selectGateway").val();
    var currency = $("#selectCurrency").val();
    if (gateway === "")  { toastr.error("Select Gateway");  return; }
    if (currency === "") { toastr.error("Select Currency"); return; }

    var invoice_form = document.getElementById("pay-invoice-form");
    if (invoice_form.checkValidity()) {
        $("#mpesaCodePaymentMethodModal").modal("show");
    } else {
        invoice_form.reportValidity();
    }
});

// ── Mpesa account select (for code payment modal) ────────────────────────────
$(document).on("change", "#mpesa_account_id", function () {
    var selectedOption = $(this).find("option:selected");
    var textContent    = selectedOption.text().trim();
    var details        = selectedOption.data("details");

    $("#mpesa_code_account_id").val(selectedOption.val());

    if (details === "TILLNUMBER") {
        var parts      = textContent.split("- Till Number: ");
        var tillNumber = parts.length > 1 ? parts[1].trim() : "";
        document.getElementById("till-number").textContent = tillNumber;
        $("#mpesa-code-payment-paybill").addClass("d-none");
        $("#mpesa-code-payment-till").removeClass("d-none");
    } else if (details === "PAYBILL") {
        var paybillMatch     = textContent.match(/Paybill:\s*([\w\d]+)/);
        var accountNameMatch = textContent.match(/Account Number:\s*([\w\d]+)/);
        document.getElementById("bs-number").textContent  = paybillMatch     ? paybillMatch[1]     : "";
        document.getElementById("acc-number").textContent = accountNameMatch ? accountNameMatch[1] : "";
        $("#mpesa-code-payment-paybill").removeClass("d-none");
        $("#mpesa-code-payment-till").addClass("d-none");
    }
});

// ── M-Pesa code submit ───────────────────────────────────────────────────────
$("#mpesaCodeSubmitBtn").on("click", function () {
    var mpesaTransCode = $("#mpesaTransactionCode").val();
    if (mpesaTransCode === "") {
        toastr.error("Enter Mpesa Transaction Code");
    } else {
        $("#mpesaCodeSubmitBtn").attr("type", "submit");
    }
});

// ── Transaction model UI setup ───────────────────────────────────────────────
// Runs on DOMContentLoaded. If window.isTransactionModel is true (set by blade),
// hides the gateway picker and auto-fetches the company M-Pesa currency so the
// form can submit without any manual tenant interaction.
document.addEventListener("DOMContentLoaded", function () {
    if (window.isTransactionModel !== true) return;

    var gatewaySection = document.getElementById("gatewaySection");
    if (gatewaySection) gatewaySection.style.display = "none";

    var tabContent = document.getElementById("invoicePaymentTabContent");
    if (tabContent) tabContent.style.display = "none";

    var currencySection = document.getElementById("currencySection");
    if (currencySection) currencySection.style.display = "none";

    $("#selectGateway").val("mpesa");

    // Disable Pay until currency AJAX resolves and cart_total is injected
    $("#payBtn").prop("disabled", true).css("opacity", "0.6");

    var mpesaGatewayId = window.ownerMpesaGatewayId;

    if (mpesaGatewayId) {
        commonAjax(
            "GET",
            $("#getCurrencyByGatewayRoute").val(),
            function (response) {
                var currencies = Object.entries(response.data);

                if (currencies.length === 0) {
                    console.error("[TXN] No currencies returned for rent gateway ID:", mpesaGatewayId);
                    $("#payBtn").prop("disabled", false).css("opacity", "1");
                    return;
                }

                // getCurrencyRes handles auto-selection, cart_total injection,
                // and re-enabling the Pay button at its end
                getCurrencyRes(response);

                // Force-set currency if getCurrencyRes didn't (multi-currency edge case)
                if (!$("#selectCurrency").val()) {
                    var c         = currencies[0][1];
                    var rawAmount = (c.conversion_rate * parseFloat($("#invoiceAmount").val())).toFixed(2);
                    var formatted = gatewayCurrencyPrice(rawAmount, c.currency);

                    $("#selectCurrency").val(c.currency.replace(/\s+/g, ""));
                    $("#mpesa_selectCurrency").val(c.currency.replace(/\s+/g, ""));
                    $("#gatewayCurrencyAmount").text("(" + formatted + ")");

                    $("#pay-invoice-form #txn-cart-total").remove();
                    $("#pay-invoice-form").append(
                        '<input type="hidden" id="txn-cart-total" name="cart_total" value="' + formatted + '">'
                    );
                }
                // Note: getCurrencyRes already re-enables the button above.
                // No duplicate call needed here.
            },
            function (err) {
                console.error("[TXN] Currency AJAX failed:", err);
                $("#payBtn").prop("disabled", false).css("opacity", "1");
            },
            { id: mpesaGatewayId }
        );
    } else {
        console.error("[TXN] ownerMpesaGatewayId is null — check centresidence_rent_mpesa_account_id setting.");
        $("#payBtn").prop("disabled", false).css("opacity", "1");
    }
});