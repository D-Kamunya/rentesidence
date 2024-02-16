"use strict"
$('#bank_id').on('change', function () {
    $('#bankDetails').removeClass('d-none');
    $('#bankDetails p').html($(this).find(':selected').data('details'));
});

$(document).on('click', '.paymentGateway', function () {
    $('#selectGateway').val($(this).data('gateway').replace(/\s+/g, ''))
    $('#selectCurrency').val('');
    commonAjax('GET', $('#getCurrencyByGatewayRoute').val(), getCurrencyRes, getCurrencyRes, { 'id': $(this).find('input').val() });
});

function getCurrencyRes(response) {
    var html = '';
    var invoiceAmount = parseFloat($('#invoiceAmount').val()).toFixed(2);
    Object.entries(response.data).forEach((currency) => {
        let currencyAmount = currency[1].conversion_rate * invoiceAmount;
        html += `<tr>
                    <td>
                        <div class="custom-radiobox gatewayCurrencyAmount">
                            <input type="radio" name="gateway_currency_amount" id="${currency[1].id}" class="" value="${gatewayCurrencyPrice(currencyAmount, currency[1].symbol)}">
                            <label for="${currency[1].id}">${currency[1].currency}</label>
                        </div>
                    </td>
                    <td><h6 class="tenant-invoice-tbl-right-text text-end">${gatewayCurrencyPrice(currencyAmount, currency[1].symbol)}</h6></td>
                </tr>`;
    });
    $('#currencyAppend').html(html);
}

$(document).on('click', '.gatewayCurrencyAmount', function () {
    var getCurrencyAmount = '(' + $(this).find('input').val() + ')';
    $('#gatewayCurrencyAmount').text(getCurrencyAmount)
    $('#selectCurrency').val($(this).text().replace(/\s+/g, ''));
});

$('#payBtn').on('click', function () {
    var gateway = $('#selectGateway').val()
    var currency = $('#selectCurrency').val();
    if (gateway == '') {
        toastr.error('Select Gateway');
        $('#payBtn').attr('type', 'button');
    } else {
        if (currency == '') {
            toastr.error('Select Currency');
            $('#payBtn').attr('type', 'button');
        } else {
            $('#payBtn').attr('type', 'submit');
        }
    }
});

