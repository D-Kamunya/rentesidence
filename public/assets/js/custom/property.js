// State selector
var thisStateSelector;
var property_id = $("#property_id").val();
var propertyUnitIds = [];
var country_id;
var state_id;
var city_id;
getPropertyInformation(property_id);

// Response handler
function stepChange(response) {
    var output = "";
    var type = "error";
    $(".error-message").remove();
    $(".is-invalid").removeClass("is-invalid");
    if (response["status"] == true) {
        output = output + response["message"];
        type = "success";
        toastr.success(response.data.message);
        $("#addHtmlForm").html(response.data.view);
        stepActiveClass(response.data.step);
        if (response.data.step == 4) {
            propertyUnitIds = response.data.propertyUnitIds;
        }
        if (response.data.property.property_detail) {
            country_id = response.data.property.property_detail.country_id;
            state_id = response.data.property.property_detail.state_id;
            city_id = response.data.property.property_detail.city_id;
            if (country_id) {
                getStateByCountryId(country_id);
            }
            if (state_id) {
                getCitiesByState(state_id);
            }
        }
        datePicker();
        if (response.data.step == 5) {
            thumbmnilImage();
            dropzone();
        }
        alertAjaxMessage(type, output);
    } else {
        commonHandler(response);
    }
}

// Go to Step
function stepActiveClass(step) {
    if (step == 1) {
        $("#accountInformationStep").addClass("active");
        $("#locationStep").removeClass("active");
        $("#unitStep").removeClass("active");
        $("#rentChargesStep").removeClass("active");
        $("#imageStep").removeClass("active");
    } else if (step == 2) {
        $("#accountInformationStep").addClass("active");
        $("#locationStep").addClass("active");
        $("#unitStep").removeClass("active");
        $("#rentChargesStep").removeClass("active");
        $("#imageStep").removeClass("active");
    } else if (step == 3) {
        $("#accountInformationStep").addClass("active");
        $("#locationStep").addClass("active");
        $("#unitStep").addClass("active");
        $("#rentChargesStep").removeClass("active");
        $("#imageStep").removeClass("active");
    } else if (step == 4) {
        $("#accountInformationStep").addClass("active");
        $("#locationStep").addClass("active");
        $("#unitStep").addClass("active");
        $("#rentChargesStep").addClass("active");
        $("#imageStep").removeClass("active");
    } else if (step == 5) {
        $("#accountInformationStep").addClass("active");
        $("#locationStep").addClass("active");
        $("#unitStep").addClass("active");
        $("#rentChargesStep").addClass("active");
        $("#imageStep").addClass("active");
    }
}

function datePicker() {
    $(".datepicker").datepicker({
        dateFormat: "yy-mm-dd",
        duration: "fast",
    });
}

function thumbmnilImage() {
    document
        .querySelector("#app-logo-profile-img-file-input")
        .addEventListener("change", function () {
            var o = document.querySelector(".app-logo-user-profile-image"),
                e = document.querySelector(".app-logo-profile-img-file-input")
                    .files[0],
                i = new FileReader();
            i.addEventListener(
                "load",
                function () {
                    o.src = i.result;
                },
                !1
            ),
                e && i.readAsDataURL(e);
        });
}

function dropzone() {
    var dropzonePreviewNode = document.querySelector("#dropzone-preview-list");
    dropzonePreviewNode.id = "";
    var previewTemplate = dropzonePreviewNode.parentNode.innerHTML;
    dropzonePreviewNode.parentNode.removeChild(dropzonePreviewNode);
    var route = $("#imageStoreRoute").val() + "/" + $(".property_id").val();
    var dropzone = new Dropzone(".dropzone", {
        method: "post",
        url: route,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        previewTemplate: previewTemplate,
        previewsContainer: "#dropzone-preview",
        success: function (response) {
            toastr.success("Uploaded Successfully");
        },
        error: function (error) {
            if (error.status) {
                toastr.error(error.responseJSON.message);
            }
        },
    });
}

// Get location
$(document).on("change", ".country_id", function () {
    thisStateSelector = $(this);
    getStateByCountryId($(thisStateSelector).val());
});

function getStateByCountryId(country_id) {
    var getStateListRoute = $("#getStateListRoute").val();
    commonAjax(
        "GET",
        getStateListRoute,
        getStateByCountryRes,
        getStateByCountryRes,
        { country_id: country_id }
    );
}

