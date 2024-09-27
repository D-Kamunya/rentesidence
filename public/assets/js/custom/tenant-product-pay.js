"use strict";

document.addEventListener("DOMContentLoaded", () => {
    let cartItems = JSON.parse(localStorage.getItem("cartItems")) || [];
    const cartContainer = document.getElementById("cartItems");
    const totalAmountElement = document.getElementById("totalAmount");
    const checkoutAmountElement = document.getElementById("checkoutAmount");
    const cartTotalElement = document.getElementById("cartTotal");

    function groupCartItems(items) {
        // Group cart items by name or another unique identifier
        const groupedItems = {};

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
            const price = parseFloat(item.price);
            const itemTotal = price * item.quantity;
            totalAmount += itemTotal;

            const cartItemCard = document.createElement("div");
            cartItemCard.classList.add("cart-item-card");

            // Rendering the image, name, price, quantity controls, and remove button
            cartItemCard.innerHTML = `
                    <div class="cart-item-details">
                        <img class="cart-item-image" src="${item.image}" alt="${
                item.name
            }" width="50" height="50">
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">$${price.toFixed(
                                2
                            )}</div>
                        </div>
                    </div>
                    <div class="cart-item-quantity">
                        <button class="cart-item-button btn-secondary" onclick="decreaseQuantity(${index})">-</button>
                        <span>${item.quantity}</span>
                        <button class="cart-item-button btn-secondary" onclick="increaseQuantity(${index})">+</button>
                    </div>
                    <div class="cart-item-actions">
                        <div>$${itemTotal.toFixed(2)}</div>
                        <button class="cart-item-button btn-danger" onclick="removeItem(${index})">Remove</button>
                    </div>
                `;

            cartContainer.appendChild(cartItemCard);
        });

        totalAmountElement.textContent = totalAmount.toFixed(2);
        checkoutAmountElement.textContent = totalAmount.toFixed(2);
        cartTotalElement.value = totalAmount.toFixed(2);

        // Update localStorage after grouping
        localStorage.setItem("cartItems", JSON.stringify(cartItems));
    }

    window.increaseQuantity = function (index) {
        cartItems[index].quantity++;
        localStorage.setItem("cartItems", JSON.stringify(cartItems));
        renderCart();
    };

    window.decreaseQuantity = function (index) {
        if (cartItems[index].quantity > 1) {
            cartItems[index].quantity--;
            localStorage.setItem("cartItems", JSON.stringify(cartItems));
            renderCart();
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
        $("#mpesa_selectGateway").val(selectGateway);
        $("#mpesa_selectCurrency").val("");

        $("#mpesaAccountAppend").removeClass("d-none");
        $("#mpesaPayBtn").removeClass("d-none");
        $("#checkoutAmount").text("Via STK");
        $("#mpesaGatewayCurrencyAmount").text("");
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
                            <input type="hidden" id="currencyAmount" name="currencyAmount" value="${currencyAmount}">
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
    var getCurrencyAmount = $(this).find('input[name="gateway_currency_amount"]').val();
    var gateway = $("#selectGateway").val();
    $("#checkoutAmount").text(getCurrencyAmount);
    var currencyAmount = $(this).find('input[name="currencyAmount"]').val();
    
    if (gateway === "mpesa") {
        $("#checkoutAmount").text("Via STK " + getCurrencyAmount);
        $("#mpesaGatewayCurrencyAmount").text(getCurrencyAmount);
        document.getElementById("mpesa-amount").textContent = getCurrencyAmount;
        $("#mpesa_amount").value(currencyAmount);
    } else {
        $("#checkoutAmount").text(getCurrencyAmount);
        $("#cartTotal").value(currencyAmount);
    }
    $("#selectCurrency").val($(this).text().replace(/\s+/g, ""));
    $("#mpesa_selectCurrency").val($(this).text().replace(/\s+/g, ""));
});

function showMpesaPreloader() {
    document.getElementById("mpesa-preloader").style.display = "block";
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
            $("#checkoutBtn").attr("type", "submit");
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
            var payment_form = document.getElementById(
                "pay-products-order-form"
            );
            payment_form.action = "";

            $("#mpesaPayBtn").attr("type", "submit");

            // Prevent default form submission
            payment_form.addEventListener("submit", function (event) {
                event.preventDefault();
            });
            if (payment_form.checkValidity()) {
                $("#paymentMethodModal").modal("hide");
                var selector = $("#mpesaCodePaymentMethodModal");
                selector.modal("show");
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
