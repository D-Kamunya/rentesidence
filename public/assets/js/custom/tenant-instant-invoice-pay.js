"use strict";

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

$("#instantPayBtn").on("click", function () {
    var number = $("#mpesa_number").val();
    if (number == "") {
        toastr.error("Please enter M-PESA Number");
        $("#instantPayBtn").attr("type", "button");
    } else {
        var payment_form = document.getElementById("instant-invoice-pay-form");
        showMpesaPreloader();
        var formData = new FormData(payment_form);
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
                        clearTimeout(redirectTimeout);
                        window.location.href =
                            data["redirect_url"] +
                            "&callback=true&stk_success=false";
                    }
                );
                channel.bind(
                    "MpesaTransactionProcessed",
                    function (dataa) {
                        clearTimeout(redirectTimeout);
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
});

