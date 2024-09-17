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

    document.getElementById("checkoutBtn").addEventListener("click", () => {
        const selectedGateway = document.querySelector(
            'input[name="gatewayOption"]:checked'
        );
        if (!selectedGateway) {
            alert("Please select a payment method.");
            return;
        }

        document.getElementById("selectGateway").value = selectedGateway.value;
        document.getElementById("checkout-form").submit();
    });

    renderCart();
});
