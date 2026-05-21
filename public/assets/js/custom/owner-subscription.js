$(document).on("click", "#chooseAPlan", function () {
    commonAjax(
        "GET",
        $("#chooseAPanRoute").val(),
        setPlanModalData,
        setPlanModalData
    );
});

function setPlanModalData(response) {
    var selector = $("#choosePlanModal");
    selector.modal("show");
    selector.find("#planListBlock").html(response.responseText);
}

$(document).on("input", ".quantity", function () {
    var quantity = $(this).val();
    if (parseInt(quantity) < 1) {
        quantity = 1;
    }
    var selector = $(this).closest("form");
    var per_monthly_price = selector
        .find("input[name=per_monthly_price]")
        .val();
    var per_yearly_price = selector.find("input[name=per_yearly_price]").val();
    var totalPerMonthlyPrice = 0;
    var totalPerYearlyPrice = 0;
    if (parseInt(quantity) > 0) {
        totalPerMonthlyPrice = Number(per_monthly_price) * parseInt(quantity);
        totalPerYearlyPrice = Number(per_yearly_price) * parseInt(quantity);
    }
    var perMonthlyPriceDetails =
        currencyPrice(visualNumberFormat(per_monthly_price)) +
        "*" +
        parseInt(quantity) +
        "=" +
        currencyPrice(visualNumberFormat(totalPerMonthlyPrice));
    var perYearlyPriceDetails =
        currencyPrice(visualNumberFormat(per_monthly_price)) +
        "*" +
        parseInt(quantity) +
        "=" +
        currencyPrice(visualNumberFormat(totalPerYearlyPrice));
    selector.find(".per_monthly_price").text(perMonthlyPriceDetails);
    selector.find(".per_yearly_price").text(perYearlyPriceDetails);
});

$(document).on("change", ".quantity", function () {
    var quantity = $(this).val();
    if (parseInt(quantity) < 1) {
        $(this).val(1);
    } else {
        $(this).val(parseInt(quantity));
    }
});

var requestCurrentPlan = $("#requestCurrentPlan").val();
if (requestCurrentPlan == "no") {
    $("#chooseAPlan").trigger("click");
}

$(document).on("change", "#monthly-yearly-button", function () {
    if ($(this).is(":checked") == true) {
        $(document).find(".price-yearly").removeClass("d-none");
        $(document).find(".price-monthly").addClass("d-none");
        $(document).find(".plan_type").val(2);
    } else {
        $(document).find(".price-yearly").addClass("d-none");
        $(document).find(".price-monthly").removeClass("d-none");
        $(document).find(".plan_type").val(1);
    }
});

window.addEventListener("load", function () {
    if ($("#requestPlanId").val()) {
        let response = { responseText: $("#gatewayResponse").val() };
        setPaymentModal(response);
    }
});

function setPaymentModal(response) {
    var selector = $("#paymentMethodModal");
    selector.modal("show");
    $("#choosePlanModal").modal("hide");
    selector.find("#gatewayListBlock").html(response.responseText);
}

