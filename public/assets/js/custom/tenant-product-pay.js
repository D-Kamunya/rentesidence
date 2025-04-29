"use strict";

document.addEventListener("DOMContentLoaded", () => {
    let cartItems = JSON.parse(localStorage.getItem("cartItems")) || [];
    var cartContainer = document.getElementById("cartItems");
    var totalAmountElement = document.getElementById("totalAmount");
    var checkoutAmountElement = document.getElementById("checkoutAmount");
    var cartTotalElement = document.getElementById("cartTotal");
    var mpesaTotalElement = document.getElementById("mpesa_amount");

    function groupCartItems(items) {
        // Group cart items by name or another unique identifier
        var groupedItems = {};

        items.forEach((item) => {
            // Ensure the quantity is initialized correctly
            if (!item.quantity) item.quantity = 1;

            if (groupedItems[item.name]) {
                // If the item is already in the grouped list, increase its quantity
                groupedItems[item.name].quantity += item.quantity;
            } else {
                // Otherwise, add the item to the grouped list
                groupedItems[item.name] = { ...item };
            }
        });

        return Object.values(groupedItems);
    }

    function renderCart() {
        cartItems = groupCartItems(cartItems); // Group the items by name
        cartContainer.innerHTML = "";
        let totalAmount = 0;
        cartItems.forEach((item, index) => {
            // Ensure the price is a number
            var price = parseFloat(item.price);
            var itemTotal = price * item.quantity;
            totalAmount += itemTotal;

            var cartItemCard = document.createElement("div");
            cartItemCard.classList.add("cart-item-card");

            // Rendering the image, name, price, quantity controls, and remove button
            cartItemCard.innerHTML = `
                    <div class="cart-item-details">
                        <img class="cart-item-image" src="${item.image}" alt="${
                item.name
            }" width="50" height="50">
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}</div>
                            <!-- <div class="cart-item-price">KShs.${price.toFixed(2)}</div> -->
                        </div>
                    </div>
                    <div class="cart-item-quantity">
                        <button class="cart-item-button btn-secondary" onclick="decreaseQuantity(${index})">-</button>
                        <span>${item.quantity}</span>
                        <button class="cart-item-button btn-secondary" onclick="increaseQuantity(${index})">+</button>
                    </div>
                    <div class="cart-item-actions">
                        <div>KShs.${itemTotal.toFixed(2)}</div>
                        <button class="cart-item-button btn-danger" onclick="removeItem(${index})">Remove</button>
                    </div>
                `;

            cartContainer.appendChild(cartItemCard);
        });

        totalAmountElement.textContent = totalAmount.toFixed(2);
        checkoutAmountElement.textContent = totalAmount.toFixed(2);
        cartTotalElement.value = totalAmount.toFixed(2);
        mpesaTotalElement.value = totalAmount.toFixed(2);

        // Update localStorage after grouping
        localStorage.setItem("cartItems", JSON.stringify(cartItems));
    }

    window.increaseQuantity = function (index) {
        cartItems[index].quantity++;
        localStorage.setItem("cartItems", JSON.stringify(cartItems));
        renderCart();
        updateCurrencyAmounts();
    };

    window.decreaseQuantity = function (index) {
        if (cartItems[index].quantity > 1) {
            cartItems[index].quantity--;
            localStorage.setItem("cartItems", JSON.stringify(cartItems));
            renderCart();
            updateCurrencyAmounts();
        }
    };

    window.removeItem = function (index) {
        cartItems.splice(index, 1);
        localStorage.setItem("cartItems", JSON.stringify(cartItems));
        renderCart();
    };

    renderCart();
});

$("#bank_id").on("change", function () {
    $("#bankDetails").removeClass("d-none");
    $("#bankDetails p").html($(this).find(":selected").data("details"));
});

$(document).on("click", ".paymentGateway", function (e) {
    e.preventDefault();

    // Remove the active class from all nav-link elements in the #gatewaySection
    $(this)
        .closest("#gatewaySection")
        .find(".paymentGateway")
        .removeClass("active");

    // Add the active class to the clicked element
    $(this).addClass("active");
    var selectGateway = $(this).data("gateway").replace(/\s+/g, "");
    $("#selectGateway").val(selectGateway);
    $("#selectCurrency").val("");

    commonAjax(
        "GET",
        $("#getCurrencyByGatewayRoute").val(),
        getCurrencyRes,
        getCurrencyRes,
        { id: $(this).find("input").val() }
    );
    if (selectGateway == "bank") {
        $("#bank_id").val("");
        $("#bankDetails").addClass("d-none");
        $("#bank_slip").val("");
        $("#bankAppend").removeClass("d-none");
        $("#checkoutBtn").removeClass("d-none");
        $("#bank_slip").attr("required", true);
        $("#bank_id").attr("required", true);
        $("#checkoutAmount").text("");
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
        // $("#checkoutAmount").text("Via STK");
        // $("#mpesaGatewayCurrencyAmount").text("");
        $("#mpesa_account_id").attr("required", true);
        $("#bank_slip").attr("required", false);
        $("#bank_id").attr("required", false);
        $("#bankAppend").addClass("d-none");
    } else {
        $("#bank_slip").attr("required", false);
        $("#checkoutBtn").removeClass("d-none");
        $("#bank_id").attr("required", false);
        $("#mpesa_account_id").attr("required", false);
        $("#checkoutAmount").text("");
        $("#mpesaGatewayCurrencyAmount").text("");
        $("#mpesaPayBtn").addClass("d-none");
        $("#bankAppend").addClass("d-none");
        $("#mpesaAccountAppend").addClass("d-none");
    }
});

