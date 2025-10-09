
$('#allOwnerDataTable').DataTable({
    processing: true,
    serverSide: true,
    pageLength: 25,
    responsive: true,
    ajax: $('#adminOwnerRoute').val(),
    order: [[1, 'desc']], // Keep ordering enabled properly
    autoWidth: false,
    drawCallback: function () {
        $(".dataTables_length select").addClass("form-select form-select-sm");
    },
    language: {
        paginate: {
            previous: '<span class="iconify" data-icon="icons8:angle-left"></span>',
            next: '<span class="iconify" data-icon="icons8:angle-right"></span>'
        }
    },
    columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'name', name: 'owner_user.first_name', searchable: true },
        { data: 'email', name: 'owner_user.email', searchable: true },
        { data: 'contact_number', name: 'owner_user.contact_number', searchable: true },
        { data: 'affiliate', name: 'affiliate_user.first_name', searchable: true },
        { data: 'status', name: 'owners.status', searchable: true }
    ]
});