function getStateByCountryRes(response) {
    var states = response.data.states;
    var optionsHtml = states
        .map(function (opt) {
            return (
                "<option " +
                (state_id == opt.id ? "selected" : "") +
                ' value="' +
                opt.id +
                '">' +
                opt.name +
                "</option>"
            );
        })
        .join("");
    var html = '<option value="">--Select State--</option>' + optionsHtml;
    $("#stateHtmlOption").html(html);
    $("#cityHtmlOption").html('<option value="">--Select City--</option>');
}

$(document).on("change", ".state_id", function () {
    thisStateSelector = $(this);
    getCitiesByState($(thisStateSelector).val());
});

function getCitiesByState(state_id) {
    var getCityListRoute = $("#getCityListRoute").val();
    commonAjax(
        "GET",
        getCityListRoute,
        getCitiesByStateRes,
        getCitiesByStateRes,
        { state_id: state_id }
    );
}

$(document).on("click", ".unit-edit", function () {
    let detailsUrl = $(this).data("detailsurl");
    commonAjax("GET", detailsUrl, getDataEditRes, getDataEditRes);
});

$(document).on("click", ".add-unit", function () {
    var selector = $("#addUnitModal");
    selector.find(".is-invalid").removeClass("is-invalid");
    selector.find(".error-message").remove();
    selector.find(".select_rent_type").removeClass("active");
    selector.find(".add-tab-pane").removeClass("active");
    selector.find("form").trigger("reset");
    selector.modal("show");
});

function getDataEditRes(response) {
    var selector = $(".edit_modal");
    selector.find(".is-invalid").removeClass("is-invalid");
    selector.find(".error-message").remove();
    selector.modal("show");

    var imageUrl =
        response.data.unit.folder_name + "/" + response.data.unit.file_name;
    var domain = window.location.origin; // Get the current domain of the application
    var unitImage = domain + "/storage/" + imageUrl; // Construct the full asset URL

    if (
        response.data.unit.file_name !== null &&
        response.data.unit.file_name !== undefined
    ) {
        document.getElementById("unit-image").setAttribute("src", unitImage);
    } else {
        document
            .getElementById("unit-image")
            .setAttribute("src", domain + "/assets/images/no-image.jpg");
    }
    selector.find("input[name=property_id]").val(response.data.property.id);
    selector.find("input[name=unit_id]").val(response.data.unit.id);
    selector.find("input[name=unit_name]").val(response.data.unit.unit_name);
    selector.find("input[name=bedroom]").val(response.data.unit.bedroom);
    selector.find("input[name=bath]").val(response.data.unit.bath);
    selector.find("input[name=kitchen]").val(response.data.unit.kitchen);
    selector
        .find("input[name=square_feet]")
        .val(response.data.unit.square_feet);
    selector.find("input[name=amenities]").val(response.data.unit.amenities);
    selector.find("input[name=condition]").val(response.data.unit.condition);
    selector.find("input[name=parking]").val(response.data.unit.parking);
    selector
        .find("input[name=general_rent]")
        .val(response.data.unit.general_rent);
    if (response.data.unit.security_deposit_type === 0) {
        $('#security_deposit_type option[value="0"]').prop("selected", true);
    } else if (response.data.unit.security_deposit_type === 1) {
        $('#security_deposit_type option[value="1"]').prop("selected", true);
    }
    if (response.data.unit.late_fee_type === 0) {
        $('#late_fee_type option[value="0"]').prop("selected", true);
    } else if (response.data.unit.late_fee_type === 1) {
        $('#late_fee_type option[value="1"]').prop("selected", true);
    }

    if (response.data.unit.rent_type === 1) {
        // Add the 'active' class to the button
        $("#monthly-unit-block-tab").addClass("active");
        selector
            .find("input[name=monthly_due_day]")
            .val(response.data.unit.monthly_due_day);
        selector
            .find("input[name=rent_type]")
            .val(response.data.unit.rent_type);
        $("#monthly-unit-block-tab-pane").addClass("show active");
    } else if (response.data.unit.rent_type === 2) {
        $("#yearly-unit-block-tab").addClass("active");
        selector
            .find("input[name=yearly_due_day]")
            .val(response.data.unit.yearly_due_day);
        selector
            .find("input[name=rent_type]")
            .val(response.data.unit.rent_type);
        $("#yearly-unit-block-tab-pane").addClass("show active");
    } else if (response.data.unit.rent_type === 3) {
        $("#custom-unit-block-tab").addClass("active");
        selector
            .find("input[name=lease_start_date]")
            .val(response.data.unit.lease_start_date);
        selector
            .find("input[name=lease_end_date]")
            .val(response.data.unit.lease_end_date);
        selector
            .find("input[name=lease_payment_due_date]")
            .val(response.data.unit.lease_payment_due_date);
        selector
            .find("input[name=rent_type]")
            .val(response.data.unit.rent_type);
        $("#custom-unit-block-tab-pane").addClass("show active");
    }
    selector
        .find("input[name=security_deposit]")
        .val(response.data.unit.security_deposit);
    selector.find("input[name=late_fee]").val(response.data.unit.late_fee);
    selector
        .find("input[name=incident_receipt]")
        .val(response.data.unit.incident_receipt);

    selector
        .find("input[name=description]")
        .val(response.data.unit.description);
}

