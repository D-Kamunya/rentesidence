(function ($) {
    "use strict"; // Start of use strict

    // Preloader Start
    $(window).on("load", function () {
        $("#preloaderInner").fadeOut();
        $("#preloader").delay(350).fadeOut("slow");
        $("body").delay(350);
    });
    // Preloader End

    /*---------------------------------
    Customer Testimonial JS
   -----------------------------------*/
    $(".customer-testimonial-slider").owlCarousel({
        items: 2,
        loop: true,
        autoplay: false,
        autoplayTimeout: 1500,
        margin: 25,
        nav: false,
        dots: true,
        navText: [
            '<span class="iconify" data-icon="bi:arrow-left"></span>',
            '<span class="iconify" data-icon="bi:arrow-right"></span>',
        ],
        smartSpeed: 3000,
        autoplayTimeout: 3000,
        responsive: {
            0: {
                items: 1,
            },
            575: {
                items: 1,
            },
            991: {
                items: 1,
            },
            992: {
                items: 2,
            },
            1199: {
                items: 2,
            },
            1200: {
                items: 2,
            },
        },
    });
})(jQuery); // End of use strict

/*---------------------------------
Hero Section Images
-----------------------------------*/

const stack = document.querySelector('.property-stack');

stack.addEventListener('mousemove', (e) => {
    const { offsetWidth: width, offsetHeight: height } = stack;
    const { offsetX: x, offsetY: y } = e;

    // Calculate rotation values
    const rotateX = ((y / height) - 0.5) * 15; // tilt up/down
    const rotateY = ((x / width) - 0.5) * -15; // tilt left/right

    stack.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
});

stack.addEventListener('mouseleave', () => {
    stack.style.transform = `rotateX(0deg) rotateY(0deg)`; // Reset
});


/*---------------------------------
Testimonial section
-----------------------------------*/

const track = document.querySelector('.testimonial-track');
const cards = document.querySelectorAll('.testimonial-card');

let index = 0;
let cardWidth = cards[0].offsetWidth + 16; // card + margin
let visibleCards = 3;

  const pauseBtn = document.getElementById('pauseScroll');
  const resumeBtn = document.getElementById('resumeScroll');

  pauseBtn.addEventListener('click', () => {
    track.style.animationPlayState = 'paused';
  });
  
  resumeBtn.addEventListener('click', () => {
    track.style.animationPlayState = 'running';
  });

  /*---------------------------------
How it works toggle buttons script
-----------------------------------*/
document.addEventListener("DOMContentLoaded", function () {
    const tenantBtn = document.getElementById("toggleTenant");
    const agentBtn = document.getElementById("toggleAgent");
    const tenantCards = document.querySelectorAll(".tenant-card");
    const agentCards = document.querySelectorAll(".agent-card");

    function showCards(cardsToShow, cardsToHide, activeBtn, inactiveBtn) {
        // Remove active from all buttons first
        activeBtn.classList.add("active");
        inactiveBtn.classList.remove("active");

        // Hide cards smoothly
        cardsToHide.forEach(card => {
            card.classList.remove("show");
            setTimeout(() => card.style.display = "none", 400);
        });

        // Show cards smoothly
        setTimeout(() => {
            cardsToShow.forEach(card => {
                card.style.display = "block";
                setTimeout(() => card.classList.add("show"), 50);
            });
        }, 400);
    }

    tenantBtn.addEventListener("click", () => {
        showCards(tenantCards, agentCards, tenantBtn, agentBtn);
    });

    agentBtn.addEventListener("click", () => {
        showCards(agentCards, tenantCards, agentBtn, tenantBtn);
    });
});
/*--------------------------------------
Hide price on filter if rent is selected
---------------------------------------*/
document.addEventListener("DOMContentLoaded", function () {
    const listingType = document.getElementById("listingType");
    const priceFilter = document.getElementById("priceFilter");

    function togglePriceFilter() {
        if (listingType.value === "sale") {
            priceFilter.style.display = "block";
        } else {
            priceFilter.style.display = "none";
        }
    }

    // Run on page load & whenever changed
    togglePriceFilter();
    listingType.addEventListener("change", togglePriceFilter);
});

