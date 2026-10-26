$(document).on('click', '.reply', function () {
    commonAjax('GET', $('#messageInfoRoute').val(), getDataViewRes, getDataViewRes, { 'id': $(this).data('id') });
});

function getDataViewRes(response) {
    var selector = $('#replyModal');
    selector.find('.id').val(response.id);
    selector.find('.name').text(response.first_name + ' ' + response.last_name);
    selector.find('.email').text(response.email);
    selector.find('.phone').text(response.phone);
    selector.find('.subject').text(response.subject);
    selector.find('.message').text(response.message);
    if (response.reply != null) {
        selector.find('.reply-sec').removeClass('d-none');
        selector.find('.reply').text(response.reply);
    } else {
        selector.find('.reply-sec').addClass('d-none');
    }

    // Attribution: show the referring affiliate when this enquiry came through a referral link.
    if (response.referral_affiliate_name) {
        selector.find('.referral-sec').removeClass('d-none');
        selector.find('.referral').text(response.referral_affiliate_name);
    } else {
        selector.find('.referral-sec').addClass('d-none');
    }

    // Footer actions by state: onboarded → "created"; in the pool → "in marketplace"; otherwise offer
    // one-click Create-owner (trial intent) and/or Send-to-marketplace (any un-referred enquiry).
    var createFooter = document.getElementById('createOwnerFooter');
    var createdFooter = document.getElementById('ownerCreatedFooter');
    var mpFooter = document.getElementById('marketplaceFooter');
    var inMpFooter = document.getElementById('inMarketplaceFooter');
    [createFooter, createdFooter, mpFooter, inMpFooter].forEach(function (f) { if (f) f.style.display = 'none'; });

    if (response.owner_id) {
        if (createdFooter) createdFooter.style.display = '';
    } else if (response.lead_id) {
        if (inMpFooter) inMpFooter.style.display = '';
    } else {
        if (response.intent === 'trial' && createFooter) {
            var base = document.getElementById('createOwnerRoute').value;
            document.getElementById('createOwnerForm').setAttribute('action', base.replace('ID_PLACEHOLDER', response.id));
            createFooter.style.display = '';
        }
        // A referred enquiry is already credited to an affiliate — never pool it.
        if (!response.referral_affiliate_id && mpFooter) {
            var mbase = document.getElementById('sendMarketplaceRoute').value;
            document.getElementById('sendMarketplaceForm').setAttribute('action', mbase.replace('ID_PLACEHOLDER', response.id));
            mpFooter.style.display = '';
        }
    }

    selector.modal('show')
}

$('#messageDataTable').DataTable({
    processing: true,
    serverSide: true,
    pageLength: 25,
    responsive: true,
    ajax: $('#messageIndexRoute').val(),
    order: [1, 'desc'],
    ordering: false,
    autoWidth: false,
    drawCallback: function () {
        $(".dataTables_length select").addClass("form-select form-select-sm");
    },
    language: {
        'paginate': {
            'previous': '<span class="iconify" data-icon="icons8:angle-left"></span>',
            'next': '<span class="iconify" data-icon="icons8:angle-right"></span>'
        }
    },
    columns: [{
        "data": 'DT_RowIndex',
        "name": 'DT_RowIndex',
        orderable: false,
        searchable: false,
    },
    {
        "data": "name",
        "name": "messages.first_name"
    },
    {
        "data": "email",
        "name": "email"
    },
    {
        "data": "phone",
        "name": "phone"
    },
    {
        "data": "status",
    },
    {
        "data": "action",
    },
    ]
});
