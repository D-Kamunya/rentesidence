@extends('admin.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('admin.centresidence._nav', ['active' => 'integrations'])

        @if (session('success')) <div class="cs-alert is-success">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif
        @if ($errors->any()) <div class="cs-alert is-danger">{{ $errors->first() }}</div> @endif

        {{-- Operational drivers (DB-backed, .env fallback) --}}
        <form method="POST" action="{{ route('admin.centresidence.integrations.save') }}">
            @csrf
            <div class="cs-card">
                <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Operational mode') }}</h2></div>
                <div class="cs-card__body cs-muted" style="font-size:13px;">
                    {{ __('Flip these to go live without editing the server’s .env. A saved value here wins; if left unset, the .env/default applies (the fallback). Secrets below stay in .env only.') }}
                </div>
                <div class="cs-card__body" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px;">
                    <div class="cs-field">
                        <label class="cs-label">{{ __('Collections (STK)') }}</label>
                        <select name="collection_driver" class="cs-input">
                            <option value="log" @selected($drivers['collection'] === 'log')>{{ __('Log / simulate (test)') }}</option>
                            <option value="mpesa" @selected($drivers['collection'] === 'mpesa')>{{ __('M-Pesa — live') }}</option>
                        </select>
                        <div class="cs-help">{{ __('Down-payments, early settlement, infra bills, token top-ups. Live prompts the payer’s phone.') }}</div>
                    </div>
                    <div class="cs-field">
                        <label class="cs-label">{{ __('Partner payouts') }}</label>
                        <select name="payout_driver" class="cs-input">
                            <option value="log" @selected($drivers['payout'] === 'log')>{{ __('Log / simulate (test)') }}</option>
                            <option value="mpesa" @selected($drivers['payout'] === 'mpesa')>{{ __('M-Pesa B2B — live') }}</option>
                        </select>
                        <div class="cs-help">{{ __('Remittances to partners via B2B to their paybill/bank. Live moves real money.') }}</div>
                    </div>
                    <div class="cs-field">
                        <label class="cs-label">{{ __('LoRaWAN (ChirpStack)') }}</label>
                        <select name="chirpstack_driver" class="cs-input">
                            <option value="simulated" @selected($drivers['chirpstack'] === 'simulated')>{{ __('Simulated (test)') }}</option>
                            <option value="live" @selected($drivers['chirpstack'] === 'live')>{{ __('Live — real gateways/meters') }}</option>
                        </select>
                        <div class="cs-help">{{ __('Live registers gateways/devices in ChirpStack and processes real meter uplinks.') }}</div>
                    </div>
                </div>
                <div class="cs-card__body"><button type="submit" class="cs-btn cs-btn--primary">{{ __('Save operational mode') }}</button></div>
            </div>
        </form>

        {{-- Secret status (read-only; values live in .env) --}}
        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Credentials status') }}</h2></div>
            <div class="cs-card__body cs-muted" style="font-size:13px;">
                {{ __('For security these live in the server .env and are never shown or edited here — this only confirms what’s configured. Set any missing ones before switching the matching mode to live.') }}
            </div>
            <div class="cs-tablewrap">
                <table class="cs-table">
                    <thead><tr><th>{{ __('Credential') }}</th><th>{{ __('Used for') }}</th><th>{{ __('Status') }}</th></tr></thead>
                    <tbody>
                        @foreach ($secrets as [$name, $set, $usedFor])
                            <tr>
                                <td>{{ $name }}</td>
                                <td class="cs-muted" style="font-size:12px;">{{ $usedFor }}</td>
                                <td>
                                    @if ($set)
                                        <span class="cs-badge is-paid">{{ __('Configured') }}</span>
                                    @else
                                        <span class="cs-badge is-danger">{{ __('Missing') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div></div></div>

<style>.cs-help { font-size:11.5px; color:#6b7280; line-height:1.5; margin-top:5px; }</style>
@endsection
