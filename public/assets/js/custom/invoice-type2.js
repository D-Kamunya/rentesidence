//this one is for use on add invoice modal since the add invoice button calls #add, clicking on it activated both the add invoice
//modal and the add invoice type modal together so here i've differentiated the add call by adding number 2 to it, the sane
//to the name of this file. The edit invoice type function has been removed as it is not required in add invoice modal.

// Update the open second modal handler to reset the submitted flag
function invoiceTypeStoreDataRes(response) {
    var output = '';
    var type = 'error';
    
    $('.error-message').remove();
    $('.is-invalid').removeClass('is-invalid');
    
    if (response['status'] == true) {
        output = output + response['message'];
        type = 'success';
        toastr.success(response.message);
        
        // Update all invoice type dropdowns
        var html = '<option value="">--Select Type--</option>';
        
        if (response.data && response.data.invoiceTypes) {
            response.data.invoiceTypes.forEach((invoiceType) => {
                html += '<option value="' + invoiceType.id + '">' + invoiceType.name + '</option>';
            });
        }
        
        // Update all dropdowns with this class
        $('.invoiceItem-invoice_type_id').html(html);
        
        // Select the newly added invoice type
        if (response.data && response.data.newInvoiceType) {
            $('.invoiceItem-invoice_type_id').val(response.data.newInvoiceType.id);
        }
        
        // Hide only the second modal
        $('#addInvoiceTypeModal').modal('hide');
        
    } else {
        commonHandler(response);
    }
}
    
    // Modified hidden event handler
    $('#addInvoiceTypeModal').on('hidden.bs.modal', function () {
        // Only show the first modal if the second modal was closed manually (not via successful submission)
        // You can use a flag to track this
        if (!$(this).data('submitted')) {
            $('#createNewInvoiceModal').modal('show');
        }
        // Reset the flag
        $(this).data('submitted', false);
    });
    
    // Update the open second modal handler to reset the submitted flag
    $("#add2").on("click", function () {
        var selector = $("#addInvoiceTypeModal");
        selector.find(".is-invalid").removeClass("is-invalid");
        selector.find(".error-message").remove();
        selector.data('submitted', false); // Reset the flag
        selector.modal("show");
        selector.find("form").trigger("reset");
    });
