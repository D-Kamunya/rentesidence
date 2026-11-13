@extends('admin.layouts.app')

@section('content')
@php
    $name   = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->email ?? __('Owner'));
    $isActive = (int) $user->status === USER_STATUS_ACTIVE;
@endphp
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-3 p-md-4 radius-20">

            {{-- Header --}}
            <div class="row mb-3">
                <div class="col-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h4 class="mb-0" style="font-size:22px;font-weight:500;color:#111827;">{{ $name }}</h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0" style="display:flex;gap:6px;font-size:12px;color:#9ca3af;list-style:none;padding:0;margin-top:4px;">
                                    <li><a href="{{ route('admin.dashboard') }}" style="color:#185FA5;font-weight:500;">Dashboard</a></li>
                                    <li>/</li>
                                    <li><a href="{{ route('admin.owner.index') }}" style="color:#185FA5;font-weight:500;">Owners</a></li>
                                    <li>/</li>
                                    <li style="color:#9ca3af;">{{ Str::limit($name, 28) }}</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn js-message-user"
                                data-user-id="{{ $user->id }}" data-user-name="{{ $name }}"
                                style="background:#185FA5;color:#fff;display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:500;padding:8px 15px;border-radius:7px;border:none;cursor:pointer;">
                                <svg width="13" height="13" viewBox="0 0 16 16" fill="none"><rect x="2" y="3.5" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="1.3"/><path d="M3 5l5 3.5L13 5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                {{ __('Message owner') }}
                            </button>
                            <a href="{{ route('admin.owner.index') }}" class="btn"
                                style="background:#f3f4f6;color:#374151;font-size:12px;font-weight:500;padding:8px 15px;border-radius:7px;border:0.5px solid #e5e7eb;text-decoration:none;">{{ __('Back to owners') }}</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Identity --}}
                <div class="col-lg-4 mb-4">
                    <div class="ow-card" style="background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;padding:24px;box-shadow:0 4px 12px rgba(0,0,0,0.04);">
                        <div style="width:60px;height:60px;border-radius:14px;background:linear-gradient(135deg,#185FA5,#2E7FC7);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:600;color:#fff;margin-bottom:14px;">
                            {{ strtoupper(substr($name, 0, 1)) }}
                        </div>
                        <h3 style="font-size:17px;font-weight:600;color:#111827;margin-bottom:2px;">{{ $name }}</h3>
                        <span style="display:inline-block;font-size:11px;font-weight:600;padding:2px 10px;border-radius:99px;margin-bottom:16px;{{ $isActive ? 'background:#E1F5EE;color:#0F6E56;' : 'background:#FAEEDA;color:#854F0B;' }}">
                            {{ $isActive ? __('Active') : __('Pending email verification') }}
                        </span>
                        @php
                            $rows = [
                                [__('Email'), $user->email],
                                [__('Phone'), $user->contact_number ?: '—'],
                                [__('Joined'), optional($user->created_at)->format('M d, Y')],
                                [__('Last activity'), $lastActivity ? $lastActivity->diffForHumans() : '—'],
                                [__('Referred by'), $affiliateName ?: __('Direct / website')],
                            ];
                        @endphp
                        @foreach($rows as [$k, $v])
                            <div style="display:flex;justify-content:space-between;gap:12px;padding:9px 0;border-top:1px solid #f3f4f6;font-size:13px;">
                                <span style="color:#9ca3af;">{{ $k }}</span>
                                <span style="color:#374151;font-weight:500;text-align:right;word-break:break-word;">{{ $v }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Uptake + setup --}}
                <div class="col-lg-8 mb-4">
                    {{-- Stat tiles --}}
                    <div class="row g-3 mb-1">
                        @php
                            $tiles = [
                                [__('Properties'), $properties, '#185FA5', '#E6F1FB'],
                                [__('Units'), $units, '#534AB7', '#EDEBFB'],
                                [__('Tenants'), $tenants, '#0F6E56', '#E1F5EE'],
                                [__('Rent collected'), $hasRent ? __('Yes') : __('Not yet'), $hasRent ? '#0F6E56' : '#9ca3af', $hasRent ? '#E1F5EE' : '#f3f4f6'],
                            ];
                        @endphp
                        @foreach($tiles as [$label, $val, $color, $bg])
                            <div class="col-6 col-md-3">
                                <div class="ow-card" style="background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;padding:18px 16px;box-shadow:0 4px 12px rgba(0,0,0,0.04);height:100%;">
                                    <div style="font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;margin-bottom:8px;">{{ $label }}</div>
                                    <div style="font-size:26px;font-weight:600;color:{{ $color }};line-height:1;">{{ $val }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Setup progress --}}
                    <div class="ow-card mt-3" style="background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;padding:22px 24px;box-shadow:0 4px 12px rgba(0,0,0,0.04);">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 style="font-size:15px;font-weight:600;color:#111827;margin:0;">{{ __('Setup progress') }}</h5>
                            <span style="font-size:12px;font-weight:600;color:{{ $stepsDone === 4 ? '#0F6E56' : '#854F0B' }};background:{{ $stepsDone === 4 ? '#E1F5EE' : '#FAEEDA' }};padding:3px 11px;border-radius:99px;">{{ $stepsDone }}/4 {{ __('done') }}</span>
                        </div>
                        @php
                            $steps = [
                                ['property', __('Added a property'), __('They haven\'t added a property yet.')],
                                ['tenant',   __('Added a tenant'),   __('No tenants added yet.')],
                                ['payment',  __('Set up how they get paid'), __('No rent-receiving account yet — rent can\'t be collected until they add their M-Pesa Till/Paybill.')],
                                ['rent',     __('Collected first rent'), __('No rent collected through the system yet.')],
                            ];
                        @endphp
                        @foreach($steps as [$key, $doneLabel, $todoLabel])
                            @php $done = $setup[$key]; @endphp
                            <div style="display:flex;align-items:center;gap:12px;padding:11px 0;border-top:1px solid #f3f4f6;">
                                <span style="flex:none;width:26px;height:26px;border-radius:50%;display:grid;place-items:center;{{ $done ? 'background:#E1F5EE;color:#0F6E56;' : 'background:#f3f4f6;color:#c0c6cf;' }}">
                                    @if($done)
                                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none"><path d="M4 8.5l2.5 2.5 5-5.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @else
                                        <svg width="13" height="13" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.4"/></svg>
                                    @endif
                                </span>
                                <div>
                                    <div style="font-size:14px;font-weight:500;color:{{ $done ? '#111827' : '#6b7280' }};">{{ $doneLabel }}</div>
                                    @unless($done)<div style="font-size:12px;color:#9ca3af;">{{ $todoLabel }}</div>@endunless
                                </div>
                            </div>
                        @endforeach

                        @if($stepsDone < 4)
                            <div style="margin-top:16px;background:#FFF8EC;border:0.5px solid #F3E2C0;border-radius:10px;padding:12px 14px;font-size:13px;color:#854F0B;display:flex;align-items:flex-start;gap:9px;">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;margin-top:1px;"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 5v3.5M8 11h.01" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                                <span>{{ __('This owner may be stuck in setup. Reach out with the') }} <strong>{{ __('Message owner') }}</strong> {{ __('button to help them get going.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            </div>
        </div>
    </div>
</div>

@include('admin.partials.user-message-modal')
@endsection
