$(document).ready(function () {
    $("#allDataTable").DataTable({
        language: {
            search: "", // Remove the default "Search" label
            searchPlaceholder: "Search Property on this Page", // Set the placeholder text
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>",
            },
        },

        pageLength: 10,
        responsive: true,
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
    });
});
