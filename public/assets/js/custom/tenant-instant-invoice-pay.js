"use strict";

// Shared STK waiting overlay (common.partials.mpesa-stk-waiting).
function showMpesaPreloader(amount) { if (window.mpesaWait) mpesaWait.show(amount ? { amount: amount } : {}); }
function hideMpesaPreloader() { if (window.mpesaWait) mpesaWait.hide(); }

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

