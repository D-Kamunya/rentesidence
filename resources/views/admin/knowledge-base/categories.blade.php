@extends('admin.layouts.app')

@section('title', 'Knowledge Base Categories')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid px-4">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                {{-- Page Header --}}
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h1 style="font-size: 22px; font-weight: 500; color: #111827; margin-bottom: 4px;">Knowledge Base Categories</h1>
                        <nav aria-label="breadcrumb">
                            <ol style="display: flex; gap: 6px; font-size: 12px; color: #9ca3af; list-style: none; padding: 0; margin: 0;">
                                <li><a href="{{ route('admin.dashboard') }}" style="color: #185FA5; font-weight: 500;">Dashboard</a></li>
                                <li>
                                    <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                                        <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </li>
                                <li>Knowledge Base</li>
                                <li>
                                    <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                                        <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </li>
                                <li>Categories</li>
                            </ol>
                        </nav>
                    </div>
                    <button onclick="openCategoryModal()" class="ow-btn" style="background: #534AB7; color: #fff; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: none; cursor: pointer; transition: all .13s;">
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                            <path d="M6.5 2.5V10.5M2.5 6.5H10.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        Add Category
                    </button>
                </div>

                {{-- Categories Grid --}}
                <div class="row">
                    @forelse($categories as $category)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="ow-card" style="background: #fff; border: 0.5px solid #185ea56e; border-radius: 12px; overflow: hidden; padding: 20px; transition: all .25s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06); cursor: pointer;" 
                            onmouseover="this.style.borderColor='#185FA5'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.06), 0 0 0 1px rgba(24,95,165,0.12), 0 12px 30px rgba(24,95,165,0.18)';"
                            onmouseout="this.style.borderColor='#185ea56e'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06)';">
                            
                            {{-- Category Header --}}
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    @if($category->icon)
                                    <span style="font-size: 20px;">{!! $category->icon !!}</span>
                                    @endif
                                    <h3 style="font-size: 15px; font-weight: 600; color: #111827; margin: 0;">{{ $category->name }}</h3>
                                </div>
                                <div>
                                    @if($category->is_active)
                                    <span class="ow-badge" style="background: #E1F5EE; color: #0F6E56; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px;">
                                        Active
                                    </span>
                                    @else
                                    <span class="ow-badge" style="background: #F3F4F6; color: #6b7280; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px; border: 0.5px solid #e5e7eb;">
                                        Inactive
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Audience Badge --}}
                            <div class="mb-3">
                                <span class="ow-badge" style="background: #E6F1FB; color: #0C447C; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px; border: 0.5px solid #B5D4F4;">
                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                        <circle cx="5" cy="5" r="3" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                    {{ ucfirst($category->audience) }}
                                </span>
                            </div>

                            {{-- Description --}}
                            @if($category->description)
                            <p style="font-size: 13px; color: #6b7280; margin-bottom: 16px; line-height: 1.5;">
                                {{ Str::limit($category->description, 120) }}
                            </p>
                            @endif

                            {{-- Article Count --}}
                            <div style="font-size: 12.5px; color: #6b7280; margin-bottom: 16px;">
                                <strong>{{ $category->articles_count }}</strong> articles
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex gap-2">
                                <button onclick="event.stopPropagation(); openCategoryModal({{ $category->id }})" 
                                        class="ow-btn" style="background: #f3f4f6; color: #374151; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: 0.5px solid #e5e7eb; cursor: pointer; transition: all .13s;">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                        <path d="M9.5 1.5L11.5 3.5L4 11L1.5 11.5L2 9L9.5 1.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Edit
                                </button>
                                <form action="{{ route('admin.kb.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this category?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ow-btn" style="background: #185ea51c; color: #374151; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: none; cursor: pointer; transition: all .13s;" 
                                            onmouseover="this.style.background='#fee2e2'; this.style.color='#b91c1c';" 
                                            onmouseout="this.style.background='#185ea51c'; this.style.color='#374151';">
                                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                            <path d="M2.5 4H10.5M5 4V3C5 2.44772 5.44772 2 6 2H7C7.55228 2 8 2.44772 8 3V4M9.5 4V10.5C9.5 11.0523 9.05228 11.5 8.5 11.5H4.5C3.94772 11.5 3.5 11.0523 3.5 10.5V4H9.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="ow-card text-center" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 60px 20px;">
                            <p style="font-size: 15px; color: #6b7280; margin-bottom: 16px;">No categories found</p>
                            <button onclick="openCategoryModal()" class="ow-btn" style="background: #534AB7; color: #fff; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: none; cursor: pointer;">
                                Create Your First Category
                            </button>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Category Modal --}}
@include('admin.knowledge-base.partials.category-modal')

<script>
let categories = @json($categories->keyBy('id'));

function openCategoryModal(id = null) {
    const modal = document.getElementById('categoryModal');
    const form = document.getElementById('categoryForm');
    const title = document.getElementById('modalTitle');
    
    if (id && categories[id]) {
        const category = categories[id];
        title.textContent = 'Edit Category';
        form.action = `/admin/knowledge-base/categories/${id}`;
        form.method = 'POST';
        document.getElementById('name').value = category.name;
        document.getElementById('icon').value = category.icon || '';
        document.getElementById('description').value = category.description || '';
        document.getElementById('audience').value = category.audience;
        document.getElementById('sort_order').value = category.sort_order || 0;
        document.getElementById('is_active').checked = category.is_active;
    } else {
        title.textContent = 'Add Category';
        form.action = '{{ route("admin.kb.categories.store") }}';
        form.method = 'POST';
        form.reset();
    }
    
    modal.style.display = 'block';
}

function closeCategoryModal() {
    document.getElementById('categoryModal').style.display = 'none';
}
</script>
@endsection