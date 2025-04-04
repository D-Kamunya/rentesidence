$(document).on("change", "#property_id", function () {
    var property_id = $(this).val();
    commonAjax(
        "GET",
        $("#getPropertyUnitsRoute").val(),
        getUnitsRes,
        getUnitsRes,
        { property_id: property_id }
    );
});

function getUnitsRes(response) {
    var html = '<option value="">--Select Option--</option>';
    Object.entries(response.data).forEach((unit) => {
        html +=
            '<option value="' +
            unit[1].id +
            '">' +
            unit[1].unit_name +
            "</option>";
    });
    $("#unit_id").html(html);
}

$(document).on("click", "#searchBtn", function () {
    var url =
        $("#earningReportRoute").val() +
        "?start_date=" +
        $("#start_date").val() +
        "&end_date=" +
        $("#end_date").val() +
        "&property_id=" +
        $("#property_id").val() +
        "&unit_id=" +
        $("#unit_id").val();
    dt.ajax.url(url).load();
});

var dt = $("#allReportEarningDataTable").DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    ajax:
        $("#earningReportRoute").val() +
        "?start_date=&end_date=&property_id=&unit_id=",
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
                    " Earning Report"
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
                <c t="inlineStr" r="D1" s="42">
                    <is><t>${$(
                        "#ownerName"
                    ).val()} - ${appName} Earning Report</t></is>
                </c>
            </row>
        `);

                // ✅ **Ensure Background Color is Only in Column D**
                var titleCell = $sheet.find('c[r="D1"]');
                titleCell.attr("s", "42"); // Apply style 42 only to D1

                // 🎯 **Centered Footer (Column D)**
                var lastRow = $("row:last", sheet).attr("r");
                var footerRow = parseInt(lastRow) + 2;

                $("sheetData", sheet).append(`
            <row r="${footerRow}">
                <c t="inlineStr" r="D${footerRow}">
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
                    " Earning Report"
                );
            },
            exportOptions: {
                columns: ":visible",
            },
            customize: function (doc) {
                var ownerName = $("#ownerName").val();
                var appName = $("#appName").val();
                var logo = $("#userLogo").val(); // Ensure this is a valid Base64 image or URL

                // 🔹 Set page size to A3 (wider)
                // doc.pageSize = "A3"; // ✅ Wider layout

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
                                    text: appName + " Earning Report",
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

                // 🔹 Allow horizontal scrolling
                table.table.widths = Array(table.table.body[0].length).fill("auto"); // ✅ Prevents column shrinkage
                doc.content[1].layout = "fixed"; // Fix table layout for better alignment
                doc.content[1].alignment = "center"; // Center the table horizontally

                doc.defaultStyle.alignment = "center";

                // 🔹 Adjusting header font sizes for better readability
                doc.styles.tableHeader = {
                    fontSize: 10,
                    bold: true,
                    alignment: "center",
                };

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
                                        margin: [0, -30, 0, 2], // ✅ Moves logo up kiasi
                                    }, // Logo
                                    {
                                        text:
                                            "Confidential " +
                                            $("#appName").val() +
                                            " Report - Do Not Share",
                                        fontSize: 10,
                                        bold: true, 
                                        
                                        alignment: "center",
                                        margin: [0, -5, 0, 2], // ✅ Moves text closer to logo
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
        { className: "text-center", targets: [1, 2, 3, 4, 5] },
        { className: "text-end", targets: [6] },
    ],
    footerCallback: function (row, data, start, end, display) {
        var api = this.api();
        // Remove the formatting to get integer data for summation
        var intVal = function (num) {
            var val = 0;
            if (!!num[0].trim() && !isNaN(+num[0])) {
                val = num.split(" ")[0];
            } else {
                val = num.split(" ")[1];
            }
            return Number(val.replace(",", ""));
        };
        var totalTaxAmount = 0;
        var totalAmount = 0;
        for (var i = 0; i < data.length; i++) {
            totalTaxAmount += parseFloat(intVal(data[i]["tax_amount"]));
            totalAmount += parseFloat(intVal(data[i]["amount"]));
        }

        $(api.column(4).footer()).html(
            "All Total : " +
                currencyPrice(api.ajax.json().total) +
                " (Tax : " +
                currencyPrice(api.ajax.json().tax_amount) +
                ")"
        );
        $(api.column(5).footer()).html(
            "Page Total : " + currencyPrice(totalTaxAmount)
        );
        $(api.column(6).footer()).html(
            "Page Total : " + currencyPrice(totalAmount)
        );
    },
    columns: [
        {
            data: "DT_RowIndex",
            name: "DT_RowIndex",
            orderable: false,
            searchable: false,
        },
        { data: "invoice", name: "invoices.invoice_no" },
        { data: "property", name: "properties.name" },
        { data: "unit", name: "property_units.unit_name" },
        // { data: "date", name: "invoices.created_at" },
        { data: "invoice_month",  name: "invoices.month" },
        { data: "tax_amount", name: "invoices.tax_amount" },
        { data: "amount", name: "invoices.amount" },   
    ],
});
