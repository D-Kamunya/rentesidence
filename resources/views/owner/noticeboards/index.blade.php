@extends('owner.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- Page Content Wrapper Start -->
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    {{-- ── Page Header ── --}}
                    <div class="nb-page-header">
                        <div class="nb-page-header__left">
                            <h1 class="nb-page-title">{{ $pageTitle }}</h1>
                            <ol class="nb-breadcrumb">
                                <li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                <li>
                                    <svg width="8" height="8" viewBox="0 0 8 8" fill="none"><path d="M2.5 1.5l3 2.5-3 2.5" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </li>
                                <li aria-current="page">{{ $pageTitle }}</li>
                            </ol>
                        </div>
                        <div class="nb-page-header__right">
                            <button type="button" class="nb-btn nb-btn--primary" id="add" title="{{ __('Add New Notice') }}">
                                <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><path d="M6.5 1.5v10M1.5 6.5h10" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                                {{ __('Add New Notice') }}
                            </button>
                        </div>
                    </div>

                    {{-- ── Table Card ── --}}
                    <div class="nb-card">
                        <div class="nb-card__head">
                            <span class="nb-card__head-title">{{ __('All Notices') }}</span>
                        </div>
                        <div class="nb-card__body">
                            <table id="allNoticeDataTable" class="table nb-table dt-responsive">
                                <thead>
                                    <tr>
                                        <th>{{ __('SL') }}</th>
                                        <th>{{ __('Notice Title') }}</th>
                                        <th>{{ __('Property') }}</th>
                                        <th>{{ __('Details') }}</th>
                                        <th>{{ __('Start Date') }}</th>
                                        <th>{{ __('End Date') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                </div>{{-- /page-content-wrapper --}}
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         Add New Notice Modal
    ══════════════════════════════════════════ --}}
    <div class="modal fade" id="addNoticeModal" tabindex="-1" aria-labelledby="addNoticeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered nb-modal-dialog">
            <div class="modal-content nb-modal">
                <div class="nb-modal__header">
                    <div>
                        <p class="nb-modal__eyebrow">{{ __('Notice Board') }}</p>
                        <h4 class="nb-modal__title" id="addNoticeModalLabel">{{ __('Add New Notice') }}</h4>
                    </div>
                    <button type="button" class="nb-modal__close" data-bs-dismiss="modal" aria-label="Close">
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><path d="M1.5 1.5l10 10M11.5 1.5l-10 10" stroke="#374151" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <form class="ajax" action="{{ route('owner.noticeboard.store') }}" method="POST"
                    enctype="multipart/form-data" data-handler="getShowMessage">
                    <div class="modal-body nb-modal__body">
                        <div class="nb-form-group">
                            <label class="nb-label">{{ __('Notice Title') }}</label>
                            <input type="text" name="title" class="nb-input title"
                                placeholder="{{ __('Notice Title') }}">
                        </div>

                        <div class="nb-form-row">
                            <div class="nb-form-group">
                                <label class="nb-label">{{ __('Property Name') }}</label>
                                <select class="nb-select property_id" name="property_id">
                                    <option value="" selected>-- {{ __('Select Property') }} --</option>
                                    @foreach ($properties as $property)
                                        <option value="{{ $property->id }}"
                                            data-units="{{ $property->propertyUnits }}">{{ $property->name }}</option>
                                    @endforeach
                                </select>
                                <div class="nb-checkbox-wrap">
                                    <input type="checkbox" id="addCheckAllProperty" name="all_property" class="nb-checkbox">
                                    <label for="addCheckAllProperty">{{ __('All Property') }}</label>
                                </div>
                            </div>

                            <div class="nb-form-group">
                                <label class="nb-label">{{ __('Unit Name') }}</label>
                                <select class="nb-select unit_id" name="unit_id" id="unitOption">
                                    <option value="" selected>-- {{ __('Select Unit') }} --</option>
                                </select>
                                <div class="nb-checkbox-wrap">
                                    <input type="checkbox" id="addCheckAllUnit" name="all_unit" class="nb-checkbox">
                                    <label for="addCheckAllUnit">{{ __('All Unit') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="nb-form-group">
                            <label class="nb-label">{{ __('Details') }}</label>
                            <textarea class="nb-textarea details" name="details"
                                placeholder="{{ __('Write details here...') }}"></textarea>
                        </div>

                        <div class="nb-form-row">
                            <div class="nb-form-group">
                                <label class="nb-label">{{ __('Start Date') }}</label>
                                <div class="nb-datepicker custom-datepicker">
                                    <div class="custom-datepicker-inner position-relative">
                                        <input type="text" class="datepicker nb-input start_date"
                                            name="start_date" autocomplete="off" placeholder="dd-mm-yy">
                                        <i class="ri-calendar-2-line nb-datepicker__icon"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="nb-form-group">
                                <label class="nb-label">{{ __('End Date') }}</label>
                                <div class="nb-datepicker custom-datepicker">
                                    <div class="custom-datepicker-inner position-relative">
                                        <input type="text" class="datepicker nb-input end_date"
                                            name="end_date" autocomplete="off" placeholder="dd-mm-yy">
                                        <i class="ri-calendar-2-line nb-datepicker__icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="nb-form-group">
                            <label class="nb-label">{{ __('Upload Files') }}</label>
                            <input class="nb-file-input" type="file" name="image" multiple>
                        </div>
                    </div>
                    <div class="nb-modal__footer">
                        <button type="button" class="nb-btn nb-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="nb-btn nb-btn--primary">{{ __('Save Notice') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         Edit Notice Modal
    ══════════════════════════════════════════ --}}
    <div class="modal fade" id="editNoticeModal" tabindex="-1" aria-labelledby="editNoticeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered nb-modal-dialog">
            <div class="modal-content nb-modal">
                <div class="nb-modal__header">
                    <div>
                        <p class="nb-modal__eyebrow">{{ __('Notice Board') }}</p>
                        <h4 class="nb-modal__title" id="editNoticeModalLabel">{{ __('Edit Notice') }}</h4>
                    </div>
                    <button type="button" class="nb-modal__close" data-bs-dismiss="modal" aria-label="Close">
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><path d="M1.5 1.5l10 10M11.5 1.5l-10 10" stroke="#374151" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <form class="ajax" action="{{ route('owner.noticeboard.store') }}" method="POST"
                    enctype="multipart/form-data" data-handler="getShowMessage">
                    <input type="hidden" name="id" id="id">
                    <div class="modal-body nb-modal__body">
                        <div class="nb-form-group">
                            <label class="nb-label">{{ __('Notice Title') }}</label>
                            <input type="text" name="title" class="nb-input title"
                                placeholder="{{ __('Notice Title') }}">
                        </div>

                        <div class="nb-form-row">
                            <div class="nb-form-group">
                                <label class="nb-label">{{ __('Property Name') }}</label>
                                <select class="nb-select property_id" name="property_id">
                                    <option value="">-- {{ __('Select Property') }} --</option>
                                    @foreach ($properties as $property)
                                        <option value="{{ $property->id }}"
                                            data-units="{{ $property->propertyUnits }}">{{ $property->name }}</option>
                                    @endforeach
                                </select>
                                <div class="nb-checkbox-wrap">
                                    <input type="checkbox" id="editCheckAllProperty" name="all_property" class="nb-checkbox">
                                    <label for="editCheckAllProperty">{{ __('All Property') }}</label>
                                </div>
                            </div>

                            <div class="nb-form-group">
                                <label class="nb-label">{{ __('Unit Name') }}</label>
                                <select class="nb-select unit_id" name="unit_id" id="unitOption">
                                    <option value="" selected>-- {{ __('Select Unit') }} --</option>
                                </select>
                                <div class="nb-checkbox-wrap">
                                    <input type="checkbox" id="editCheckAllUnit" name="all_unit" class="nb-checkbox">
                                    <label for="editCheckAllUnit">{{ __('All Unit') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="nb-form-group">
                            <label class="nb-label">{{ __('Details') }}</label>
                            <textarea class="nb-textarea details" name="details"
                                placeholder="{{ __('Write details here...') }}"></textarea>
                        </div>

                        <div class="nb-form-row">
                            <div class="nb-form-group">
                                <label class="nb-label">{{ __('Start Date') }}</label>
                                <div class="nb-datepicker custom-datepicker">
                                    <div class="custom-datepicker-inner position-relative">
                                        <input type="text" class="datepicker nb-input start_date"
                                            name="start_date" autocomplete="off" placeholder="dd-mm-yy">
                                        <i class="ri-calendar-2-line nb-datepicker__icon"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="nb-form-group">
                                <label class="nb-label">{{ __('End Date') }}</label>
                                <div class="nb-datepicker custom-datepicker">
                                    <div class="custom-datepicker-inner position-relative">
                                        <input type="text" class="datepicker nb-input end_date"
                                            name="end_date" autocomplete="off" placeholder="dd-mm-yy">
                                        <i class="ri-calendar-2-line nb-datepicker__icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="nb-form-group">
                            <label class="nb-label">{{ __('Upload Files') }}</label>
                            <input class="nb-file-input" type="file" name="image" multiple>
                        </div>
                    </div>
                    <div class="nb-modal__footer">
                        <button type="button" class="nb-btn nb-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="nb-btn nb-btn--primary">{{ __('Update Notice') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         View Notice Details Modal
    ══════════════════════════════════════════ --}}
    <div class="modal fade" id="viewNoticeBoardDetailsModal" tabindex="-1"
        aria-labelledby="viewNoticeBoardtDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered nb-modal-dialog">
            <div class="modal-content nb-modal">
                <div class="nb-modal__header">
                    <div>
                        <p class="nb-modal__eyebrow">{{ __('Notice Board') }}</p>
                        <h4 class="nb-modal__title" id="viewNoticeBoardtDetailsModalLabel">{{ __('Notice Details') }}</h4>
                    </div>
                    <button type="button" class="nb-modal__close" data-bs-dismiss="modal" aria-label="Close">
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><path d="M1.5 1.5l10 10M11.5 1.5l-10 10" stroke="#374151" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <div class="modal-body nb-modal__body nb-modal__body--view">

                    <div class="nb-view-field">
                        <span class="nb-view-field__label">{{ __('Notice Title') }}</span>
                        <p class="nb-view-field__value viewtitle"></p>
                    </div>

                    <div class="nb-form-row">
                        <div class="nb-view-field">
                            <span class="nb-view-field__label">{{ __('Property') }}</span>
                            <p class="nb-view-field__value nb-view-field__value--blue property"></p>
                        </div>
                        <div class="nb-view-field">
                            <span class="nb-view-field__label">{{ __('Unit') }}</span>
                            <p class="nb-view-field__value nb-view-field__value--blue unit"></p>
                        </div>
                    </div>

                    <div class="nb-view-field">
                        <span class="nb-view-field__label">{{ __('Details') }}</span>
                        <p class="nb-view-field__value details"></p>
                    </div>

                    <div class="nb-form-row">
                        <div class="nb-view-field">
                            <span class="nb-view-field__label">{{ __('Start Date') }}</span>
                            <p class="nb-view-field__value start_date"></p>
                        </div>
                        <div class="nb-view-field">
                            <span class="nb-view-field__label">{{ __('End Date') }}</span>
                            <p class="nb-view-field__value end_date"></p>
                        </div>
                    </div>

                    <div class="nb-view-field">
                        <span class="nb-view-field__label">{{ __('Attachment') }}</span>
                        <div class="nb-attachment">
                            <div class="nb-attachment__icon">
                                <img src="{{ asset('assets/images/file-text-line.svg') }}" alt="File" width="20">
                            </div>
                            <div class="nb-attachment__info">
                                <span>{{ __('Uploaded File') }}</span>
                                <a href="" class="nb-attachment__download image" title="{{ __('Download') }}" download="">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><path d="M6.5 1.5v7M3.5 6l3 3 3-3M1.5 11h10" stroke="#185FA5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Hidden route inputs (required by noticeboard.js) --}}
    <input type="hidden" id="getInfoRoute" value="{{ route('owner.noticeboard.get.info') }}">
    <input type="hidden" id="route" value="{{ route('owner.noticeboard.index') }}">

@endsection

@push('style')
    @include('common.layouts.datatable-style')
    <style>
        /* ═══════════════════════════════════════════════
           NOTICE BOARD — Centresidence UI Design System
        ═══════════════════════════════════════════════ */

        /* ── Tokens ── */
        :root {
            --blue:          #185FA5;
            --blue-hover:    #0F4A84;
            --blue-light:    #E6F1FB;
            --blue-border:   #B5D4F4;
            --blue-faint:    #185ea56e;
            --blue-ghost:    #185ea51c;
            --green:         #1D9E75;
            --green-dark:    #0F6E56;
            --green-light:   #E1F5EE;
            --amber:         #854F0B;
            --amber-light:   #FAEEDA;
            --amber-border:  #F5D9A8;
            --red:           #993C1D;
            --red-light:     #FAECE7;
            --purple:        #534AB7;
            --purple-hover:  #3C3489;
            --gray-900:      #111827;
            --gray-800:      #1f2937;
            --gray-700:      #374151;
            --gray-500:      #6b7280;
            --gray-400:      #9ca3af;
            --gray-200:      #e5e7eb;
            --gray-100:      #f3f4f6;
            --gray-50:       #fafafa;
            --white:         #ffffff;
        }

        /* ── Page Header ── */
        .nb-page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding-bottom: 20px;
            margin-bottom: 24px;
            border-bottom: 0.5px solid var(--gray-200);
        }
        .nb-page-title {
            font-size: 22px;
            font-weight: 500;
            color: var(--gray-900);
            margin: 0 0 4px;
            line-height: 1.2;
        }
        .nb-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
            margin: 0;
            padding: 0;
            font-size: 12px;
            color: var(--gray-400);
        }
        .nb-breadcrumb li { display: flex; align-items: center; }
        .nb-breadcrumb a { color: var(--blue); font-weight: 500; text-decoration: none; }
        .nb-breadcrumb a:hover { text-decoration: underline; }

        /* ── Buttons ── */
        .nb-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 500;
            padding: 7px 15px;
            border-radius: 7px;
            border: none;
            cursor: pointer;
            transition: all .13s;
            white-space: nowrap;
            text-decoration: none;
        }
        .nb-btn--primary {
            background: var(--blue);
            color: var(--white);
        }
        .nb-btn--primary:hover {
            background: var(--blue-hover);
            transform: translateY(-1px);
            color: var(--white);
        }
        .nb-btn--ghost {
            background: var(--gray-100);
            color: var(--gray-700);
            border: 0.5px solid var(--gray-200);
        }
        .nb-btn--ghost:hover {
            background: var(--gray-200);
            color: var(--gray-700);
        }

        /* ── Table Card ── */
        .nb-card {
            background: var(--white);
            border: 0.5px solid var(--blue-faint);
            border-radius: 12px;
            overflow: hidden;
            box-shadow:
                0 4px 12px rgba(0,0,0,.04),
                0 0 0 1px rgba(24,95,165,.05),
                0 6px 18px rgba(24,95,165,.06);
        }
        .nb-card__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .75rem 1.1rem;
            border-bottom: 0.5px solid var(--gray-200);
            background: var(--gray-50);
        }
        .nb-card__head-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-900);
        }
        .nb-card__body {
            padding: 0;
            overflow-x: auto;
        }

        /* ── DataTable overrides ── */
        .nb-table {
            width: 100% !important;
            margin: 0 !important;
            border-collapse: collapse;
        }
        .nb-table thead tr {
            background: var(--gray-50);
        }
        .nb-table thead th {
            font-size: 10px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--gray-500);
            padding: .65rem 1rem;
            border-bottom: 0.5px solid var(--gray-200);
            white-space: nowrap;
        }
        .nb-table tbody td {
            font-size: 13px;
            color: var(--gray-700);
            padding: .8rem 1rem;
            border-bottom: 0.5px solid var(--gray-100);
            vertical-align: middle;
        }
        .nb-table tbody tr:last-child td { border-bottom: none; }
        .nb-table tbody tr:nth-child(even) td { background: var(--gray-50); }
        .nb-table tbody tr:hover td { background: var(--gray-100); }

        /* DataTables wrapper fixes */
        .dataTables_wrapper {
            padding: 0;
        }
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_length {
            padding: 12px 16px 0;
            font-size: 12px;
            color: var(--gray-500);
        }
        .dataTables_wrapper .dataTables_filter input {
            border: 0.5px solid var(--gray-200);
            border-radius: 7px;
            padding: 5px 10px;
            font-size: 12px;
            color: var(--gray-700);
            outline: none;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(24,95,165,.1);
        }
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            padding: 12px 16px;
            font-size: 12px;
            color: var(--gray-500);
            border-top: 0.5px solid var(--gray-200);
            background: var(--gray-50);
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            min-width: 32px;
            height: 32px;
            border-radius: 7px;
            font-size: 12px;
            padding: 4px 8px !important;
            border: 0.5px solid var(--gray-200) !important;
            color: var(--gray-700) !important;
            margin: 0 2px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--blue) !important;
            color: var(--white) !important;
            border-color: var(--blue) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
            background: var(--blue-light) !important;
            color: var(--blue) !important;
            border-color: var(--blue-border) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            color: var(--gray-400) !important;
            background: var(--gray-50) !important;
        }

        /* ── Modal ── */
        .nb-modal-dialog { max-width: 480px; }
        .nb-modal {
            background: var(--white);
            border-radius: 14px;
            border: 0.5px solid var(--gray-200);
            overflow: hidden;
        }
        .nb-modal__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 18px 20px 14px;
            background: var(--gray-50);
            border-bottom: 0.5px solid var(--gray-200);
        }
        .nb-modal__eyebrow {
            font-size: 10px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--gray-400);
            margin: 0 0 3px;
        }
        .nb-modal__title {
            font-size: 15px;
            font-weight: 600;
            color: var(--gray-900);
            margin: 0;
        }
        .nb-modal__close {
            width: 30px;
            height: 30px;
            border-radius: 7px;
            background: var(--gray-100);
            border: 0.5px solid var(--gray-200);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .13s;
            flex-shrink: 0;
        }
        .nb-modal__close:hover { background: var(--gray-200); }

        .nb-modal__body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .nb-modal__body--view { gap: 14px; }

        .nb-modal__footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            padding: 14px 20px;
            background: var(--gray-50);
            border-top: 0.5px solid var(--gray-200);
        }

        /* ── Form Controls ── */
        .nb-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .nb-form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .nb-label {
            font-size: 10px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--gray-400);
        }
        .nb-input,
        .nb-select,
        .nb-textarea {
            border: 0.5px solid var(--gray-200);
            border-radius: 7px;
            padding: 8px 11px;
            font-size: 13px;
            color: var(--gray-700);
            background: var(--white);
            outline: none;
            width: 100%;
            transition: border-color .13s, box-shadow .13s;
            font-family: inherit;
        }
        .nb-input::placeholder,
        .nb-textarea::placeholder { color: var(--gray-400); }
        .nb-input:focus,
        .nb-select:focus,
        .nb-textarea:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(24,95,165,.1);
        }
        .nb-textarea {
            resize: vertical;
            min-height: 90px;
        }
        .nb-select { appearance: auto; }

        /* Datepicker icon */
        .nb-datepicker__icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            pointer-events: none;
            font-size: 14px;
        }
        .nb-datepicker .nb-input { padding-right: 32px; }

        /* Checkbox */
        .nb-checkbox-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            font-size: 12px;
            font-weight: 500;
            color: var(--gray-700);
        }
        .nb-checkbox {
            width: 14px;
            height: 14px;
            accent-color: var(--blue);
            cursor: pointer;
            flex-shrink: 0;
        }

        /* File input */
        .nb-file-input {
            border: 0.5px solid var(--gray-200);
            border-radius: 7px;
            padding: 7px 11px;
            font-size: 12px;
            color: var(--gray-500);
            width: 100%;
            background: var(--gray-50);
            cursor: pointer;
        }
        .nb-file-input:hover { border-color: var(--blue-border); }

        /* ── View modal fields ── */
        .nb-view-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .nb-view-field__label {
            font-size: 10px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--gray-400);
        }
        .nb-view-field__value {
            font-size: 13px;
            color: var(--gray-700);
            margin: 0;
            line-height: 1.5;
        }
        .nb-view-field__value--blue {
            color: var(--blue);
            font-weight: 500;
        }

        /* Attachment row */
        .nb-attachment {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border: 0.5px solid var(--gray-200);
            border-radius: 7px;
            background: var(--gray-50);
        }
        .nb-attachment__icon {
            width: 36px;
            height: 36px;
            background: var(--blue-light);
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .nb-attachment__info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: 12px;
            color: var(--gray-500);
        }
        .nb-attachment__download {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 500;
            color: var(--blue);
            text-decoration: none;
        }
        .nb-attachment__download:hover { text-decoration: underline; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .nb-page-header { flex-direction: column; gap: 10px; }
            .nb-modal-dialog { max-width: 96vw; margin: 10px auto; }
        }
        @media (max-width: 540px) {
            .nb-form-row { grid-template-columns: 1fr; }
            .nb-modal__body { padding: 16px; }
        }
    </style>
@endpush

@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('assets/js/custom/noticeboard.js') }}"></script>
@endpush