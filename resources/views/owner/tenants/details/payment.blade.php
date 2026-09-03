@extends('owner.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                <div class="td-header">
                    <div>
                        <h2 class="td-title">{{ $pageTitle }}</h2>
                        <ol class="td-crumb">
                            <li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li>›</li>
                            <li><a href="{{ route('owner.tenant.index') }}">{{ __('Tenants') }}</a></li>
                            <li>›</li>
                            <li>{{ __('Payments & Deposit') }}</li>
                        </ol>
                    </div>
                    @if ($tenant->status == TENANT_STATUS_ACTIVE)
                        <button type="button" id="addInvoice" class="pf-btn pf-btn--primary" title="{{ __('Add New Invoice') }}">
                            <i class="ri-add-line"></i> {{ __('Add New Invoice') }}
                        </button>
                    @endif
                </div>

                <div class="td-layout">
                    <aside class="td-rail">
                        @include('owner.tenants.details._hero')
                        @include('owner.tenants.details.sidenav')
                    </aside>

                    <div class="td-content">
                        {{-- ── Settle-deposit modal ── --}}
                        @if (($depositHeld ?? 0) > 0)
                            <div class="modal fade" id="settleModal" tabindex="-1" aria-labelledby="settleModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content" style="border:none;border-radius:14px;overflow:hidden;">
                                        <div class="modal-header" style="border-bottom:0.5px solid #e5e7eb;padding:18px 22px;">
                                            <div>
                                                <p style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#185FA5;margin:0 0 3px;">{{ __('Move out') }}</p>
                                                <h4 id="settleModalLabel" style="font-size:16px;font-weight:600;color:#111827;margin:0;">{{ __('Settle deposit') }}</h4>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                        </div>
                                        <div class="modal-body" style="padding:20px 22px;">
                                            @if (!empty($finalCtx) && empty($finalCtx['already_billed']))
                                                <div class="stl-order-nudge">
                                                    <i class="ri-lightbulb-flash-line"></i>
                                                    <span>{{ __('Tip: generate the final rent invoice first (via the notice above) so it appears here as an arrears line you can deduct from the deposit.') }}</span>
                                                </div>
                                            @endif
                                            <div class="stl-held">{{ __('Deposit held') }}: <strong id="stlHeld">—</strong></div>

                                            <div id="stlArrears" class="stl-arrears"></div>

                                            <div class="stl-lines-head">{{ __('Deductions') }} <span class="stl-lines-hint">{{ __('(damage, charges, arrears)') }}</span></div>
                                            <div id="stlLines"></div>
                                            <button type="button" id="stlAddLine" class="stl-addline">+ {{ __('Add deduction') }}</button>

                                            <div class="stl-summary">
                                                <div class="stl-summary__row"><span>{{ __('Total deductions') }}</span><span id="stlTotalDed">—</span></div>
                                            </div>
                                            <div id="stlWarn" class="stl-warn" style="display:none;">{{ __('Deductions exceed the held deposit. Charge the excess separately.') }}</div>
                                            <div class="stl-refund-box">
                                                <span class="stl-refund-box__label">{{ __('Refund due to tenant') }}</span>
                                                <strong class="stl-refund-box__amt" id="stlRefund">—</strong>
                                            </div>

                                            <div class="stl-details">
                                                <div>
                                                    <label class="stl-label">{{ __('Refund method') }}</label>
                                                    <select id="stlMethod" class="stl-input">
                                                        <option value="mpesa">{{ __('M-Pesa') }}</option>
                                                        <option value="cash">{{ __('Cash') }}</option>
                                                        <option value="bank">{{ __('Bank transfer') }}</option>
                                                        <option value="other">{{ __('Other') }}</option>
                                                    </select>
                                                </div>
                                                <div id="stlReferenceWrap">
                                                    <label class="stl-label" id="stlReferenceLabel">{{ __('Reference / code') }}</label>
                                                    <input type="text" id="stlReference" class="stl-input" maxlength="100" placeholder="{{ __('e.g. M-Pesa code') }}">
                                                </div>
                                                <div>
                                                    <label class="stl-label">{{ __('Refund date') }}</label>
                                                    <input type="date" id="stlDate" class="stl-input" value="{{ \Carbon\Carbon::today()->toDateString() }}">
                                                </div>
                                            </div>
                                            <label class="stl-label" style="margin-top:12px;">{{ __('Notes (optional)') }}</label>
                                            <textarea id="stlNotes" class="stl-input" rows="2" maxlength="1000" placeholder="{{ __('Anything worth recording about the settlement…') }}"></textarea>
                                            <p class="stl-foot">{{ __('You refund the balance to your tenant directly; this records the itemized settlement. The tenant will be asked to confirm receipt.') }}</p>
                                        </div>
                                        <div class="modal-footer" style="border-top:0.5px solid #e5e7eb;padding:14px 22px;gap:8px;">
                                            <button type="button" class="stl-btn stl-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                            <button type="button" class="stl-btn stl-btn--primary" id="stlSubmit">{{ __('Record settlement') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- ── Final rent invoice modal (pro-rated / custom, mirrors move-in) ── --}}
                        @if (!empty($finalCtx) && !empty($activeNotice))
                            <div class="modal fade" id="finalInvoiceModal" tabindex="-1" aria-labelledby="finalInvoiceLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content" style="border:none;border-radius:14px;overflow:hidden;">
                                        <div class="modal-header" style="border-bottom:0.5px solid #e5e7eb;padding:18px 22px;">
                                            <div>
                                                <p style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#185FA5;margin:0 0 3px;">{{ __('Move out') }}</p>
                                                <h4 id="finalInvoiceLabel" style="font-size:16px;font-weight:600;color:#111827;margin:0;">{{ __('Final rent invoice') }}</h4>
                                                <p class="ow-muted" style="font-size:12px;color:#6b7280;margin:4px 0 0;">{{ __('Move-out') }} {{ $finalCtx['move_out'] }} · {{ $finalCtx['occupied_days'] }}/{{ $finalCtx['days_in_month'] }} {{ __('days occupied') }}</p>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                        </div>
                                        <div class="modal-body" style="padding:20px 22px;">
                                            @if ($finalCtx['already_billed'])
                                                <div class="stl-warn" style="display:block;">
                                                    {{ __(':month rent is already invoiced — adjust that invoice directly if a pro-rated final amount is needed.', ['month' => $finalCtx['period_label']]) }}
                                                </div>
                                            @else
                                                <label class="fin-opt">
                                                    <input type="radio" name="final_mode" value="prorate" checked>
                                                    <span class="fin-opt__body">
                                                        <span class="fin-opt__title">{{ __('Pro-rated') }}
                                                            <span class="fin-opt__note">{{ __('Rent for the days occupied this month') }} ({{ $finalCtx['occupied_days'] }}/{{ $finalCtx['days_in_month'] }} {{ __('days') }})</span>
                                                        </span>
                                                        <span class="fin-opt__amt">{{ currencyPrice($finalCtx['prorated_amount']) }}</span>
                                                    </span>
                                                </label>
                                                <label class="fin-opt">
                                                    <input type="radio" name="final_mode" value="custom">
                                                    <span class="fin-opt__body">
                                                        <span class="fin-opt__title">{{ __('Custom amount') }}
                                                            <span class="fin-opt__note">{{ __('an agreed move-out figure') }}</span>
                                                        </span>
                                                        <span class="fin-opt__amt">
                                                            <input type="number" min="1" step="any" id="finalCustomAmount" class="stl-input" style="width:120px;text-align:right;" placeholder="0.00" disabled>
                                                        </span>
                                                    </span>
                                                </label>
                                            @endif
                                        </div>
                                        @if (!$finalCtx['already_billed'])
                                            <p style="font-size:11.5px;color:#9ca3af;line-height:1.5;margin:0 22px;padding-bottom:4px;">{{ __('Optional — close this without generating if there\'s no final rent to charge (e.g. already paid, or an agreed arrangement).') }}</p>
                                        @endif
                                        <div class="modal-footer" style="border-top:0.5px solid #e5e7eb;padding:14px 22px;gap:8px;">
                                            <button type="button" class="stl-btn stl-btn--ghost" data-bs-dismiss="modal">{{ !$finalCtx['already_billed'] ? __('No final rent') : __('Close') }}</button>
                                            @if (!$finalCtx['already_billed'])
                                                <button type="button" class="stl-btn stl-btn--primary" id="finalSubmit" data-url="{{ route('owner.tenant.final-invoice.store', $tenant->id) }}">{{ __('Generate invoice') }}</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- ── Reported deposit settlement — owner responds (does NOT self-resolve) ── --}}
                        @if (!empty($depositSettlement) && $depositSettlement->status === 'disputed')
                            <div class="td-notice td-notice--flag">
                                <div class="td-notice__head">
                                    <span class="td-notice__ic"><i class="ri-error-warning-line"></i></span>
                                    <div>
                                        <h4 class="td-notice__title">{{ __('Deposit settlement — issue reported') }}
                                            @if ($depositSettlement->owner_responded_at)
                                                <span class="td-notice__badge td-notice__badge--ok">{{ __('You responded') }}</span>
                                            @endif
                                        </h4>
                                        <p class="td-notice__meta">{{ __('Refund') }} <strong>{{ currencyPrice($depositSettlement->refund_amount) }}</strong> · {{ __('the tenant reports they haven\'t received it or something\'s off.') }}</p>
                                        @if ($depositSettlement->tenant_response_note)
                                            <p class="td-notice__msg">{{ __('Tenant:') }} “{{ $depositSettlement->tenant_response_note }}”</p>
                                        @endif
                                        @if ($depositSettlement->owner_responded_at)
                                            <p class="td-notice__hint">{{ __('You responded:') }} “{{ $depositSettlement->owner_response_note ?: __('(no message)') }}”. {{ __('Awaiting the tenant to confirm receipt.') }}</p>
                                        @else
                                            <p class="td-notice__hint">{{ __('Sort this out with your tenant directly (re-send the refund, or clarify), then respond so they can confirm receipt.') }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if (!$depositSettlement->owner_responded_at)
                                    <button type="button" class="td-notice__ack" data-bs-toggle="modal" data-bs-target="#respondModal">{{ __('Respond') }}</button>
                                @endif
                            </div>

                            @if (!$depositSettlement->owner_responded_at)
                                <div class="modal fade" id="respondModal" tabindex="-1" aria-labelledby="respondModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content" style="border:none;border-radius:14px;overflow:hidden;">
                                            <div class="modal-header" style="border-bottom:0.5px solid #e5e7eb;padding:18px 22px;">
                                                <h4 id="respondModalLabel" style="font-size:16px;font-weight:600;color:#111827;margin:0;">{{ __('Respond to the tenant') }}</h4>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                            </div>
                                            <div class="modal-body" style="padding:20px 22px;">
                                                <p style="font-size:12.5px;color:#6b7280;line-height:1.55;margin:0 0 12px;">{{ __('Let the tenant know how you\'ve put it right — e.g. "Re-sent via M-Pesa, code ABC123, today." They\'ll be asked to confirm receipt. (This doesn\'t close the report — only the tenant confirming receipt does.)') }}</p>
                                                <textarea id="respondNote" rows="3" maxlength="1000" class="stl-input" placeholder="{{ __('e.g. Re-sent via M-Pesa, code ABC123…') }}"></textarea>
                                            </div>
                                            <div class="modal-footer" style="border-top:0.5px solid #e5e7eb;padding:14px 22px;gap:8px;">
                                                <button type="button" class="stl-btn stl-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                <button type="button" class="stl-btn stl-btn--primary" id="respondSubmit" data-url="{{ route('owner.tenant.deposit-settlement.respond', $depositSettlement->id) }}">{{ __('Send response') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if (!empty($activeNotice))
                            <div class="td-notice {{ $activeNotice->meets_notice ? '' : 'td-notice--flag' }}">
                                <div class="td-notice__head">
                                    <span class="td-notice__ic"><i class="ri-logout-box-r-line"></i></span>
                                    <div>
                                        <h4 class="td-notice__title">{{ __('Notice to vacate') }}
                                            @if (!$activeNotice->meets_notice)
                                                <span class="td-notice__badge">{{ __('Short notice') }}</span>
                                            @elseif ($activeNotice->status === \App\Models\VacationNotice::STATUS_ACKNOWLEDGED)
                                                <span class="td-notice__badge td-notice__badge--ok">{{ __('Acknowledged') }}</span>
                                            @endif
                                        </h4>
                                        <p class="td-notice__meta">
                                            {{ __('Moving out') }} <strong>{{ \Carbon\Carbon::parse($activeNotice->intended_move_out_date)->format('d M Y') }}</strong>
                                            · {{ __('filed') }} {{ \Carbon\Carbon::parse($activeNotice->notice_date)->format('d M Y') }}
                                            · {{ $activeNotice->meets_notice
                                                    ? __(':n-day notice requirement met', ['n' => $activeNotice->notice_period_days])
                                                    : __('under the :n-day notice requirement', ['n' => $activeNotice->notice_period_days]) }}
                                        </p>
                                        @if ($activeNotice->message)
                                            <p class="td-notice__msg">{{ __('Reason') }}: “{{ $activeNotice->message }}”</p>
                                        @endif
                                        @if (!$activeNotice->meets_notice)
                                            <p class="td-notice__hint">{{ __('This is shorter than your required notice period — you may approve it or charge rent through the notice period at settlement.') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="td-notice__actions">
                                    @if ($activeNotice->status === \App\Models\VacationNotice::STATUS_PENDING)
                                        <button type="button" class="td-notice__ack" data-ack-url="{{ route('owner.tenant.vacation-notice.acknowledge', $activeNotice->id) }}">
                                            {{ __('Acknowledge') }}
                                        </button>
                                    @endif
                                    @if (!empty($finalCtx))
                                        <button type="button" class="td-notice__final {{ $finalCtx['already_billed'] ? 'td-notice__final--done' : '' }}" data-bs-toggle="modal" data-bs-target="#finalInvoiceModal"
                                                title="{{ $finalCtx['already_billed'] ? __('This month is already invoiced — click to view / adjust') : __('Bill rent for the days occupied up to the move-out date') }}">
                                            @if ($finalCtx['already_billed'])
                                                <i class="ri-check-line"></i> {{ __('Final rent invoiced') }}
                                            @else
                                                {{ __('Final rent invoice') }}
                                            @endif
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="td-card">
                            <div class="td-card__head">
                                <span class="td-card__ic"><i class="ri-bank-card-line"></i></span>
                                <h3 class="td-card__title">{{ __('Payment History') }}</h3>
                                @if (($depositHeld ?? 0) > 0)
                                    <a href="{{ route('owner.deposit.index') }}" class="td-deposit-pill" title="{{ __('Security deposit held — refundable at move-out, not income') }}">
                                        <i class="ri-safe-2-line"></i>
                                        {{ __('Deposit held') }}: {{ currencyPrice($depositHeld) }}
                                    </a>
                                    <button type="button" class="td-settle-btn" data-bs-toggle="modal" data-bs-target="#settleModal"
                                            data-context-url="{{ route('owner.tenant.deposit-settlement.context', $tenant->id) }}"
                                            data-store-url="{{ route('owner.tenant.deposit-settlement.store', $tenant->id) }}">
                                        {{ __('Settle deposit') }}
                                    </button>
                                @elseif (!empty($depositSettlement))
                                    @php
                                        $dsStat = $depositSettlement->status;
                                        $dsSuffix = $dsStat === 'confirmed' ? __('confirmed by tenant')
                                                    : ($dsStat === 'disputed' ? __('disputed by tenant') : __('awaiting tenant confirmation'));
                                    @endphp
                                    <span class="td-deposit-pill {{ $dsStat === 'disputed' ? 'td-deposit-pill--disputed' : 'td-deposit-pill--settled' }}"
                                          title="{{ __('Deposit settled at move-out') }}">
                                        <i class="ri-check-double-line"></i>
                                        {{ __('Deposit settled') }} · {{ __('refunded') }} {{ currencyPrice($depositSettlement->refund_amount) }} · {{ $dsSuffix }}
                                    </span>
                                @endif
                            </div>
                            <div class="td-card__body" style="padding:16px 18px 18px;">
                                <div class="table-responsive">
                                    <table id="allInvoicePaymentDataTable" class="table theme-border p-20 responsive" style="white-space: nowrap;">
                                        <thead>
                                            <tr>
                                                <th class="all">{{ __('SL') }}</th>
                                                <th class="all">{{ __('Property') }}</th>
                                                <th class="all">{{ __('Unit') }}</th>
                                                <th class="all">{{ __('Month') }}</th>
                                                <th class="all">{{ __('Invoice') }}</th>
                                                <th class="all">{{ __('Issues Date') }}</th>
                                                <th class="all">{{ __('Due Date') }}</th>
                                                <th class="all">{{ __('Amount') }}</th>
                                                <th class="all">{{ __('Status') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- New Invoice modal (unchanged) --}}
<div class="modal fade" id="createNewInvoiceModal" tabindex="-1" aria-labelledby="createNewInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="createNewInvoiceModalLabel">{{ __('New Invoice') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span class="iconify" data-icon="akar-icons:cross"></span>
                </button>
            </div>
            <form class="ajax" action="{{ route('owner.invoice.store') }}" method="post" data-handler="getShowMessage">
                @csrf
                <div class="modal-body">
                    <div class="modal-inner-form-box bg-off-white theme-border radius-4 p-20 mb-20 pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-25">
                                <label class="label-text-title color-heading font-medium mb-2">{{ __('Invoice Prefix') }}</label>
                                <input type="text" name="name" value="INV" class="form-control">
                            </div>
                            <input type="hidden" name="property_id" value="{{ $tenant->property_id }}">
                            <input type="hidden" name="property_unit_id" value="{{ $tenant->unit_id }}">
                            <div class="col-md-6 mb-25">
                                <label class="label-text-title color-heading font-medium mb-2">{{ __('Month') }}</label>
                                <select class="form-select flex-shrink-0" name="month">
                                    <option value="">--{{ __('Select Month') }}--</option>
                                    @foreach (month() as $month)
                                        <option value="{{ $month }}">{{ $month }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-25">
                                <label class="label-text-title color-heading font-medium mb-2">{{ __('Due Date') }}</label>
                                <div class="custom-datepicker">
                                    <div class="custom-datepicker-inner position-relative">
                                        <input type="text" name="due_date" class="datepicker form-control" autocomplete="off" placeholder="{{ __('Due Date') }}">
                                        <i class="ri-calendar-2-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="multi-field-wrapper">
                        <div class="multi-fields">
                            <div class="multi-field border-bottom pb-25 mb-25">
                                <div class="modal-inner-form-box bg-off-white theme-border radius-4 p-20 mb-20">
                                    <input type="hidden" name="invoiceItem[id][]" class="" value="">
                                    <div class="row">
                                        <div class="col-md-6 mb-25">
                                            <label class="label-text-title color-heading font-medium mb-2">{{ __('Invoice Type') }}</label>
                                            <select class="form-select flex-shrink-0 invoiceItem-invoice_type_id" name="invoiceItem[invoice_type_id][]">
                                                <option value="">--{{ __('Select Type') }}--</option>
                                                @foreach ($invoiceTypes as $invoiceType)
                                                    <option value="{{ $invoiceType->id }}">{{ $invoiceType->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-25">
                                            <label class="label-text-title color-heading font-medium mb-2">{{ __('Amount') }}</label>
                                            <input type="number" name="invoiceItem[amount][]" class="form-control invoiceItem-amount" placeholder="{{ __('Amount') }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="label-text-title color-heading font-medium mb-2">{{ __('Description') }}</label>
                                            <textarea class="form-control invoiceItem-description" name="invoiceItem[description][]" placeholder="{{ __('Description') }}"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="remove-field red-color">{{ __('Remove') }}</button>
                            </div>
                        </div>
                        <button type="button" class="add-field theme-btn-purple pull-right">+ {{ __('Add Items') }}</button>
                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    <button type="button" class="theme-btn-back me-3" data-bs-dismiss="modal" title="{{ __('Back') }}">{{ __('Back') }}</button>
                    <button type="submit" class="theme-btn me-3" title="{{ __('Create Invoice') }}">{{ __('Create Invoice') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="payStatusChangeModal" tabindex="-1" aria-labelledby="payStatusChangeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="payStatusChangeModalLabel">{{ __('Payment Status Change') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span class="iconify" data-icon="akar-icons:cross"></span>
                </button>
            </div>
            <form class="ajax" action="{{ route('owner.invoice.payment.status') }}" method="post" data-handler="getShowMessage">
                <input type="hidden" name="id">
                <div class="modal-body">
                    <div class="modal-inner-form-box bg-off-white theme-border radius-4 p-20 mb-20 pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-25">
                                <label class="label-text-title color-heading font-medium mb-2">{{ __('Status') }}</label>
                                <select class="form-select flex-shrink-0" name="status">
                                    <option value="">--{{ __('Select Option') }}--</option>
                                    <option value="0">{{ __('Pending') }}</option>
                                    <option value="1">{{ __('Paid') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    <button type="button" class="theme-btn-back me-3" data-bs-dismiss="modal" title="{{ __('Back') }}">{{ __('Back') }}</button>
                    <button type="submit" class="theme-btn me-3" title="{{ __('Update') }}">{{ __('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<input type="hidden" class="invoiceTypes" value="{{ $invoiceTypes }}">
<input type="hidden" id="route" value="{{ route('owner.tenant.details', [$tenant->id, 'tab' => 'payment']) }}">
@endsection

@push('style')
    @include('common.layouts.datatable-style')
    <style>
        .td-deposit-pill { margin-left:auto; display:inline-flex; align-items:center; gap:5px; font-size:11.5px; font-weight:600;
            padding:5px 11px; border-radius:99px; background:#FAEEDA; color:#854F0B; border:0.5px solid #F5D9A8; text-decoration:none; white-space:nowrap; }
        .td-deposit-pill:hover { background:#F5E2C0; color:#6b3f08; }
        .td-deposit-pill i { font-size:13px; }
        .td-notice { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; flex-wrap:wrap;
            background:#E6F1FB; border:0.5px solid #B5D4F4; border-radius:12px; padding:15px 18px; margin-bottom:16px; }
        .td-notice--flag { background:#FAEEDA; border-color:#F5D9A8; }
        .td-notice__head { display:flex; align-items:flex-start; gap:12px; flex:1; min-width:0; }
        .td-notice__ic { flex:none; width:34px; height:34px; border-radius:9px; background:#fff; display:flex; align-items:center; justify-content:center; color:#0C447C; }
        .td-notice--flag .td-notice__ic { color:#854F0B; }
        .td-notice__ic i { font-size:17px; }
        .td-notice__title { font-size:14px; font-weight:600; color:#111827; margin:0 0 3px; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
        .td-notice__badge { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; padding:2px 8px; border-radius:99px; background:#FAEACE; color:#854F0B; }
        .td-notice__badge--ok { background:#E1F5EE; color:#0F6E56; }
        .td-notice__meta { font-size:12px; color:#4b5563; margin:0; line-height:1.5; }
        .td-notice__meta strong { color:#111827; }
        .td-notice__msg { font-size:12px; color:#6b7280; font-style:italic; margin:6px 0 0; }
        .td-notice__hint { font-size:11.5px; color:#854F0B; margin:6px 0 0; line-height:1.5; }
        .td-notice__ack { flex:none; background:#185FA5; color:#fff; border:none; border-radius:8px; font-size:12.5px; font-weight:600; padding:8px 16px; cursor:pointer; }
        .td-notice__ack:hover { background:#0F4A84; }
        .td-notice__ack:disabled { opacity:.6; cursor:default; }
        .td-notice__actions { display:flex; flex-direction:column; gap:8px; flex:none; }
        .td-notice__final { background:#fff; color:#185FA5; border:0.5px solid #B5D4F4; border-radius:8px; font-size:12.5px; font-weight:600; padding:8px 16px; cursor:pointer; white-space:nowrap; }
        .td-notice__final:hover { background:#F2F8FF; }
        .td-notice__final:disabled { opacity:.6; cursor:default; }
        .td-notice__final--done { background:#E1F5EE; color:#0F6E56; border-color:#B6E3D3; }
        .td-notice__final--done:hover { background:#D6F0E6; }
        .td-settle-btn { margin-left:8px; background:#185FA5; color:#fff; border:none; border-radius:8px; font-size:11.5px; font-weight:600; padding:6px 13px; cursor:pointer; white-space:nowrap; }
        .td-settle-btn:hover { background:#0F4A84; }
        .td-deposit-pill--settled { background:#E1F5EE; color:#0F6E56; border-color:#B6E3D3; }
        .td-deposit-pill--disputed { background:#FAECE7; color:#993C1D; border-color:#F3C4BC; }
        .stl-held { font-size:14px; color:#111827; padding:12px 14px; background:#F2F8FF; border:0.5px solid #B5D4F4; border-radius:9px; margin-bottom:14px; }
        .stl-held strong { color:#0F4A84; }
        .stl-arrears { margin-bottom:14px; }
        .stl-arrears__title { font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; font-weight:600; margin-bottom:7px; }
        .stl-arrears__item { display:flex; align-items:center; gap:8px; font-size:12.5px; color:#374151; padding:6px 0; cursor:pointer; }
        .stl-arrears__item span:nth-child(2) { flex:1; }
        .stl-arrears__amt { font-weight:600; color:#111827; }
        .stl-lines-head { font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; font-weight:600; margin-bottom:8px; }
        .stl-lines-hint { text-transform:none; letter-spacing:0; font-weight:400; color:#9ca3af; }
        .stl-line { display:flex; gap:7px; margin-bottom:7px; align-items:center; }
        .stl-line__type { flex:none; width:110px; }
        .stl-line__desc { flex:1; }
        .stl-line__amt { flex:none; width:110px; text-align:right; }
        .stl-line__type, .stl-line__desc, .stl-line__amt { border:0.5px solid #d1d5db; border-radius:7px; padding:8px 10px; font-size:13px; outline:none; }
        .stl-line__rm { flex:none; width:30px; height:30px; border:none; background:#f3f4f6; border-radius:7px; color:#993C1D; font-size:18px; line-height:1; cursor:pointer; }
        .stl-addline { background:none; border:0.5px dashed #B5D4F4; color:#185FA5; border-radius:7px; font-size:12px; font-weight:600; padding:7px 13px; cursor:pointer; margin-top:2px; }
        .stl-summary { margin-top:16px; border-top:0.5px solid #eef2f6; padding-top:12px; }
        .stl-summary__row { display:flex; justify-content:space-between; font-size:13px; color:#4b5563; padding:3px 0; }
        .stl-refund-box { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:12px;
            background:#E1F5EE; border:1px solid #B6E3D3; border-radius:10px; padding:14px 18px; }
        .stl-refund-box__label { font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:#0F6E56; }
        .stl-refund-box__amt { font-size:21px; font-weight:800; color:#0F6E56; font-variant-numeric:tabular-nums; }
        .stl-warn { margin-top:8px; font-size:12px; color:#993C1D; background:#FAECE7; border:0.5px solid #F3C4BC; border-radius:8px; padding:9px 12px; }
        .stl-details { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-top:16px; }
        @media (max-width:560px){ .stl-details { grid-template-columns:1fr; } }
        .stl-label { display:block; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; margin-bottom:5px; }
        .stl-input { width:100%; border:0.5px solid #d1d5db; border-radius:7px; padding:9px 11px; font-size:13px; color:#111827; outline:none; }
        .stl-input:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
        .stl-foot { font-size:11.5px; color:#9ca3af; line-height:1.5; margin:12px 0 0; }
        .stl-btn { border:none; border-radius:8px; font-size:12.5px; font-weight:600; padding:9px 18px; cursor:pointer; }
        .stl-btn--ghost { background:#f3f4f6; color:#374151; }
        .stl-btn--ghost:hover { background:#e5e7eb; }
        .stl-btn--primary { background:#185FA5; color:#fff; }
        .stl-btn--primary:hover { background:#0F4A84; }
        .stl-btn--primary:disabled { opacity:.6; cursor:default; }
        .fin-opt { display:flex; align-items:center; gap:12px; padding:13px 14px; border:1px solid #e5e7eb; border-radius:10px; margin-bottom:10px; cursor:pointer; }
        .fin-opt:last-child { margin-bottom:0; }
        .fin-opt:hover { border-color:#B5D4F4; background:#F7FBFF; }
        .fin-opt input[type="radio"] { accent-color:#185FA5; width:16px; height:16px; flex:none; }
        .fin-opt:has(input:checked) { border-color:#185FA5; background:#F2F8FF; box-shadow:0 0 0 3px rgba(24,95,165,.08); }
        .fin-opt__body { display:flex; align-items:center; justify-content:space-between; gap:10px; flex:1; min-width:0; }
        .fin-opt__title { font-size:13px; font-weight:500; color:#111827; }
        .fin-opt__note { display:block; font-size:11px; font-weight:400; color:#9ca3af; margin-top:2px; }
        .fin-opt__amt { font-size:14px; font-weight:600; color:#0F4A84; white-space:nowrap; }
        .stl-order-nudge { display:flex; align-items:flex-start; gap:8px; background:#E6F1FB; border:0.5px solid #B5D4F4; border-radius:9px; padding:10px 13px; margin-bottom:14px; font-size:12px; color:#0C447C; line-height:1.5; }
        .stl-order-nudge i { font-size:15px; flex:none; margin-top:1px; }
    </style>
@endpush

@push('script')
    <script>
        // Final rent invoice modal — pro-rated / custom (mirrors the move-in modal).
        (function () {
            var submit = document.getElementById('finalSubmit');
            if (!submit) return;
            var custom = document.getElementById('finalCustomAmount');
            document.addEventListener('change', function (e) {
                if (!e.target || e.target.name !== 'final_mode') return;
                var isCustom = document.querySelector('input[name="final_mode"]:checked').value === 'custom';
                if (custom) { custom.disabled = !isCustom; if (isCustom) custom.focus(); }
            });
            submit.addEventListener('click', function () {
                var mode = document.querySelector('input[name="final_mode"]:checked').value;
                var payload = { mode: mode };
                if (mode === 'custom') {
                    var amt = parseFloat(custom.value);
                    if (!amt || amt <= 0) { if (window.toastr) toastr.error('{{ __('Enter a valid amount.') }}'); return; }
                    payload.custom_amount = amt;
                }
                submit.disabled = true;
                $.ajax({
                    url: submit.getAttribute('data-url'), type: 'POST', data: payload,
                    success: function (res) { if (window.toastr) toastr.success(res.message || '{{ __('Done') }}'); setTimeout(function () { window.location.reload(); }, 900); },
                    error: function (xhr) { submit.disabled = false; var m = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || '{{ __('Something went wrong.') }}'; if (window.toastr) toastr.error(m); }
                });
            });
        })();

        // Owner responds to a reported settlement (re-prompts the tenant; does NOT self-resolve).
        (function () {
            var rs = document.getElementById('respondSubmit');
            if (!rs) return;
            rs.addEventListener('click', function () {
                var note = (document.getElementById('respondNote').value || '').trim();
                if (!note) { if (window.toastr) toastr.error('{{ __('Add a short note for your tenant.') }}'); return; }
                rs.disabled = true;
                $.ajax({
                    url: rs.getAttribute('data-url'), type: 'POST', data: { note: note },
                    success: function (res) { if (window.toastr) toastr.success(res.message || '{{ __('Done') }}'); setTimeout(function () { window.location.reload(); }, 900); },
                    error: function (xhr) { rs.disabled = false; var m = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || '{{ __('Something went wrong.') }}'; if (window.toastr) toastr.error(m); }
                });
            });
        })();

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.td-notice__ack');
            if (!btn || !btn.getAttribute('data-ack-url')) return; // only real acknowledge buttons
            btn.disabled = true;
            $.ajax({
                url: btn.getAttribute('data-ack-url'), type: 'POST',
                success: function (res) {
                    if (window.toastr) toastr.success(res.message || '{{ __('Notice acknowledged.') }}');
                    setTimeout(function () { window.location.reload(); }, 800);
                },
                error: function (xhr) {
                    btn.disabled = false;
                    var msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || '{{ __('Something went wrong.') }}';
                    if (window.toastr) toastr.error(msg);
                }
            });
        });
    </script>

    {{-- Settle-deposit modal logic --}}
    <script>
        (function () {
            var modal = document.getElementById('settleModal');
            if (!modal) return;
            var trigger = document.querySelector('.td-settle-btn');
            var contextUrl = trigger && trigger.getAttribute('data-context-url');
            var storeUrl   = trigger && trigger.getAttribute('data-store-url');
            var held = 0, curSym = '', curPlace = 'left', arrears = [];

            function fmt(n) {
                var v = Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                return curPlace === 'right' ? (v + ' ' + curSym) : (curSym + ' ' + v);
            }

            modal.addEventListener('shown.bs.modal', function () {
                if (!contextUrl || held) return; // fetch once
                $.get(contextUrl, function (res) {
                    var ctx = res && res.data ? res.data.context : null;
                    if (!ctx) return;
                    held = Number(ctx.held); curSym = ctx.currency_symbol; curPlace = ctx.currency_placement; arrears = ctx.arrears || [];
                    document.getElementById('stlHeld').textContent = fmt(held);
                    renderArrears();
                    recalc();
                });
            });

            function renderArrears() {
                var box = document.getElementById('stlArrears');
                if (!arrears.length) { box.innerHTML = ''; return; }
                var html = '<div class="stl-arrears__title">{{ __('Outstanding invoices — tick to deduct from the deposit') }}</div>';
                arrears.forEach(function (a, i) {
                    html += '<label class="stl-arrears__item"><input type="checkbox" class="stl-arrears__cb" data-idx="' + i + '"> '
                          + '<span>' + $('<div>').text(a.label).html() + '</span> <span class="stl-arrears__amt">' + fmt(a.amount) + '</span></label>';
                });
                box.innerHTML = html;
            }

            function addLine(opts) {
                opts = opts || {};
                var row = document.createElement('div');
                row.className = 'stl-line';
                row.innerHTML =
                    '<select class="stl-line__type"><option value="damage">{{ __('Damage') }}</option><option value="charge">{{ __('Charge') }}</option><option value="arrears">{{ __('Arrears') }}</option><option value="other">{{ __('Other') }}</option></select>'
                    + '<input class="stl-line__desc" placeholder="{{ __('Description') }}">'
                    + '<input class="stl-line__amt" type="number" min="0" step="any" placeholder="0.00">'
                    + '<button type="button" class="stl-line__rm" aria-label="{{ __('Remove') }}">&times;</button>';
                document.getElementById('stlLines').appendChild(row);
                if (opts.type)  row.querySelector('.stl-line__type').value = opts.type;
                if (opts.desc)  row.querySelector('.stl-line__desc').value = opts.desc;
                if (opts.amount != null) row.querySelector('.stl-line__amt').value = opts.amount;
                if (opts.invoiceId) row.setAttribute('data-invoice-id', opts.invoiceId);
                row.querySelector('.stl-line__amt').addEventListener('input', recalc);
                row.querySelector('.stl-line__rm').addEventListener('click', function () { row.remove(); recalc(); });
                recalc();
                return row;
            }

            document.getElementById('stlAddLine').addEventListener('click', function () { addLine(); });

            // Reference/code only makes sense for methods that carry one — hide it for cash.
            var methodEl = document.getElementById('stlMethod');
            var refWrap  = document.getElementById('stlReferenceWrap');
            var refLabel = document.getElementById('stlReferenceLabel');
            var refInput = document.getElementById('stlReference');
            var refPlaceholders = {
                mpesa: '{{ __('M-Pesa code') }}',
                bank:  '{{ __('Bank reference') }}',
                other: '{{ __('Reference (optional)') }}'
            };
            function syncReference() {
                var m = methodEl.value;
                if (m === 'cash') {
                    refWrap.style.display = 'none';
                    refInput.value = '';
                } else {
                    refWrap.style.display = '';
                    refLabel.textContent = (m === 'mpesa' || m === 'bank') ? '{{ __('Reference / code') }}' : '{{ __('Reference') }}';
                    refInput.placeholder = refPlaceholders[m] || '{{ __('Reference (optional)') }}';
                }
            }
            methodEl.addEventListener('change', syncReference);
            syncReference();

            document.getElementById('stlArrears').addEventListener('change', function (e) {
                var cb = e.target.closest('.stl-arrears__cb'); if (!cb) return;
                var idx = cb.getAttribute('data-idx'), a = arrears[idx];
                if (cb.checked) {
                    var row = addLine({ type: 'arrears', desc: a.label, amount: a.amount, invoiceId: a.invoice_id });
                    row.setAttribute('data-arrears-idx', idx);
                } else {
                    var ex = document.querySelector('.stl-line[data-arrears-idx="' + idx + '"]');
                    if (ex) { ex.remove(); recalc(); }
                }
            });

            function collectLines() {
                var lines = [];
                document.querySelectorAll('#stlLines .stl-line').forEach(function (r) {
                    var amt = parseFloat(r.querySelector('.stl-line__amt').value) || 0;
                    if (amt <= 0) return;
                    lines.push({
                        type: r.querySelector('.stl-line__type').value,
                        description: r.querySelector('.stl-line__desc').value,
                        amount: amt,
                        invoice_id: r.getAttribute('data-invoice-id') || null
                    });
                });
                return lines;
            }

            function recalc() {
                var total = 0;
                collectLines().forEach(function (l) { total += l.amount; });
                document.getElementById('stlTotalDed').textContent = fmt(total);
                var refund = held - total;
                document.getElementById('stlRefund').textContent = fmt(refund < 0 ? 0 : refund);
                var over = total > held + 0.001;
                document.getElementById('stlWarn').style.display = over ? 'block' : 'none';
                document.getElementById('stlSubmit').disabled = over;
            }

            document.getElementById('stlSubmit').addEventListener('click', function () {
                var self = this; self.disabled = true;
                $.ajax({
                    url: storeUrl, type: 'POST',
                    data: {
                        deductions: collectLines(),
                        refund_method: document.getElementById('stlMethod').value,
                        refund_reference: document.getElementById('stlReference').value,
                        refund_date: document.getElementById('stlDate').value,
                        notes: document.getElementById('stlNotes').value
                    },
                    success: function (res) {
                        if (window.toastr) toastr.success(res.message || '{{ __('Recorded.') }}');
                        setTimeout(function () { window.location.reload(); }, 900);
                    },
                    error: function (xhr) {
                        self.disabled = false;
                        var m = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || '{{ __('Could not record the settlement.') }}';
                        if (window.toastr) toastr.error(m);
                    }
                });
            });
        })();
    </script>
@endpush

@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('assets/js/custom/tenant-payment.js') }}"></script>
@endpush