function getCitiesByStateRes(response) {
    var cities = response.data.cities;
    var optionsHtml = cities
        .map(function (opt) {
            return (
                "<option " +
                (city_id == opt.id ? "selected" : "") +
                ' value="' +
                opt.id +
                '">' +
                opt.name +
                "</option>"
            );
        })
        .join("");
    var html = '<option value="">--Select City--</option>' + optionsHtml;
    $("#cityHtmlOption").html(html);
}

// Back step
$(document).on("click", ".locationBack", function () {
    var property_id = $(".property_id").val();
    getPropertyInformation(property_id);
    stepActiveClass(1);
});

$(document).on("click", ".unitBack", function () {
    var property_id = $(".property_id").val();
    getLocation(property_id);
    stepActiveClass(2);
});

$(document).on("click", ".rentChargeBack", function () {
    var property_id = $(".property_id").val();
    getUnitByPropertyId(property_id);
    stepActiveClass(3);
});

$(document).on("click", ".imageBack", function () {
    var property_id = $(".property_id").val();
    getRentCharge(property_id);
    stepActiveClass(4);
});

// Back step function
function getPropertyInformation(property_id) {
    var getPropertyInformationRoute = $("#getPropertyInformationRoute").val();
    commonAjax(
        "GET",
        getPropertyInformationRoute,
        getPropertyInformationRes,
        getPropertyInformationRes,
        { property_id: property_id }
    );
}

function getPropertyInformationRes(response) {
    $("#addHtmlForm").html(response.data);
    datePicker();
}

function getLocation(property_id) {
    var getLocationRoute = $("#getLocationRoute").val();
    commonAjax("GET", getLocationRoute, getLocationRes, getLocationRes, {
        property_id: property_id,
    });
}

function getLocationRes(response) {
    $("#addHtmlForm").html(response.data.view);
    datePicker();
    if (response.data.property.property_detail) {
        country_id = response.data.property.property_detail.country_id;
        state_id = response.data.property.property_detail.state_id;
        city_id = response.data.property.property_detail.city_id;
        if (country_id) {
            getStateByCountryId(country_id);
        }
        if (state_id) {
            getCitiesByState(state_id);
        }
    }
}

function getUnitByPropertyId(property_id) {
    var getUnitRoute = $("#getUnitRoute").val();
    commonAjax("GET", getUnitRoute, getUnitRes, getUnitRes, {
        property_id: property_id,
    });
}

function getUnitRes(response) {
    $("#addHtmlForm").html(response.data.view);
    datePicker();
}

function getRentCharge(property_id) {
    var getRentChargeRoute = $("#getRentChargeRoute").val();
    commonAjax("GET", getRentChargeRoute, getUnitRes, getUnitRes, {
        property_id: property_id,
    });
}

function getRentChargeRes(response) {
    $("#addHtmlForm").html(response.data.view);
    datePicker();
    dropzone();
}

