<!-- DataTables -->
<link href="{{ asset('/') }}assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<link href="{{ asset('/') }}assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet">
<link href="{{ asset('/') }}assets/libs/datatables.net-select-bs4/css/select.bootstrap4.min.css" rel="stylesheet">

<!-- Responsive datatable examples -->
<link href="{{ asset('/') }}assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet">

{{-- Design-system uplift applied to EVERY DataTable app-wide (loads after the lib CSS
     so it wins). Pure CSS on stable DataTables classes — no JS/markup change. --}}
<style>
    .dataTables_wrapper { font-size: 13px; color: #374151; }
    table.dataTable thead th {
        background: #fafafa; color: #6b7280 !important; font-size: 10px; font-weight: 600;
        text-transform: uppercase; letter-spacing: .06em; padding: 12px 14px;
        border-bottom: 0.5px solid #e5e7eb !important; border-top: none !important; white-space: nowrap;
    }
    table.dataTable tbody td { font-size: 13px; color: #374151; padding: 12px 14px; border-top: 0.5px solid #f3f4f6 !important; vertical-align: middle; }
    table.dataTable.no-footer { border-bottom: 0.5px solid #e5e7eb !important; }
    table.dataTable tbody tr:hover > * { background: #f7f9fc !important; }

    /* Length + search controls */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 0.5px solid #e5e7eb !important; border-radius: 8px; padding: 6px 10px; font-size: 13px; color: #374151; outline: none; background: #fff;
    }
    .dataTables_wrapper .dataTables_filter input:focus,
    .dataTables_wrapper .dataTables_length select:focus { border-color: #185FA5 !important; box-shadow: 0 0 0 3px rgba(24,95,165,.1); }
    .dataTables_wrapper .dataTables_info { color: #9ca3af; font-size: 12.5px; }

    /* Pagination */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 7px !important; border: 0.5px solid #e5e7eb !important; padding: 5px 11px !important;
        margin: 0 2px !important; color: #374151 !important; background: #fff !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #185FA5 !important; border-color: #185FA5 !important; color: #fff !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #E6F1FB !important; border-color: #B5D4F4 !important; color: #0C447C !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled { color: #d1d5db !important; }

    /* Export/copy buttons (Excel / PDF / Copy) — match the design system: compact ghost
       buttons that fill solid blue with white text on hover. Overrides the legacy oversized
       .theme-btn styling these carry. */
    div.dt-buttons { display: inline-flex; gap: 8px; flex-wrap: wrap; margin-bottom: 4px; }
    div.dt-buttons .dt-button,
    div.dt-buttons .btn,
    div.dt-buttons .theme-btn {
        background: #fff !important; color: #374151 !important;
        border: 0.5px solid #e5e7eb !important; border-radius: 7px !important;
        padding: 7px 14px !important; margin: 0 !important;
        font-size: 12px !important; font-weight: 500 !important; line-height: 1.2 !important;
        box-shadow: none !important; transition: all .13s;
    }
    div.dt-buttons .dt-button:hover,
    div.dt-buttons .btn:hover,
    div.dt-buttons .theme-btn:hover {
        background: #185FA5 !important; border-color: #185FA5 !important;
        color: #fff !important; transform: translateY(-1px);
    }
    div.dt-buttons .dt-button:focus,
    div.dt-buttons .dt-button.focus { box-shadow: 0 0 0 3px rgba(24,95,165,.1) !important; }
</style>
