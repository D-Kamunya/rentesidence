document.addEventListener("DOMContentLoaded", () => {
    var sliders = document.querySelectorAll(".image-slider");
    var scrollAmount = 10; // Adjust scroll amount for each click
    var scrollInterval = 20000; // 5 seconds interval for auto-scrolling

    sliders.forEach((slider) => {
        var isDown = false;
        var startX;
        var scrollLeft;
        var autoScroll;

        // Swipe/drag interactions
        slider.addEventListener("mousedown", (e) => {
            isDown = true;
            slider.classList.add("active");
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
            clearInterval(autoScroll); // Stop auto-scrolling on interaction
        });

        slider.addEventListener("mouseleave", () => {
            isDown = false;
            slider.classList.remove("active");
            startAutoScroll(slider); // Resume auto-scrolling
        });

        slider.addEventListener("mouseup", () => {
            isDown = false;
            slider.classList.remove("active");
            startAutoScroll(slider); // Resume auto-scrolling
        });

        slider.addEventListener("mousemove", (e) => {
            if (!isDown) return;
            e.preventDefault();
            var x = e.pageX - slider.offsetLeft;
            var walk = (x - startX) * 2;
            slider.scrollLeft = scrollLeft - walk;
        });

        // Touch support for swipe on mobile devices
        slider.addEventListener("touchstart", (e) => {
            isDown = true;
            slider.classList.add("active");
            startX = e.touches[0].pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
            clearInterval(autoScroll); // Stop auto-scrolling on interaction
        });

        slider.addEventListener("touchend", () => {
            isDown = false;
            slider.classList.remove("active");
            startAutoScroll(slider); // Resume auto-scrolling
        });

        slider.addEventListener("touchmove", (e) => {
            if (!isDown) return;
            e.preventDefault();
            var x = e.touches[0].pageX - slider.offsetLeft;
            var walk = (x - startX) * 2;
            slider.scrollLeft = scrollLeft - walk;
        });

        // Button interactions
        var scrollLeftButton = slider.previousElementSibling; // Assuming buttons are before and after the slider
        var scrollRightButton = slider.nextElementSibling;

        scrollLeftButton.addEventListener("click", () => {
            slider.scrollBy({
                left: -scrollAmount,
                behavior: "smooth",
            });
            clearInterval(autoScroll); // Stop auto-scrolling on button click
            startAutoScroll(slider); // Resume auto-scrolling
        });

        scrollRightButton.addEventListener("click", () => {
            slider.scrollBy({
                left: scrollAmount,
                behavior: "smooth",
            });
            clearInterval(autoScroll); // Stop auto-scrolling on button click
            startAutoScroll(slider); // Resume auto-scrolling
        });

        // Function to start automatic scrolling
        function startAutoScroll(slider) {
            autoScroll = setInterval(() => {
                // Scroll to the right, and reset to the start if it reaches the end
                if (
                    slider.scrollLeft + slider.clientWidth >=
                    slider.scrollWidth
                ) {
                    slider.scrollLeft = 0; // Go back to the start
                } else {
                    slider.scrollBy({
                        left: scrollAmount,
                        behavior: "smooth",
                    });
                }
            }, scrollInterval);
        }

        // Start auto-scrolling initially
        startAutoScroll(slider);
    });

    var cartButton = document.getElementById("floating-cart-button");
    var cartCounter = document.getElementById("cart-counter");
    var cartItems = []; // Array to hold cart items

    // Event listener for adding items to the cart
    document.querySelectorAll(".add-to-cart-button").forEach((button) => {
        button.addEventListener("click", (e) => {
            // Find the nearest product card container
            var productElement = e.target.closest(".product-card");
    
            // Get product details
            var productId = productElement.querySelector(".product-id").textContent.trim();
            var productName = productElement.querySelector(".product-title").textContent.trim();
            var priceText = productElement.querySelector(".product-price").textContent.trim();
            var productImage = productElement.querySelector("img").src;
    
            // Extract and sanitize quantity
            var quantityInput = productElement.querySelector("#quantity");
            var quantity = quantityInput ? parseInt(quantityInput.value) : 1;
    
            // Sanitize price text
            var numericPrice = priceText
                .replace(/Ksh\./i, '')    // Remove "Ksh." (case-insensitive)
                .replace(/,/g, '')        // Remove commas
                .replace(/[^\d.]/g, '')   // Remove everything except digits and decimal point
                .trim();
    
            var unitPrice = parseFloat(numericPrice);
            var totalPrice = unitPrice * quantity;
            
            // Add the product to the cart
            cartItems.push({
                id: productId,
                name: productName,
                price: totalPrice.toFixed(2),
                image: productImage,
            });

            // Update the counter badge
            cartCounter.textContent = cartItems.length;

            // Optionally, store cart items in local storage for persistence
            localStorage.setItem("cartItems", JSON.stringify(cartItems));

            toastr.success("Item Added to Cart!");
        });
    });

    // Event listener for the floating cart button click
    cartButton.addEventListener("click", (event) => {
        // Retrieve the route URL from the data attribute
        var routeUrl = cartButton.getAttribute("data-url");

        if (cartItems.length === 0) {
            // Display a message if the cart is empty
            showToast("Cart Empty!Add items to cart", "error"); // Show success message

            // Prevent the redirection
            event.preventDefault();
            return false; // Stop further code execution
        }

        // If cart is not empty, proceed to redirection
        window.location.href = routeUrl;
    });

    // On page load, check if there are items in the local storage
    var storedCartItems = JSON.parse(localStorage.getItem("cartItems")) || [];
    if (storedCartItems.length > 0) {
        cartItems = storedCartItems;
        cartCounter.textContent = cartItems.length;
    }

    var floatingCartButton = document.getElementById("floating-cart-button");
    // Check local storage for cart items before redirecting
    floatingCartButton.addEventListener("click", function (event) {});
});
