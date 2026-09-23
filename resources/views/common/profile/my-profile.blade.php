@extends(getLayout() . '.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- Header --}}
                <div class="pf-header">
                    <div>
                        <h2 class="pf-title">{{ $pageTitle }}</h2>
                        <ol class="pf-crumb">
                            <li><a href="{{ route(getLayout() . '.dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li>›</li>
                            <li>{{ __('Profile') }}</li>
                        </ol>
                    </div>
                    @if (auth()->user()->role == USER_ROLE_TENANT || auth()->user()->role == USER_ROLE_MAINTAINER)
                        <button type="button" class="pf-btn pf-btn--danger-ghost" id="deleteMyAccountBtn"
                            title="{{ __('Delete my account') }}">
                            <i class="ri-delete-bin-line"></i> {{ __('Delete my Account') }}
                        </button>
                    @endif
                </div>

                <form action="{{ route('profile.update') }}" method="post" enctype="multipart/form-data">
                    @csrf

                    {{-- Personal Information --}}
                    <div class="pf-card">
                        <div class="pf-card__head">
                            <span class="pf-card__ic"><i class="ri-user-3-line"></i></span>
                            <h3 class="pf-card__title">{{ __('Personal Information') }}</h3>
                        </div>
                        <div class="pf-card__body">
                            <div class="pf-idrow">
                                <div class="pf-avatar">
                                    <div class="profile-user position-relative d-inline-block">
                                        <img src="{{ auth()->user()->image }}"
                                            class="rounded-circle default-user-profile-image pf-avatar__img">
                                        <div class="default-profile-photo-edit pf-avatar__edit">
                                            <input id="default-profile-img-file-input" type="file" name="image"
                                                class="default-profile-img-file-input js-image-resize">
                                            <label for="default-profile-img-file-input"
                                                class="default-profile-photo-edit pf-avatar__badge" title="{{ __('Change Image') }}">
                                                <i class="ri-camera-fill"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="pf-avatar__meta">
                                        <p class="pf-avatar__name">{{ auth()->user()->name }}</p>
                                        <p class="pf-avatar__hint">{{ __('JPG or PNG. Click the camera to change.') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="pf-grid">
                                @php
                                    $u = auth()->user();
                                    $fields = [
                                        ['first_name', __('First Name'), 'text', $u->first_name],
                                        ['last_name', __('Last Name'), 'text', $u->last_name],
                                        ['email', __('Email'), 'email', $u->email],
                                        ['contact_number', __('Contact Number'), 'text', $u->contact_number],
                                        ['date_of_birth', __('Date of birth'), 'date', $u->date_of_birth],
                                        ['nid_number', __('NID Number'), 'text', $u->nid_number],
                                    ];
                                @endphp
                                @foreach ($fields as [$name, $label, $type, $value])
                                    <div class="pf-field">
                                        <label class="pf-label">{{ $label }}</label>
                                        <input type="{{ $type }}" class="pf-input @error($name) is-invalid @enderror"
                                            name="{{ $name }}" placeholder="{{ $label }}" value="{{ $value }}">
                                        @error($name)<span class="pf-err">{{ $message }}</span>@enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if (auth()->user()->role == USER_ROLE_OWNER)
                        {{-- Print Details (owner) --}}
                        <div class="pf-card">
                            <div class="pf-card__head">
                                <span class="pf-card__ic"><i class="ri-printer-line"></i></span>
                                <h3 class="pf-card__title">{{ __('Print Details') }}</h3>
                                <span class="pf-card__sub">{{ __('Shown on invoices & receipts') }}</span>
                            </div>
                            <div class="pf-card__body">
                                <div class="pf-grid">
                                    <div class="pf-field">
                                        <label class="pf-label">{{ __('Print Name') }}</label>
                                        <input type="text" class="pf-input @error('print_name') is-invalid @enderror"
                                            value="{{ $owner->print_name }}" name="print_name" placeholder="{{ __('Name') }}">
                                        @error('print_name')<span class="pf-err">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="pf-field">
                                        <label class="pf-label">{{ __('Print Address') }}</label>
                                        <input type="text" class="pf-input @error('print_address') is-invalid @enderror"
                                            value="{{ $owner->print_address }}" name="print_address" placeholder="{{ __('Address') }}">
                                        @error('print_address')<span class="pf-err">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="pf-field">
                                        <label class="pf-label">{{ __('Print Contact') }}</label>
                                        <input type="text" class="pf-input @error('print_contact') is-invalid @enderror"
                                            value="{{ $owner->print_contact }}" name="print_contact" placeholder="{{ __('Contact') }}">
                                        @error('print_contact')<span class="pf-err">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="pf-field">
                                        <label class="pf-label">{{ __('Print Logo') }}</label>
                                        <div class="pf-logo">
                                            <div class="profile-user position-relative d-inline-block">
                                                @if ($owner->file_name)
                                                    <img src="{{ assetUrl($owner->folder_name . '/' . $owner->file_name) }}"
                                                        class="user-profile-image pf-logo__img">
                                                @else
                                                    <img src="{{ asset('assets/images/users/empty-user.jpg') }}"
                                                        class="user-profile-image pf-logo__img">
                                                @endif
                                                <div class="profile-photo-edit pf-avatar__edit">
                                                    <input id="profile-img-file-input" name="print_logo" type="file"
                                                        class="profile-img-file-input js-image-resize">
                                                    <label for="profile-img-file-input"
                                                        class="profile-photo-edit pf-avatar__badge" title="{{ __('Upload Image') }}">
                                                        <i class="ri-camera-fill"></i>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (auth()->user()->role == USER_ROLE_TENANT)
                        @php
                            $tenantGroups = [
                                [__('Previous Address'), 'ri-map-pin-line', [
                                    ['previous_address', __('Address'), $details->previous_address],
                                    ['previous_country_id', __('Country'), $details->previous_country_id],
                                    ['previous_state_id', __('State'), $details->previous_state_id],
                                    ['previous_city_id', __('City'), $details->previous_city_id],
                                    ['previous_zip_code', __('Zip Code'), $details->previous_zip_code],
                                ]],
                                [__('Permanent Address'), 'ri-home-4-line', [
                                    ['permanent_address', __('Address'), $details->permanent_address],
                                    ['permanent_country_id', __('Country'), $details->permanent_country_id],
                                    ['permanent_state_id', __('State'), $details->permanent_state_id],
                                    ['permanent_city_id', __('City'), $details->permanent_city_id],
                                    ['permanent_zip_code', __('Zip Code'), $details->permanent_zip_code],
                                ]],
                                [__('Other Information'), 'ri-briefcase-line', [
                                    ['job', __('Employment'), $tenant->job],
                                    ['family_member', __('Family Member'), $tenant->family_member],
                                    ['age', __('Age'), $tenant->age],
                                ]],
                            ];
                        @endphp
                        @foreach ($tenantGroups as [$title, $icon, $groupFields])
                            <div class="pf-card">
                                <div class="pf-card__head">
                                    <span class="pf-card__ic"><i class="{{ $icon }}"></i></span>
                                    <h3 class="pf-card__title">{{ $title }}</h3>
                                </div>
                                <div class="pf-card__body">
                                    <div class="pf-grid">
                                        @foreach ($groupFields as [$name, $label, $value])
                                            <div class="pf-field">
                                                <label class="pf-label">{{ $label }}</label>
                                                <input type="text" class="pf-input @error($name) is-invalid @enderror"
                                                    name="{{ $name }}" placeholder="{{ $label }}" value="{{ $value }}">
                                                @error($name)<span class="pf-err">{{ $message }}</span>@enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    @if (auth()->user()->role == USER_ROLE_OWNER)
                        @include('owner.partials.credit-status')
                    @endif

                    <div class="pf-actions">
                        <button type="submit" class="pf-btn pf-btn--primary" title="{{ __('Update') }}">
                            <i class="ri-check-line"></i> {{ __('Update') }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

{{-- Delete account modal (functionality unchanged) --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content pf-modal">
            <div class="modal-header pf-modal__head">
                <h4 class="modal-title" id="deleteModalLabel">{{ __('Delete Account') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="ajax" action="{{ route('delete-my-account') }}" method="POST" autocomplete="off" data-handler="getShowMessage">
                <div class="modal-body">
                    @csrf
                    <p class="pf-modal__warn">
                        {{ __('Type your account email') }} <span class="fw-bold">({{ auth()->user()->email }})</span>
                        {{ __('to confirm deletion. This cannot be undone.') }}
                    </p>
                    <div class="pf-field mb-3">
                        <label class="pf-label">{{ __('Email') }} <span class="pf-req">*</span></label>
                        <input type="text" class="pf-input" name="email" autocomplete="off" placeholder="{{ __('Email') }}">
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">{{ __('Password') }} <span class="pf-req">*</span></label>
                        <input type="password" class="pf-input" name="password" placeholder="{{ __('Password') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="pf-btn pf-btn--ghost" data-bs-dismiss="modal">{{ __('Back') }}</button>
                    <button type="submit" class="pf-btn pf-btn--danger">{{ __('Delete') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .pf-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:22px; }
    .pf-title { font-size:22px; font-weight:600; color:#111827; margin:0; }
    .pf-crumb { display:flex; gap:6px; align-items:center; font-size:12px; color:#9ca3af; list-style:none; padding:0; margin:6px 0 0; }
    .pf-crumb a { color:#185FA5; font-weight:500; text-decoration:none; }

    .pf-card { background:#fff; border:0.5px solid #185ea56e; border-radius:14px; margin-bottom:20px; overflow:hidden;
        box-shadow:0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06); }
    .pf-card__head { display:flex; align-items:center; gap:10px; padding:14px 20px; border-bottom:0.5px solid #e5e7eb; background:#fafafa; }
    .pf-card__ic { width:34px; height:34px; border-radius:9px; flex:none; display:inline-flex; align-items:center; justify-content:center;
        background:#E6F1FB; color:#185FA5; font-size:17px; }
    .pf-card__title { font-size:15px; font-weight:600; color:#111827; margin:0; }
    .pf-card__sub { font-size:12px; color:#9ca3af; margin-left:auto; }
    .pf-card__body { padding:20px; }

    .pf-idrow { margin-bottom:20px; }
    .pf-avatar { display:flex; align-items:center; gap:16px; }
    .pf-avatar__img { width:76px; height:76px; object-fit:cover; border:2px solid #E6F1FB; background:#f3f4f6; }
    .pf-avatar__edit { position:absolute; bottom:-2px; right:-2px; }
    .pf-avatar__edit input[type=file] { display:none; }
    .pf-avatar__badge { width:26px; height:26px; border-radius:50%; background:#185FA5; color:#fff; cursor:pointer;
        display:flex; align-items:center; justify-content:center; font-size:13px; border:2px solid #fff; margin:0; }
    .pf-avatar__badge:hover { background:#0F4A84; }
    .pf-avatar__name { font-size:15px; font-weight:600; color:#111827; margin:0; }
    .pf-avatar__hint { font-size:12px; color:#9ca3af; margin:2px 0 0; }

    .pf-logo { display:inline-block; }
    .pf-logo__img { width:64px; height:64px; border-radius:12px; object-fit:cover; border:0.5px solid #e5e7eb; background:#f3f4f6; }

    .pf-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; }
    .pf-field { display:flex; flex-direction:column; }
    .pf-label { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin-bottom:6px; }
    .pf-req { color:#A32D2D; }
    .pf-input { width:100%; border:0.5px solid #e5e7eb; border-radius:8px; padding:9px 12px; font-size:13.5px; color:#374151; background:#fff; transition:border-color .13s, box-shadow .13s; }
    .pf-input:focus { outline:none; border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    .pf-input.is-invalid { border-color:#A32D2D; }
    .pf-err { color:#A32D2D; font-size:11.5px; margin-top:4px; }

    .pf-actions { display:flex; justify-content:flex-end; margin-top:8px; padding-top:18px; border-top:0.5px solid #eef2f6; }
    .pf-btn { display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:600; padding:10px 20px; border-radius:9px; border:none; cursor:pointer; text-decoration:none; transition:all .13s; }
    .pf-btn--primary { background:#185FA5; color:#fff; }
    .pf-btn--primary:hover { background:#0F4A84; color:#fff; transform:translateY(-1px); box-shadow:0 6px 16px rgba(24,95,165,.25); }
    .pf-btn--ghost { background:#f3f4f6; color:#374151; }
    .pf-btn--ghost:hover { background:#e5e7eb; }
    .pf-btn--danger { background:#A32D2D; color:#fff; }
    .pf-btn--danger:hover { background:#872323; color:#fff; }
    .pf-btn--danger-ghost { background:#FAECE7; color:#A32D2D; border:0.5px solid #E9C4B6; }
    .pf-btn--danger-ghost:hover { background:#A32D2D; color:#fff; }

    .pf-modal { border:none; border-radius:14px; }
    .pf-modal__head { border-bottom:0.5px solid #e5e7eb; }
    .pf-modal__warn { font-size:13px; color:#6b7280; line-height:1.6; margin-bottom:16px; }

    @media (max-width: 900px) { .pf-grid { grid-template-columns:repeat(2, 1fr); } }
    @media (max-width: 560px) { .pf-grid { grid-template-columns:1fr; } .pf-header { align-items:flex-start; } }
</style>
@endsection

@push('script')
    @include('common.partials.image-resize')
    <script src="{{ asset('/') }}assets/js/pages/profile-setting.init.js"></script>
    <script src="{{ asset('/') }}assets/js/pages/default-profile-setting.init.js"></script>
    <script src="{{ asset('assets/js/custom/delete-my-account.js') }}"></script>
@endpush
