{{-- Recorded-consent checkbox for the Transaction money-path switch, shown inside the financing
     apply forms for owners not already on Transaction (a disbursed facility moves them to it).
     The server (FinancingController@store) requires + records this. Expects $currentMode. --}}
@if (($currentMode ?? 'free') !== 'transaction')
    <label style="display:flex;gap:9px;align-items:flex-start;font-size:13px;color:#374151;line-height:1.5;margin:4px 0 16px;cursor:pointer;">
        <input type="checkbox" name="transaction_mode_consent" value="1" required style="margin-top:3px;flex:0 0 auto;">
        <span>{{ \App\Models\TransactionModeConsent::statement() }}</span>
    </label>
@endif
