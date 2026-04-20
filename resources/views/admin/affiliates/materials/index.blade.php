@extends('admin.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- Page Title --}}
                @php
                    $pageTitle = 'Materials';
                @endphp
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                            <div class="page-title-left">
                                <h3 class="mb-sm-0">Marketing Materials</h3>
                            </div>
                            <div class="page-title-right">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Marketing Materials</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Header row --}}
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                    <div>
                        <p class="at-subtitle">Brochures, pitch decks, links, and images attached to action templates for affiliates to share with prospects.</p>
                    </div>
                    <a href="{{ route('admin.materials.create') }}" class="at-btn-primary">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                            <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        Add Material
                    </a>
                </div>

                {{-- Stats row --}}
                @php
                    $byType = $materials->groupBy('type');
                @endphp
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="at-stat">
                            <div class="at-stat__icon" style="background:#f3f4f6;color:#444441;">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <path d="M4 2h6l4 4v8H4V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10 2v4h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="at-stat__label">Total</div>
                            <div class="at-stat__val">{{ $materials->total() }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="at-stat" style="background:#FCEBEB;border-color:#F7C1C1;">
                            <div class="at-stat__icon" style="background:#FCEBEB;color:#A32D2D;">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <path d="M4 2h6l4 4v8H4V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10 2v4h4M6 9h4M6 11.5h2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="at-stat__label" style="color:#A32D2D;">PDFs</div>
                            <div class="at-stat__val" style="color:#A32D2D;">{{ $byType->get('pdf', collect())->count() }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="at-stat" style="background:#EEF5FD;border-color:#B5D4F4;">
                            <div class="at-stat__icon" style="background:#E6F1FB;color:#185FA5;">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <rect x="1" y="3" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M5 7l3 3 3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="at-stat__label" style="color:#185FA5;">Images</div>
                            <div class="at-stat__val" style="color:#185FA5;">{{ $byType->get('png', collect())->count() }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="at-stat" style="background:#F0FDF6;border-color:#9FE1CB;">
                            <div class="at-stat__icon" style="background:#E1F5EE;color:#0F6E56;">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <path d="M2 8a6 6 0 1 0 12 0A6 6 0 0 0 2 8z" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="at-stat__label" style="color:#0F6E56;">Active</div>
                            <div class="at-stat__val" style="color:#0F6E56;">{{ $materials->where('is_active', true)->count() }}</div>
                        </div>
                    </div>
                </div>

                {{-- Flash --}}
                @if(session('success'))
                    <div class="at-alert at-alert--success mb-4">
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Table --}}
                <div class="at-card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr style="border-bottom:0.5px solid #e5e7eb;background:#fafafa;">
                                    <th class="at-th">Title</th>
                                    <th class="at-th">Type</th>
                                    <th class="at-th">Category</th>
                                    <th class="at-th">Priority</th>
                                    <th class="at-th">Used in</th>
                                    <th class="at-th">Status</th>
                                    <th class="at-th">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($materials as $m)
                                    <tr style="border-bottom:0.5px solid #f3f4f6;">

                                        {{-- Title --}}
                                        <td class="at-td">
                                            <div class="d-flex align-items-center gap-10">
                                                {{-- Type icon thumbnail --}}
                                                <div class="mm-type-icon mm-type-icon--{{ $m->type }}">
                                                    @if($m->type === 'pdf')
                                                        <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                            <path d="M4 2h6l4 4v8H4V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M10 2v4h4M6 9h4M6 11.5h2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                        </svg>
                                                    @elseif($m->type === 'png')
                                                        <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                            <rect x="1" y="2" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                                            <circle cx="5.5" cy="5.5" r="1.5" stroke="currentColor" stroke-width="1.3"/>
                                                            <path d="M1 11l4-3 3 3 2-2 5 4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    @elseif($m->type === 'link')
                                                        <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                            <path d="M6.5 9.5a4 4 0 0 0 5.657 0l1.414-1.414a4 4 0 0 0-5.657-5.657L6.5 3.843" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                            <path d="M9.5 6.5a4 4 0 0 0-5.657 0L2.43 7.914a4 4 0 0 0 5.657 5.657l1.414-1.414" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                        </svg>
                                                    @else
                                                        <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                            <path d="M2 2h12v10H9l-3 2v-2H2V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div style="font-weight:500;font-size:14px;color:#111827;">{{ $m->title }}</div>
                                                    @if($m->type === 'link' && $m->content)
                                                        <a href="{{ $m->content }}" target="_blank" style="font-size:11px;color:#9ca3af;text-decoration:none;" class="mm-link-preview">
                                                            {{ Str::limit($m->content, 45) }}
                                                        </a>
                                                    @elseif($m->file_path)
                                                        <a href="{{ asset('storage/'.$m->file_path) }}" target="_blank" style="font-size:11px;color:#9ca3af;text-decoration:none;">
                                                            View file
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Type badge --}}
                                        <td class="at-td">
                                            <span class="at-badge mm-badge--{{ $m->type }}">
                                                {{ strtoupper($m->type) }}
                                            </span>
                                        </td>

                                        {{-- Category --}}
                                        <td class="at-td">
                                            @if($m->category)
                                                <span style="font-size:13px;color:#374151;font-weight:500;">{{ ucfirst($m->category) }}</span>
                                            @else
                                                <span style="font-size:12px;color:#d1d5db;">—</span>
                                            @endif
                                        </td>

                                        {{-- Priority --}}
                                        <td class="at-td">
                                            <span class="mm-priority mm-priority--{{ $m->priority <= 2 ? 'high' : ($m->priority <= 4 ? 'mid' : 'low') }}">
                                                {{ $m->priority }}
                                            </span>
                                        </td>

                                        {{-- Usage count --}}
                                        <td class="at-td">
                                            @if($m->usage_count > 0)
                                                <span style="font-size:13px;font-weight:500;color:#374151;">
                                                    {{ $m->usage_count }} {{ Str::plural('template', $m->usage_count) }}
                                                </span>
                                            @else
                                                <span style="font-size:12px;color:#d1d5db;">Unused</span>
                                            @endif
                                        </td>

                                        {{-- Status --}}
                                        <td class="at-td">
                                            @if($m->is_active)
                                                <span class="at-badge at-badge--active">
                                                    <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;flex-shrink:0;"></span>
                                                    Active
                                                </span>
                                            @else
                                                <span class="at-badge at-badge--inactive">
                                                    <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;flex-shrink:0;"></span>
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Actions --}}
                                        <td class="at-td">
                                            <div class="d-flex align-items-center gap-2">
                                                <a href="{{ route('admin.materials.edit', $m->id) }}" class="at-action-btn at-action-btn--edit">
                                                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                        <path d="M11.5 2.5a1.5 1.5 0 0 1 2 2L5 13H3v-2L11.5 2.5z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('admin.materials.destroy', $m->id) }}" style="display:inline;"
                                                      onsubmit="return confirm('Delete \'{{ addslashes($m->title) }}\'? This will detach it from all templates.')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="at-action-btn at-action-btn--delete">
                                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                            <path d="M3 4h10M5 4V3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1M6 7v5M10 7v5M4 4l1 9h6l1-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="padding:3rem 1rem;text-align:center;">
                                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="color:#d1d5db;margin-bottom:10px;display:block;margin-left:auto;margin-right:auto;">
                                                <path d="M7 3h10l4 4v14H3V3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M14 3v5h7M8 13h8M8 17h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                            <p style="color:#9ca3af;font-size:14px;margin:0;">No materials yet.</p>
                                            <a href="{{ route('admin.materials.create') }}" class="at-btn-primary" style="margin-top:12px;display:inline-flex;">
                                                <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                    <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                </svg>
                                                Add Material
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pagination --}}
                @if($materials->hasPages())
                    <div class="mt-4">{{ $materials->links() }}</div>
                @endif

            </div>
        </div>
    </div>
</div>

@include('admin.affiliates.materials._material_styles')
@endsection