{{--
    Shared Knowledge Base nav highlight — one source of truth so the KB entry looks
    identical across every account's sidebar (owner, admin, affiliate) and matches the
    finance-partner treatment (blog newsletter-card blue). Add class="kb-nav-highlight"
    to the KB <a>. !important beats the theme's #sidebar-menu id-specificity AND the
    global `a:hover{color:var(--primary-color)!important}` rule.
--}}
<style>
    #sidebar-menu .kb-nav-highlight {
        background: linear-gradient(135deg, #185FA5, #0F4A84) !important;
        color: #fff !important;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(24, 95, 165, .2);
        margin-top: 4px;
    }
    #sidebar-menu .kb-nav-highlight i,
    #sidebar-menu .kb-nav-highlight span {
        color: #fff !important;
    }
    #sidebar-menu .kb-nav-highlight:hover,
    #sidebar-menu .kb-nav-highlight:focus,
    #sidebar-menu .kb-nav-highlight.active,
    #sidebar-menu .kb-nav-highlight.mm-active {
        background: linear-gradient(135deg, #1c72c2, #0F4A84) !important;
        color: #fff !important;
    }
    #sidebar-menu .kb-nav-highlight:hover i,
    #sidebar-menu .kb-nav-highlight:hover span {
        color: #fff !important;
    }
    /* metismenu caret on the admin collapsible KB parent → keep it legible on blue */
    #sidebar-menu .kb-nav-highlight.has-arrow::after {
        color: #fff !important;
        border-color: #fff !important;
    }
</style>
