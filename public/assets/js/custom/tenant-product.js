document.addEventListener("DOMContentLoaded", () => {
    const sliders = document.querySelectorAll(".image-slider");
    const scrollAmount = 10; // Adjust scroll amount for each click
    const scrollInterval = 20000; // 5 seconds interval for auto-scrolling

    sliders.forEach((slider) => {
        let isDown = false;
        let startX;
        let scrollLeft;
        let autoScroll;

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
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 2;
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
            const x = e.touches[0].pageX - slider.offsetLeft;
            const walk = (x - startX) * 2;
            slider.scrollLeft = scrollLeft - walk;
        });

        // Button interactions
        const scrollLeftButton = slider.previousElementSibling; // Assuming buttons are before and after the slider
        const scrollRightButton = slider.nextElementSibling;

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

    const cartButton = document.getElementById("floating-cart-button");
    const cartCounter = document.getElementById("cart-counter");
    let cartItems = []; // Array to hold cart items

    // Event listener for adding items to the cart
    document.querySelectorAll(".add-to-cart-button").forEach((button) => {
        button.addEventListener("click", (e) => {
            // Simulate adding item details to cart (you can customize this)
            const productElement = e.target.closest(".product-card");
            const productId =
                productElement.querySelector(".product-id").textContent;
            const productName =
                productElement.querySelector(".product-title").textContent;
            const productPrice =
                productElement.querySelector(".product-price").textContent; // Assuming price is included
            const productImage = productElement.querySelector("img").src;

            // Add the product to the cart
            cartItems.push({
                id: productId,
                name: productName,
                price: productPrice,
                image: productImage,
            });

            // Update the counter badge
            cartCounter.textContent = cartItems.length;

            // Optionally, store cart items in local storage for persistence
            localStorage.setItem("cartItems", JSON.stringify(cartItems));
        });
    });

    // Event listener for the floating cart button click
    cartButton.addEventListener("click", () => {
        // Retrieve the route URL from the data attribute
        const routeUrl = cartButton.getAttribute("data-url");

        // Redirect to the route URL
        window.location.href = routeUrl;
    });

    // On page load, check if there are items in the local storage
    const storedCartItems = JSON.parse(localStorage.getItem("cartItems")) || [];
    if (storedCartItems.length > 0) {
        cartItems = storedCartItems;
        cartCounter.textContent = cartItems.length;
    }
});