function getCurrencyRes(response) {
    var html = "";
    var totalAmount = parseFloat($("#cartTotal").val()).toFixed(2);

    Object.entries(response.data).forEach((currency) => {
        let currencyAmount = currency[1].conversion_rate * totalAmount;
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
                            <input type="hidden" class="conversionRate" name="conversionRate" value="${
                                currency[1].conversion_rate
                            }">
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

// Function to loop through conversionRates and update amounts
function updateCurrencyAmounts() {
    // Get all the conversion rate input elements
    var conversionRates = document.querySelectorAll(".conversionRate");

    var totalAmount = parseFloat($("#cartTotal").val()).toFixed(2);

    // Loop through each conversionRate
    conversionRates.forEach((conversionRateInput) => {
        // Fetch the current conversion rate value
        var conversionRate = parseFloat(conversionRateInput.value);

        // Calculate the new amount based on totalAmount and conversion rate
        var newAmount = (conversionRate * totalAmount).toFixed(2);

        // Find the associated gateway_currency_amount input and update its value
        var gatewayCurrencyAmountInput = conversionRateInput
            .closest(".gatewayCurrencyAmount")
            .querySelector('input[name="gateway_currency_amount"]');
        gatewayCurrencyAmountInput.value = gatewayCurrencyPrice(
            newAmount,
            currencySymbol
        ); // Update the radio value

        // Find the associated h6 element and update its text content
        var amountText = conversionRateInput.closest("tr").querySelector("h6");
        amountText.textContent = gatewayCurrencyPrice(
            newAmount,
            currencySymbol
        ); // Update the text
    });
}

$(document).on("click", ".gatewayCurrencyAmount", function () {
    var getCurrencyAmount = $(this)
        .find('input[name="gateway_currency_amount"]')
        .val();
    var gateway = $("#selectGateway").val();
    $("#checkoutAmount").text(getCurrencyAmount);

    // if (gateway === "mpesa") {
    //     $("#checkoutAmount").text("Via STK " + getCurrencyAmount);
    //     $("#mpesaGatewayCurrencyAmount").text(getCurrencyAmount);
    //     document.getElementById("mpesa-amount").textContent = getCurrencyAmount;
    // } else {
    $("#checkoutAmount").text(getCurrencyAmount);
    // }
    $("#selectCurrency").val($(this).text().replace(/\s+/g, ""));
    $("#mpesa_selectCurrency").val($(this).text().replace(/\s+/g, ""));
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

$("#checkoutBtn").on("click", function () {
    var gateway = $("#selectGateway").val();
    var currency = $("#selectCurrency").val();
    if (gateway == "") {
        toastr.error("Select Gateway");
        $("#checkoutBtn").attr("type", "button");
    } else {
        if (currency == "") {
            toastr.error("Select Currency");
            $("#checkoutBtn").attr("type", "button");
        } else {
            var payment_form = document.getElementById(
                "pay-products-order-form"
            );
            if (gateway == "mpesa") {
                var mpesaAccount = $("#mpesa_account_id").val();
                if (mpesaAccount == "") {
                    toastr.error("Select Mpesa Account");
                    $("#checkoutBtn").attr("type", "button");
                } else {
                    showMpesaPreloader();
                    var formData = new FormData(payment_form);
                    var cartItems =
                        JSON.parse(localStorage.getItem("cartItems")) || [];

                    // Append cart items to FormData
                    cartItems.forEach((item, index) => {
                        formData.append(`products[${index}][id]`, item.id);
                        formData.append(
                            `products[${index}][quantity]`,
                            item.quantity
                        );
                    });
                    fetch(payment_form.action, {
                        method: "POST",
                        body: formData,
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data["success"]) {
                                var redirectTimeout = setTimeout(() => {
                                    window.location.href = data["redirect_url"];
                                }, 120000);
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
                $("#checkoutBtn").attr("type", "submit");
                if (payment_form.checkValidity()) {
                    payment_form.submit();
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
    } else {
        if (currency == "") {
            toastr.error("Select Currency");
            $("#mpesaPayBtn").attr("type", "button");
        } else {
            var payment_form = document.getElementById(
                "pay-products-order-form"
            );

            if (payment_form.checkValidity()) {
                $("#paymentMethodModal").modal("hide");
                var selector = $("#mpesaCodePaymentMethodModal");
                selector.modal("show");
            } else {
                payment_form.reportValidity();
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
        var paybillMatch = textContent.match(/Paybill:\s*([\w\d]+)/);
        var accountNameMatch = textContent.match(/Account Number:\s*([\w\d]+)/);

        // Extract values
        var paybill = paybillMatch ? paybillMatch[1] : "";
        var accountName = accountNameMatch ? accountNameMatch[1] : "";
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

// Select all forms with the name 'checkoutForm'
var forms = document.getElementsByName("checkoutForm");

forms.forEach(function (form) {
    form.addEventListener("submit", function (e) {
        e.preventDefault(); // Prevent default form submission
        // Logic for the individual form submission
        let cartItems = JSON.parse(localStorage.getItem("cartItems")) || [];

        cartItems.forEach((item, index) => {
            let input = document.createElement("input");
            input.type = "hidden";
            input.name = `products[${index}][id]`; // Using array notation for the product id
            input.value = item.id;
            this.appendChild(input);

            let quantityInput = document.createElement("input");
            quantityInput.type = "hidden";
            quantityInput.name = `products[${index}][quantity]`; // Using array notation for the quantity
            quantityInput.value = item.quantity;
            this.appendChild(quantityInput);
        });

        this.submit(); // Now submit the form
    });
});