$(document).on("click", ".paymentGateway", function (e) {
    e.preventDefault();

    $(this).closest("#gatewaySection").find("button").removeClass("active");
    $(this)
        .closest("#gatewaySection")
        .find(".payment-method-item")
        .removeClass("border border-primary");
    $(this).parent().addClass("border border-primary");
    $(this).addClass("active");
    var selectGateway = $(this).data("gateway").replace(/\s+/g, "");
    $("#selectGateway").val(selectGateway);
    $("#selectCurrency").val("");
    $("#plan_id").val($(this).data("plan_id"));
    $("#duration_type").val($(this).data("duration_type"));
    $("#quantity").val($(this).data("quantity"));

    commonAjax(
        "GET",
        $("#getCurrencyByGatewayRoute").val(),
        getCurrencyRes,
        getCurrencyRes,
        { id: $(this).data("id") }
    );
    if (selectGateway == "bank") {
        $("#bank_id").val("");
        $("#bankDetails").addClass("d-none");
        $("#bank_slip").val("");
        $("#bankAppend").removeClass("d-none");
        $("#payBtn").removeClass("d-none");
        $("#bank_slip").attr("required", true);
        $("#bank_id").attr("required", true);
        $("#gatewayCurrencyAmount").text("");
        $("#mpesaGatewayCurrencyAmount").text("");
        $("#mpesaPayBtn").addClass("d-none");
        $("#mpesa_account_id").attr("required", false);
        $("#mpesaAccountAppend").addClass("d-none");
    } else if (selectGateway == "mpesa") {
        // No account selection needed — platform account is auto-resolved server-side.
        // Just hide bank UI and show the Pay Now button directly.
        $("#bank_slip").attr("required", false);
        $("#bank_id").attr("required", false);
        $("#bankAppend").addClass("d-none");
        $("#mpesaAccountAppend").addClass("d-none");  // hide dropdown — not needed
        $("#mpesa_account_id").attr("required", false);
        $("#payBtn").removeClass("d-none");
        $("#mpesaPayBtn").addClass("d-none");
        $("#gatewayCurrencyAmount").text("");
        $("#mpesaGatewayCurrencyAmount").text("");
        // Mirror hidden mpesa fields so the form submits correctly
        $("#mpesa_selectGateway").val(selectGateway);
        $("#mpesa_selectCurrency").val("");
        $("#mpesa_plan_id").val($(this).data("plan_id"));
        $("#mpesa_duration_type").val($(this).data("duration_type"));
        $("#mpesa_quantity").val($(this).data("quantity"));
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
});

function getCurrencyRes(response) {
    var defaultCurrency = JSON.parse(
        document.getElementById("default-currency").value
    );
    var html = "";
    var planAmount = parseFloat($("#planAmount").val()).toFixed(2);
    var entries = Object.entries(response.data);

    entries.forEach((currency) => {
        let currencyAmount = currency[1].conversion_rate * Number(planAmount);
        html += `<tr>
                    <td>
                        <div class="custom-radiobox gatewayCurrencyAmount">
                            <input type="radio" name="gateway_currency_amount" id="${currency[1].id}" value="${gatewayCurrencyPrice(
                                Number(currencyAmount).toFixed(2),
                                currency[1].symbol
                            )}">
                            <label for="${currency[1].id}">${currency[1].currency}</label>
                        </div>
                    </td>
                    <td><h6 class="tenant-invoice-tbl-right-text text-end">${gatewayCurrencyPrice(
                        Number(planAmount).toFixed(2),
                        defaultCurrency
                    )} * ${currency[1].conversion_rate} = ${gatewayCurrencyPrice(
                        Number(currencyAmount).toFixed(2),
                        currency[1].symbol
                    )}</h6></td>
                </tr>`;
    });

    $("#currencyAppend").html(html);

    // Auto-select: if only one currency, or always select the first one
    if (entries.length > 0) {
        var firstCurrency = entries[0][1];
        var firstRadio = $("#currencyAppend input[type=radio]:first");
        firstRadio.prop("checked", true);

        // Mirror what the manual .gatewayCurrencyAmount click does
        var currencyAmount = firstCurrency.conversion_rate * Number(planAmount);
        var displayAmount = "(" + gatewayCurrencyPrice(
            Number(currencyAmount).toFixed(2),
            firstCurrency.symbol
        ) + ")";

        $("#gatewayCurrencyAmount").text(displayAmount);
        $("#selectCurrency").val(firstCurrency.currency);
        $("#mpesa_selectCurrency").val(firstCurrency.currency);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// FREE & TRANSACTION PLAN — bypass checkout, show confirmation instead
// Replace / augment your existing $(document).on("click", "#subscribeBtn") block
// ─────────────────────────────────────────────────────────────────────────────

$(document).on("click", "#subscribeBtn", function (e) {
    var $form        = $(this).closest("form");
    var planId       = $form.find("input[name=id]").val();
    var planName     = $form.closest(".cpm-card, .cpm-simple-card")
                           .find(".cpm-card-name, .cpm-simple-name")
                           .first().text().trim()
                           // strip any badge text that got included
                           .replace(/\s*(Recommended|Active)\s*/gi, '').trim();

    // Read pricing_model from a data attribute on the submit button
    // (we'll add data-pricing-model to the buttons in plan-list.blade.php below)
    var pricingModel = $(this).data("pricing-model") || "";

    if (pricingModel === "free" || pricingModel === "transaction") {
        e.preventDefault();

        // Pass data to the confirmation partial via globals (it self-executes on render)
        window._cfmPlanId       = planId;
        window._cfmPlanName     = planName;
        window._cfmPricingModel = pricingModel;

        // Fetch and render the confirmation screen inside the same modal
        $.ajax({
            url: $("#confirmFreeRoute").val(),
            method: "GET",
            data: { package_id: planId },
            success: function (html) {
                $("#choosePlanModal").find("#planListBlock").html(html);
            },
            error: function () {
                toastr.error("Could not load confirmation. Please try again.");
            }
        });
    } else {
        // Paid plan — existing behaviour: open gateway modal
        $("#selectGateway").val("");
        $("#selectCurrency").val("");
        $("#plan_id").val(planId);
        $("#payBtn").removeClass("d-none");
        $("#mpesaPayBtn").addClass("d-none");
        $("#gatewayCurrencyAmount").text("");
        $("#bank_id").val("");
        $("#mpesa_account_id").val("");
    }
});

$(document).on("click", ".gatewayCurrencyAmount", function () {
    var gateway = $("#selectGateway").val();
    var getCurrencyAmount = "(" + $(this).find("input").val() + ")";

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

$(document).on("change", "#bank_id", function () {
    $("#bankDetails").removeClass("d-none");
    $("#bankDetails p").html($(this).find(":selected").data("details"));
});

var timerInterval;
function showMpesaPreloader() {
    let countdown = 120; // 2 minutes (in seconds)
    const timerElement = document.getElementById("mpesa-timer");
    document.getElementById("mpesa-preloader").style.display = "block";
    // Start countdown
    timerInterval = setInterval(() => {
        let minutes = Math.floor(countdown / 60);
        let seconds = countdown % 60;
        timerElement.textContent = `${minutes}:${
            seconds < 10 ? "0" + seconds : seconds
        }`;

        if (countdown <= 0) {
            clearInterval(timerInterval);
        }

        countdown--;
    }, 1000);
}

function hideMpesaPreloader() {
    clearInterval(timerInterval);
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
            var subscription_form = document.getElementById(
                "pay-subscription-form"
            );
            if (gateway == "mpesa") {
                showMpesaPreloader();
                var formData = new FormData(subscription_form);
                fetch(subscription_form.action, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "Accept": "application/json",
                    },
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data["success"]) {
                            var redirectTimeout = setTimeout(() => {
                                window.location.href = data["redirect_url"];
                            }, 120000);
                            var pusher = new Pusher(window.Laravel.pusher_key, {
                                cluster: window.Laravel.pusher_cluster,
                            });
                            var channel = pusher.subscribe("transaction." + data["transaction_id"]);
                            channel.bind("MpesaTransactionDeclined", function () {
                                clearTimeout(redirectTimeout);
                                window.location.href = data["redirect_url"] + "&callback=true&stk_success=false";
                            });
                            channel.bind("MpesaTransactionProcessed", function () {
                                clearTimeout(redirectTimeout);
                                window.location.href = data["redirect_url"] + "&callback=true&stk_success=true";
                            });
                        } else {
                            hideMpesaPreloader();
                            toastr.error(data["error"]);
                        }
                    })
                    .catch((error) => {
                        hideMpesaPreloader();
                        toastr.error(error);
                    });
            } else {
                $("#payBtn").attr("type", "submit");
                if (subscription_form.checkValidity()) {
                    subscription_form.submit();
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
            var subscription_form = document.getElementById(
                "pay-subscription-form"
            );

            if (subscription_form.checkValidity()) {
                $("#paymentMethodModal").modal("hide");
                var selector = $("#mpesaCodePaymentMethodModal");
                selector.modal("show");
            } else {
                subscription_form.reportValidity();
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
        // Parse Paybill and Account Number
        const paybillMatch = textContent.match(/Paybill:\s*([\w\d]+)/);
        const accountNameMatch = textContent.match(
            /Account Number:\s*([\w\d]+)/
        );

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