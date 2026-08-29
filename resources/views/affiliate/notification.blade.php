@extends('affiliate.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0 font-size-18">{{ __('Notifications') }}</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        @forelse ($notifications as $n)
                            <a href="{{ $n->url ?? '#' }}" class="d-flex align-items-start gap-3 p-3 border-bottom text-decoration-none" style="color:inherit;">
                                <span class="flex-shrink-0 rounded-circle d-inline-flex align-items-center justify-content-center"
                                      style="width:40px;height:40px;background:#E6F1FB;color:#185FA5;">
                                    <i class="ri-notification-3-line" style="font-size:18px;"></i>
                                </span>
                                <span class="flex-grow-1">
                                    <span class="d-block fw-semibold" style="color:#111827;font-size:14px;">{{ $n->title }}</span>
                                    @if ($n->body)<span class="d-block text-muted" style="font-size:13px;">{{ $n->body }}</span>@endif
                                    <span class="d-block text-muted mt-1" style="font-size:11.5px;"><i class="mdi mdi-clock-outline"></i> {{ optional($n->created_at)->diffForHumans() }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="p-5 text-center text-muted">
                                <i class="ri-notification-off-line" style="font-size:34px;"></i>
                                <p class="mt-2 mb-0">{{ __('No notifications yet.') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