$(document).on("change", "#sameUnitRent", function () {
    if ($(this).prop("checked")) {
        var select_unit_id = $("#select_unit_id").val();
        if (select_unit_id == "") {
            $(this).prop("checked", false);
            toastr.error("Select unit name");
        } else {
            var general_rent = $("#general_rent" + select_unit_id).val();
            var security_deposit = $(
                "#security_deposit" + select_unit_id
            ).val();
            var late_fee = $("#late_fee" + select_unit_id).val();
            var incident_receipt = $(
                "#incident_receipt" + select_unit_id
            ).val();
            if (general_rent != "") {
                for (let i = 0; i < propertyUnitIds.length; i++) {
                    $("#general_rent" + propertyUnitIds[i]).val(general_rent);
                    $("#security_deposit" + propertyUnitIds[i]).val(
                        security_deposit
                    );
                    $("#late_fee" + propertyUnitIds[i]).val(late_fee);
                    $("#incident_receipt" + propertyUnitIds[i]).val(
                        incident_receipt
                    );
                }
            } else {
                $(this).prop("checked", false);
                toastr.error("Provide general rent");
            }
        }
    } else {
        $(this).prop("checked", false);
    }
});

$(document).on("click", ".select_property_type", function () {
    var select_property_type = $(this).data("property_type");
    $("#property_type").val(select_property_type);
});

$(document).on("click", ".select_unit_type", function () {
    var select_unit_type = $(this).data("unit_type");
    $("#unit_type").val(select_unit_type);
});

$(document).on("click", ".select_rent_type", function () {
    var select_rent_type = $(this).data("rent_type");
    var id = $(this).data("id");
    $("#rent_type" + id).val(select_rent_type);
});

$(document).on("click", ".add-select_rent_type", function () {
    var select_rent_type = $(this).data("add-rent_type");
    $("#add_rent_type").val(select_rent_type);
});

// Thumbnail image upload
$(document).on("change", ".thumbnailImage", function () {
    var thumbnailImageRoute = $(this).data("route");
    var fd = new FormData();
    var files = $(".thumbnailImage")[0].files;
    if (files.length > 0) {
        fd.append("file", files[0]);
        commonAjax(
            "POST",
            thumbnailImageRoute,
            getThumbnailImageRes,
            getThumbnailImageRes,
            fd
        );
    } else {
        alert("Please select a file.");
    }
});

function getThumbnailImageRes(response) {
    toastr.success(response.message);
}

$(document).on("keyup", ".map_link", function () {
    $("#map_link_iframe").attr("src", $(".map_link").val());
});

$(document).on("click", ".remove-field", function () {
    $(this).parent(".multi-field").remove();
});

// Document remove
$(document).on("click", ".removeImage", function () {
    thisStateSelector = $(this);
    var route = $(thisStateSelector).data("route");
    Swal.fire({
        title: "Sure! You want to delete?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, Delete It!",
    }).then((result) => {
        if (result.value) {
            commonAjax("GET", route, documentRemovedRes, documentRemovedRes);
        }
    });
});

function documentRemovedRes(response) {
    toastr.success(response.message);
    $(thisStateSelector)
        .parent(".dropzone-remove-icon")
        .closest(".dropzone-img-wrap")
        .closest("#dropzone-preview-list")
        .remove();
    Swal.fire({
        title: "Deleted",
        html: ' <span style="color:red">Item has been deleted</span> ',
        timer: 6000,
        icon: "success",
    });
}

$(document).on("click", ".add-field", function () {
    $(".multi-fields").append(
        `<div class="multi-field border-bottom pb-25 mb-25">
            <input type="hidden" name="multiple[id][]" value="">
            <div class="row">
                <div class="col-md-6 col-lg-6 col-xl-3 mb-25">
                    <label class="label-text-title color-heading font-medium mb-2">Unit Name</label>
                    <input type="text" name="multiple[unit_name][]" class="form-control" placeholder="Enter your unit name">
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 mb-25">
                    <label class="label-text-title color-heading font-medium mb-2">Bedroom</label>
                    <input type="number" name="multiple[bedroom][]" class="form-control" placeholder="0">
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 mb-25">
                    <label class="label-text-title color-heading font-medium mb-2">Baths</label>
                    <input type="number" name="multiple[bath][]" class="form-control" placeholder="0">
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 mb-25">
                    <label class="label-text-title color-heading font-medium mb-2">Kitchen</label>
                    <input type="number" name="multiple[kitchen][]" class="form-control" placeholder="0">
                </div>
            </div>

            <button type="button" class="remove-field red-color">Remove</button>
        </div>`
    );
});
