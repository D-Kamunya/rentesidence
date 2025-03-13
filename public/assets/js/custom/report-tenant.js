var dt = $("#tenantReportDataTable").DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: $("#tenantReportRoute").val(),
    order: [1, "desc"],
    ordering: false,
    autoWidth: false,
    lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, "All"],
    ],
    drawCallback: function () {
        $(".dataTables_length select").addClass("form-select form-select-sm");
    },
    language: {
        paginate: {
            previous:
                '<span class="iconify" data-icon="icons8:angle-left"></span>',
            next: '<span class="iconify" data-icon="icons8:angle-right"></span>',
        },
    },
    dom: '<"row"<"col-sm-4"l><"col-sm-4"B><"col-sm-4"f>>tr<"bottom"<"row"<"col-sm-6"i><"col-sm-6"p>>><"clear">',
    buttons: [
        {
            extend: "excelHtml5",
            className: "theme-btn theme-button1 default-hover-btn",
            title: "",
            filename: function () {
                return (
                    $("#ownerName").val() +
                    " - " +
                    $("#appName").val() +
                    " Tenant Report"
                );
            },
            customize: function (xlsx) {
                var sheet = xlsx.xl.worksheets["sheet1.xml"];
                var appName = $("#appName").val();
                var $sheet = $(sheet);

                // Move headers to row 2
                $("row:first", sheet).attr("r", 2);
                $("row:first c", sheet).attr("r", function (i, oldVal) {
                    return oldVal.replace(/\d+/, 2);
                });

                // Adjust SL number to start from 1
                $sheet.find("row").each(function (index, row) {
                    var rowIndex = parseInt($(row).attr("r"));
                    if (rowIndex >= 3) {
                        var slCell = $(row).find('c[r^="A"]');
                        if (slCell.length > 0) {
                            slCell.find("v").text(index - 1);
                        }
                    }
                });

                // 🎯 **Insert Title in Column D (4th column)**
                $("sheetData row:first", sheet).before(`
                    <row r="1">
                        <c t="inlineStr" r="C1" s="42">
                            <is><t>${$(
                                "#ownerName"
                            ).val()} - ${appName} Tenant Report</t></is>
                        </c>
                    </row>
                `);

                // ✅ **Ensure Background Color is Only in Column D**
                var titleCell = $sheet.find('c[r="C1"]');
                titleCell.attr("s", "42"); // Apply style 42 only to C1

                // 🎯 **Centered Footer (Column D)**
                var lastRow = $("row:last", sheet).attr("r");
                var footerRow = parseInt(lastRow) + 2;

                $("sheetData", sheet).append(`
                    <row r="${footerRow}">
                        <c t="inlineStr" r="C${footerRow}">
                            <is><t>Confidential ${appName} Report - Do Not Share</t></is>
                        </c>
                    </row>
                `);
            },
        },
        {
            extend: "pdf",
            className: "theme-btn theme-button1 default-hover-btn",
            title: "",
            filename: function () {
                return (
                    $("#ownerName").val() +
                    " - " +
                    $("#appName").val() +
                    " Tenant Report"
                );
            },
            exportOptions: {
                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
            },
            customize: function (doc) {
                var ownerName = $("#ownerName").val();
                var appName = $("#appName").val();
                var logo = $("#userLogo").val(); // Ensure this is a valid Base64 image or URL

                // Set up title content manually
                doc.content.unshift({
                    columns: [
                        {
                            stack: [
                                {
                                    image: logo, // Use a valid base64 image
                                    width: 60,
                                    alignment: "center",
                                    margin: [0, 0, 0, 3],
                                },
                                {
                                    text: ownerName,
                                    fontSize: 10,
                                    alignment: "center",
                                    margin: [0, 0, 0, 3],
                                },
                                {
                                    text: appName + " Tenant Report",
                                    fontSize: 8,
                                    alignment: "center",
                                },
                            ],
                        },
                    ],
                    margin: [0, 0, 0, 10], // Add some spacing after header
                });

                // Optional: Adjust styles for better formatting
                doc.styles.title = {
                    fontSize: 14,
                    bold: true,
                    alignment: "center",
                };

                var table = doc.content[doc.content.length - 1]; // Target the table
                table.layout = "lightHorizontalLines"; // Optional: Improve styling
                table.alignment = "center"; // **Center the table**

                table.table.widths = Array(table.table.body[0].length).fill(
                    "auto"
                );

                doc.content[1].layout = "fixed"; // Fix table layout for better alignment
                doc.content[1].alignment = "center"; // Center the table horizontally

                doc.defaultStyle.alignment = "center";

                // Add Logo to the footer
                doc.footer = function (currentPage, pageCount) {
                    return {
                        columns: [
                            {
                                stack: [
                                    {
                                        image: $("#appLogo").val(),
                                        width: 60,
                                        alignment: "center",
                                    }, // Logo
                                    {
                                        text:
                                            "Confidential " +
                                            $("#appName").val() +
                                            " Report - Do Not Share",
                                        fontSize: 10,
                                        alignment: "center",
                                    }, // Text below
                                    {
                                        text:
                                            "Page " +
                                            currentPage +
                                            " of " +
                                            pageCount,
                                        fontSize: 8,
                                        alignment: "right",
                                    }, // Page numbering
                                ],
                                alignment: "center",
                            },
                        ],
                    };
                };
            },
        },
        {
            extend: "copy",
            className: "theme-btn theme-button1 default-hover-btn",
        },
    ],
    columnDefs: [
        { className: "text-center", targets: [1, 2, 3, 4, 5, 8] },
        { className: "text-end", targets: [6, 7] },
    ],
    columns: [
        {
            data: "DT_RowIndex",
            name: "DT_RowIndex",
            orderable: false,
            searchable: false,
        },
        { data: "name", name: "users.first_name" },
        { data: "email", name: "users.email" },
        { data: "contact", name: "users.contact_number" },
        { data: "property", name: "properties.name" },
        { data: "unit", name: "property_units.unit_name" },
        { data: "paid" },
        { data: "due" },
        { data: "status" },
    ],
});
