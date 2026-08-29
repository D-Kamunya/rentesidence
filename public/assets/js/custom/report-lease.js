var dt = $('#leaseReportDataTable').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: $('#leaseReportRoute').val(),
    order: [1, 'desc'],
    ordering: false,
    autoWidth: false,
    lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, 'All'],
    ],
    drawCallback: function () {
        $(".dataTables_length select").addClass("form-select form-select-sm");
    },
    language: {
        'paginate': {
            'previous': '<span class="iconify" data-icon="icons8:angle-left"></span>',
            'next': '<span class="iconify" data-icon="icons8:angle-right"></span>'
        }
    },
    dom: '<"row"<"col-sm-4"l><"col-sm-4"B><"col-sm-4"f>>tr<"bottom"<"row"<"col-sm-6"i><"col-sm-6"p>>><"clear">',
    buttons: [{
        extend: 'excel',
            action: function (e, dt, node, config) {
                var base = $("#reportExportRoute").val();
                if (!base) { return; }
                var params = $.param({
                    format: "csv",
                    property_id: $("#property_id").val() || "",
                    unit_id: $("#unit_id").val() || "",
                    start_date: $("#start_date").val() || "",
                    end_date: $("#end_date").val() || "",
                });
                window.open(base + (base.indexOf("?") > -1 ? "&" : "?") + params, "_blank");
            },
        className: 'theme-btn theme-button1 default-hover-btn'
    },
    {
        extend: 'pdf',
            action: function (e, dt, node, config) {
                var base = $("#reportExportRoute").val();
                if (!base) { return; }
                var params = $.param({
                    property_id: $("#property_id").val() || "",
                    unit_id: $("#unit_id").val() || "",
                    start_date: $("#start_date").val() || "",
                    end_date: $("#end_date").val() || "",
                });
                window.open(base + (base.indexOf("?") > -1 ? "&" : "?") + params, "_blank");
            },
        className: 'theme-btn theme-button1 default-hover-btn',
        customize: function (doc) {
            // Landscape + full-width, wrapping columns so no data is clipped off
            // the right edge (the default portrait + auto widths truncates wide tables).
            doc.pageOrientation = "landscape";
            doc.defaultStyle.fontSize = 8;
            var table = doc.content[doc.content.length - 1];
            if (table && table.table && table.table.body[0]) {
                table.table.widths = Array(table.table.body[0].length).fill("*");
            }
        }
    },
    {
        extend: 'copy',
        className: 'theme-btn theme-button1 default-hover-btn'
    }
    ],
    columnDefs: [
        { className: "text-center", targets: [1, 2, 3, 4] },
        { className: "text-end", targets: [5] }
    ],
    columns: [{
        "data": 'DT_RowIndex',
        "name": 'DT_RowIndex',
        orderable: false,
        searchable: false,
    },
    {
        "data": "name",
        "name": "users.first_name"
    },
    {
        "data": "property",
        "name": "properties.name"
    },
    {
        "data": "unit",
        "name": "property_units.unit_name"
    },
    {
        "data": "start_date",
        "name": "tenants.lease_start_date"
    },
    {
        "data": "end_date",
        "name": "tenants.lease_end_date"
    }
    ]
});

