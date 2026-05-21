$(document).ready(function () {

    var table = $("#allDataTable").DataTable({
        language: {
            search: "",
            searchPlaceholder: "Search Property on this Page",
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next:     "<i class='mdi mdi-chevron-right'>",
            },
        },

        pageLength:   10,
        responsive:   true,
        ordering:     false,
        autoWidth:    false,
        searching:    true,
        lengthChange: false,
        info:         true,
        paging:       true,

        drawCallback: function () {
            $(".dataTables_length select").addClass("form-select form-select-sm");
            // Hide pagination bar when all results fit on one page
            var info = this.api().page.info();
            $(".dataTables_paginate").toggle(info.pages > 1);
        },

        initComplete: function () {
            var api = this.api();

            // ── Keyword search ────────────────────────────────
            $("#invoiceSearch").on("keyup", function () {
                api.search(this.value).draw();
            });

            // ── Status filter tabs ────────────────────────────
            // Column 6 is Status. We search its visible text:
            //   "paid"   → regex matches badge text containing "Paid"
            //             but NOT "Overdue" (overdue invoices are unpaid)
            //   "unpaid" → matches "Pending" or "Overdue"
            //   "all"    → clears the column search
            $("#statusFilter .inv-filter-tab").on("click", function () {
                $("#statusFilter .inv-filter-tab").removeClass("inv-filter-tab--active");
                $(this).addClass("inv-filter-tab--active");

                var filter = $(this).data("filter");
                if (filter === "all") {
                    api.column(6).search("", true, false).draw();
                } else if (filter === "paid") {
                    // Matches rows containing "Paid" but not just "Overdue/Pending"
                    api.column(6).search("Paid", true, false).draw();
                } else {
                    // Unpaid = Pending + Overdue
                    api.column(6).search("Pending|Overdue", true, false).draw();
                }
            });
        },
    });
});