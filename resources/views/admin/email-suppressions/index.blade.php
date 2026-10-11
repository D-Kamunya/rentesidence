@extends('admin.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20 cs-controls">
                    @include('centresidence._design')

                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">{{ $pageTitle }}</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p style="font-size:13.5px;color:#6b7280;max-width:70ch;margin:0 0 18px;">
                        {{ __('Addresses here never receive automated email (the deliverability safety net). Add one to park a test/placeholder address or silence a known bad address; remove one to let a genuine address receive mail again.') }}
                    </p>

                    {{-- Add form --}}
                    <form method="POST" action="{{ route('admin.email-suppressions.store') }}" class="row g-2 align-items-end mb-4" style="max-width:720px;">
                        @csrf
                        <div class="col-sm-5">
                            <label class="label-text-title color-heading font-medium mb-2">{{ __('Email') }} <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" value="{{ old('email') }}" required>
                            @error('email')<span class="text-danger" style="font-size:12px;">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-sm-5">
                            <label class="label-text-title color-heading font-medium mb-2">{{ __('Reason') }}</label>
                            <input type="text" name="reason" class="form-control" placeholder="{{ __('e.g. test placeholder, hard bounce') }}" value="{{ old('reason') }}">
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="theme-btn w-100">{{ __('Suppress') }}</button>
                        </div>
                    </form>

                    {{-- Search --}}
                    <form method="GET" action="{{ route('admin.email-suppressions.index') }}" class="mb-3" style="max-width:360px;">
                        <div style="display:flex;gap:8px;">
                            <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="{{ __('Search email…') }}">
                            <button type="submit" class="btn" style="background:#F4F5F7;border:1px solid #D9DEE6;border-radius:8px;padding:0 16px;">{{ __('Search') }}</button>
                        </div>
                    </form>

                    {{-- List --}}
                    <div class="table-responsive">
                        <table class="table theme-border">
                            <thead>
                                <tr>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Reason') }}</th>
                                    <th>{{ __('Source') }}</th>
                                    <th>{{ __('Added') }}</th>
                                    <th class="text-end">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suppressions as $s)
                                    <tr>
                                        <td style="word-break:break-word;">{{ $s->email }}</td>
                                        <td style="color:#6b7280;">{{ $s->reason ?: '—' }}</td>
                                        <td>
                                            <span class="status-btn font-13 radius-4 {{ $s->source === 'admin' ? 'status-btn-green' : ($s->source === 'bounce' ? 'status-btn-orange' : '') }}">{{ ucfirst($s->source) }}</span>
                                        </td>
                                        <td style="white-space:nowrap;">{{ $s->created_at->format('d M Y') }}</td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('admin.email-suppressions.remove', $s->id) }}" style="display:inline;"
                                                  data-cs-confirm="{{ __('Remove :email from the suppression list? It will be able to receive automated email again.', ['email' => $s->email]) }}"
                                                  data-cs-confirm-title="{{ __('Un-suppress email?') }}"
                                                  data-cs-confirm-ok="{{ __('Yes, remove') }}">
                                                @csrf
                                                <button type="submit" class="btn" style="display:inline-flex;align-items:center;gap:5px;background:#F4F5F7;border:0.5px solid #D9DEE6;color:#4B5563;border-radius:99px;font-size:11px;font-weight:500;padding:4px 11px;cursor:pointer;">
                                                    {{ __('Remove') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" style="text-align:center;padding:26px;color:#9ca3af;">{{ __('No suppressed addresses.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($suppressions->hasPages())
                        <div class="mt-3">{{ $suppressions->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
