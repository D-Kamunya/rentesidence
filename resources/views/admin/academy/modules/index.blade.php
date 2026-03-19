@extends('admin.layouts.app')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    {{-- Page Title --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">Centresidence Academy</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('admin.dashboard') }}" title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a>
                                        </li>
                                        <li class="breadcrumb-item active" aria-current="page">Centresidence Academy</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container mt-4">

                        {{-- Header row --}}
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-0" style="font-weight:500;">Academy Modules</h5>
                                <p class="mb-0 mt-1" style="font-size:13px;color:#9ca3af;">
                                    {{ $modules->count() }} {{ Str::plural('module', $modules->count()) }} total
                                </p>
                            </div>
                            <a href="{{ route('admin.academy.create') }}" class="adm-btn-primary">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                                Add Module
                            </a>
                        </div>

                        {{-- Flash --}}
                        @if(session('success'))
                            <div class="mod-alert mod-alert--success mb-4">
                                <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                    <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div>{{ session('success') }}</div>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" style="font-size:11px;"></button>
                            </div>
                        @endif

                        {{-- Table card --}}
                        <div class="adm-card">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr style="border-bottom:0.5px solid #e5e7eb;background:#fafafa;">
                                            <th class="adm-th" style="width:60px;">#</th>
                                            <th class="adm-th">Title</th>
                                            <th class="adm-th">Status</th>
                                            <th class="adm-th" style="width:160px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($modules as $module)
                                            <tr style="border-bottom:0.5px solid #f3f4f6;">

                                                {{-- Order --}}
                                                <td class="adm-td">
                                                    <span class="adm-order-chip">{{ $module->module_order }}</span>
                                                </td>

                                                {{-- Title --}}
                                                <td class="adm-td">
                                                    <span style="font-weight:500;font-size:14px;color:#111827;">
                                                        {{ $module->title }}
                                                    </span>
                                                </td>

                                                {{-- Status --}}
                                                <td class="adm-td">
                                                    @if($module->is_active)
                                                        <span class="adm-badge adm-badge--active">Active</span>
                                                    @else
                                                        <span class="adm-badge adm-badge--inactive">Inactive</span>
                                                    @endif
                                                </td>

                                                {{-- Actions --}}
                                                <td class="adm-td">
                                                    <div class="d-flex align-items-center gap-2">

                                                        {{-- Manage Questions --}}
                                                        <a href="{{ route('admin.academy.questions', $module->id) }}"
                                                           class="adm-icon-btn adm-icon-btn--blue"
                                                           data-bs-toggle="tooltip"
                                                           title="Manage Questions">
                                                            <i class="bi bi-list-check"></i>
                                                        </a>

                                                        {{-- Edit --}}
                                                        <a href="{{ route('admin.academy.edit', $module->id) }}"
                                                           class="adm-icon-btn adm-icon-btn--amber"
                                                           data-bs-toggle="tooltip"
                                                           title="Edit Module">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </a>

                                                        {{-- Delete --}}
                                                        <form action="{{ route('admin.academy.destroy', $module->id) }}"
                                                              method="POST"
                                                              class="d-inline"
                                                              onsubmit="return confirm('Delete this module?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="adm-icon-btn adm-icon-btn--red"
                                                                    data-bs-toggle="tooltip"
                                                                    title="Delete Module">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>

                                                    </div>
                                                </td>

                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5" style="color:#9ca3af;font-size:14px;">
                                                    No modules created yet.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ── Alert ───────────────────────────────────────────── */
        .mod-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: .8rem 1rem;
            border-radius: 10px;
            font-size: 14px;
        }
        .mod-alert--success { background: #E1F5EE; color: #0F6E56; }

        /* ── Primary button ──────────────────────────────────── */
        .adm-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            background: #185FA5;
            color: #fff;
            font-size: 13px;
            font-weight: 500;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            transition: background .2s, transform .2s, box-shadow .2s;
        }
        .adm-btn-primary:hover {
            background: #0C447C;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(24,95,165,.22);
        }

        /* ── Table card ──────────────────────────────────────── */
        .adm-card {
            border: 0.5px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }
        .adm-th {
            padding: .8rem 1.1rem;
            font-size: 11px;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .06em;
            border: none;
        }
        .adm-td {
            padding: .85rem 1.1rem;
            border: none;
        }

        /* ── Order chip ──────────────────────────────────────── */
        .adm-order-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: #f3f4f6;
            color: #374151;
            font-size: 12px;
            font-weight: 500;
            border-radius: 6px;
        }

        /* ── Status badges ───────────────────────────────────── */
        .adm-badge {
            display: inline-flex;
            align-items: center;
            font-size: 11px;
            font-weight: 500;
            padding: 3px 10px;
            border-radius: 99px;
        }
        .adm-badge--active   { background: #E1F5EE; color: #0F6E56; }
        .adm-badge--inactive { background: #f3f4f6; color: #6b7280; }

        /* ── Icon action buttons ─────────────────────────────── */
        .adm-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 7px;
            border: 0.5px solid transparent;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            background: transparent;
            transition: background .15s, border-color .15s, transform .15s;
        }
        .adm-icon-btn:hover { transform: translateY(-1px); }

        .adm-icon-btn--blue  { color: #185FA5; border-color: #B5D4F4; }
        .adm-icon-btn--blue:hover  { background: #E6F1FB; color: #185FA5; }

        .adm-icon-btn--amber { color: #854F0B; border-color: #FAC775; }
        .adm-icon-btn--amber:hover { background: #FEF9EE; color: #854F0B; }

        .adm-icon-btn--red   { color: #A32D2D; border-color: #F7C1C1; }
        .adm-icon-btn--red:hover   { background: #FCEBEB; color: #A32D2D; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el) })
        });
    </script>
@endsection