{{--
    CS toast styling — the single source for how toastr notifications look across EVERY account
    type and the public frontend. Previously this lived inline in owner/layouts/app.blade.php, so
    only owner pages got the branded look while every other layout fell back to toastr.min.css's
    legacy green (#51A351). Include this right after toastr.min.css everywhere it's loaded.
--}}
<style>
    #toast-container > .toast-success {
        background-color: #1D9E75 !important;
        border-left: 4px solid #0F6E56 !important;
        color: #fff !important;
    }
    #toast-container > .toast-error {
        background-color: #dc2626 !important;
        border-left: 4px solid #991b1b !important;
        color: #fff !important;
    }
    #toast-container > .toast-warning {
        background-color: #d97706 !important;
        border-left: 4px solid #92400e !important;
        color: #fff !important;
    }
    #toast-container > .toast-info {
        background-color: #185FA5 !important;
        border-left: 4px solid #0F4A84 !important;
        color: #fff !important;
    }
    #toast-container > div {
        box-shadow: 0 4px 12px rgba(0,0,0,.15) !important;
        border-radius: 8px !important;
        opacity: 1 !important;
    }
</style>
