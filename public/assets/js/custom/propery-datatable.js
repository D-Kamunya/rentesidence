(function ($) {
    "use strict";
    $("#allPropertiesDataTable").DataTable({
        processing: true,
        serverSide: true,
        pageLength: 1,
        responsive: true,
        ajax: $("#getAllPropertyRoute").val(),
        order: [1, "desc"],
        ordering: false,
        autoWidth: false,
        drawCallback: function () {
            $(".dataTables_length select").addClass(
                "form-select form-select-sm"
            );
        },
        lengthChange: false, // Remove "Show entries" option
        info: false, // Remove "Page x of y" option
        paging: false, // Remove Previous and Next options
        searching: true, // Enable search bar
        language: {
            paginate: {
                previous:
                    '<span class="iconify" data-icon="icons8:angle-left"></span>',
                next: '<span class="iconify" data-icon="icons8:angle-right"></span>',
            },
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "image" },
            { data: "name", name: "name" },
            { data: "address" },
            { data: "rooms" },
            { data: "unit" },
            { data: "available" },
            { data: "action" },
        ],
    });

    $("#allLeasePropertiesDataTable").DataTable({
        processing: true,
        serverSide: true,
        pageLength: 1,
        responsive: true,
        ajax: $("#getAllPropertyRoute").val(),
        order: [1, "desc"],
        ordering: false,
        autoWidth: false,
        drawCallback: function () {
            $(".dataTables_length select").addClass(
                "form-select form-select-sm"
            );
        },
        lengthChange: false, // Remove "Show entries" option
        info: false, // Remove "Page x of y" option
        paging: false, // Remove Previous and Next options
        searching: true, // Enable search bar
        language: {
            paginate: {
                previous:
                    '<span class="iconify" data-icon="icons8:angle-left"></span>',
                next: '<span class="iconify" data-icon="icons8:angle-right"></span>',
            },
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "image" },
            { data: "name", name: "name" },
            { data: "address" },
            { data: "rooms" },
            { data: "unit" },
            { data: "available" },
            { data: "action" },
        ],
    });

    $("#allOwnPropertiesDataTable").DataTable({
        processing: true,
        serverSide: true,
        pageLength: 1,
        responsive: true,
        ajax: $("#getAllPropertyRoute").val(),
        order: [1, "desc"],
        ordering: false,
        autoWidth: false,
        drawCallback: function () {
            $(".dataTables_length select").addClass(
                "form-select form-select-sm"
            );
        },
        lengthChange: false, // Remove "Show entries" option
        info: false, // Remove "Page x of y" option
        paging: false, // Remove Previous and Next options
        searching: true, // Enable search bar
        language: {
            paginate: {
                previous:
                    '<span class="iconify" data-icon="icons8:angle-left"></span>',
                next: '<span class="iconify" data-icon="icons8:angle-right"></span>',
            },
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "image" },
            { data: "name", name: "name" },
            { data: "address" },
            { data: "rooms" },
            { data: "unit" },
            { data: "available" },
            { data: "action" },
        ],
    });
})(jQuery);
