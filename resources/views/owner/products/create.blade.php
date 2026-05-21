@extends('owner.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container">

                    {{-- page Title --}}
                        @php
                            $pageTitle = 'Product Create';
                        @endphp

                    {{-- Page Header --}}
                    <div class="dash-header mb-4">
                        <div>
                            <h2 class="dash-title">{{ __('Add Product/Service') }}</h2>
                            <p class="dash-subtitle">
                                {{ __('Create a new product or service listing') }}
                            </p>
                        </div>
                        <a href="{{ route('owner.products.index') }}" class="theme-btn-secondary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ __('Back to Shop') }}
                        </a>
                    </div>

                    {{-- Form Card --}}
                    <div class="form-card-custom">
                        <form action="{{ route('owner.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
                            @csrf
                            
                            {{-- Basic Information Section --}}
                            <div class="form-section">
                                <div class="form-section__header">
                                    <div class="form-section__icon">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M9 8h6M9 12h6M9 16h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <h4 class="form-section__title">{{ __('Basic Information') }}</h4>
                                </div>
                                
                                <div class="form-section__body">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">
                                                {{ __('Product Name') }}
                                                <span class="required">*</span>
                                            </label>
                                            <input type="text" 
                                                   name="name" 
                                                   class="form-input @error('name') is-invalid @enderror"
                                                   placeholder="{{ __('Enter product name') }}"
                                                   value="{{ old('name') }}"
                                                   required>
                                            @error('name')
                                                <span class="form-error">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">
                                                {{ __('Description') }}
                                                <span class="required">*</span>
                                            </label>
                                            <textarea name="description" 
                                                      class="form-textarea @error('description') is-invalid @enderror"
                                                      placeholder="{{ __('Describe your product or service...') }}"
                                                      rows="4"
                                                      required>{{ old('description') }}</textarea>
                                            @error('description')
                                                <span class="form-error">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-row form-row--two">
                                        {{-- Type --}}
                                        <div class="form-group">
                                            <label class="form-label">
                                                {{ __('Type') }} <span class="required">*</span>
                                            </label>
                                            <div class="select-wrapper">
                                                <select name="type" class="form-select" required>
                                                    <option value="">{{ __('Select Type') }}</option>
                                                    <option value="product" {{ old('type') == 'product' ? 'selected' : '' }}>
                                                        {{ __('Product') }}
                                                    </option>
                                                    <option value="service" {{ old('type') == 'service' ? 'selected' : '' }}>
                                                        {{ __('Service') }}
                                                    </option>
                                                </select>
                                                <svg class="select-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                    <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </div>
                                        </div>

                                        {{-- Category --}}
                                        <div class="form-group">
                                            <label class="form-label">
                                                {{ __('Category') }} <span class="required">*</span>
                                            </label>
                                            <div class="select-wrapper">
                                                <select name="category" 
                                                        id="productCategory"
                                                        class="form-select @error('category') is-invalid @enderror"
                                                        required>
                                                    <option value="">-- {{ __('Select Category') }} --</option>
                                                    
                                                    <optgroup label="{{ __('Products') }}">
                                                        @foreach(\App\Models\ProductCategory::active()->where('type', 'product')->orderBy('name')->get() as $cat)
                                                            <option value="{{ $cat->slug }}"
                                                                    data-category-id="{{ $cat->id }}"
                                                                    data-base-commission="{{ $cat->base_commission }}"
                                                                    {{ old('category') === $cat->slug ? 'selected' : '' }}>
                                                                {{ $cat->name }} ({{ $cat->base_commission }}% base)
                                                            </option>
                                                        @endforeach
                                                    </optgroup>

                                                    <optgroup label="{{ __('Services') }}">
                                                        @foreach(\App\Models\ProductCategory::active()->where('type', 'service')->orderBy('name')->get() as $cat)
                                                            <option value="{{ $cat->slug }}"
                                                                    data-category-id="{{ $cat->id }}"
                                                                    data-base-commission="{{ $cat->base_commission }}"
                                                                    {{ old('category') === $cat->slug ? 'selected' : '' }}>
                                                                {{ $cat->name }} ({{ $cat->base_commission }}% base)
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                </select>

                                                {{-- Hidden input to store the actual category ID --}}
                                                <input type="hidden" 
                                                    name="product_category_id" 
                                                    id="productCategoryId"
                                                    value="{{ old('product_category_id') }}">

                                                <svg class="select-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                    <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </div>
                                            @error('category')
                                                <span class="form-error">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Pricing Section --}}
                            <div class="form-section">
                                <div class="form-section__header">
                                    <div class="form-section__icon form-section__icon--green">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M12 8v8M9 13h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <h4 class="form-section__title">{{ __('Pricing Details') }}</h4>
                                </div>
                                
                                <div class="form-section__body">
                                    <div class="form-row">
                                        <div class="form-group form-group--medium">
                                            <label class="form-label">
                                                {{ __('Price (Ksh)') }} <span class="required">*</span>
                                            </label>
                                            <div class="input-with-prefix">
                                                <span class="input-prefix">Ksh</span>
                                                <input type="number" 
                                                    name="price" 
                                                    id="price"
                                                    class="form-input form-input--with-prefix @error('price') is-invalid @enderror"
                                                    placeholder="{{ __('0.00') }}"
                                                    value="{{ old('price') }}"
                                                    step="0.01"
                                                    min="0"
                                                    required>
                                            </div>
                                            @error('price')
                                                <span class="form-error">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Commission Preview - Must be INSIDE form-section__body --}}
                                    <div id="commissionPreview" class="commission-preview" style="display: none;">
                                        <div class="commission-preview__header">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                                                <path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                            {{ __('Earnings Breakdown') }}
                                        </div>
                                        <div class="commission-preview__grid">
                                            <div class="commission-preview__item">
                                                <span class="commission-preview__label">{{ __('Listed Price') }}</span>
                                                <span class="commission-preview__val" id="previewPrice">—</span>
                                            </div>
                                            <div class="commission-preview__item">
                                                <span class="commission-preview__label">{{ __('Base Commission') }}</span>
                                                <span class="commission-preview__val commission-preview__val--muted" id="previewBase">—</span>
                                            </div>
                                            <div class="commission-preview__item">
                                                <span class="commission-preview__label">{{ __('Tier Markup') }}</span>
                                                <span class="commission-preview__val commission-preview__val--muted" id="previewMarkup">—</span>
                                            </div>
                                            <div class="commission-preview__item">
                                                <span class="commission-preview__label">{{ __('Tier Discount') }}</span>
                                                <span class="commission-preview__val commission-preview__val--muted" id="previewDiscount">—</span>
                                            </div>
                                            <div class="commission-preview__item">
                                                <span class="commission-preview__label">{{ __('Effective Rate') }}</span>
                                                <span class="commission-preview__val commission-preview__val--rate" id="previewRate">—</span>
                                            </div>
                                            <div class="commission-preview__item commission-preview__item--highlight">
                                                <span class="commission-preview__label">{{ __('You Earn') }}</span>
                                                <span class="commission-preview__val commission-preview__val--earn" id="previewEarn">—</span>
                                            </div>
                                            <div class="commission-preview__item">
                                                <span class="commission-preview__label">{{ __('Centresidence Fee') }}</span>
                                                <span class="commission-preview__val commission-preview__val--fee" id="previewFee">—</span>
                                            </div>
                                        </div>
                                        <p class="commission-preview__note">
                                            {{ __('Earnings shown are per unit sold. Upgrade your plan to reduce commission rates.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Images Section --}}
                            <div class="form-section">
                                <div class="form-section__header">
                                    <div class="form-section__icon form-section__icon--purple">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                            <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                                            <path d="M21 15l-5-5L8 21h13v-6z" fill="currentColor" opacity="0.5"/>
                                        </svg>
                                    </div>
                                    <h4 class="form-section__title">{{ __('Product Images') }}</h4>
                                </div>
                                
                                <div class="form-section__body">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">
                                                {{ __('Upload Images') }}
                                                <span class="required">*</span>
                                            </label>
                                            <div class="file-upload-wrapper">
                                                <label for="images" class="file-upload-label">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    <span>{{ __('Choose images or drag & drop') }}</span>
                                                    <span class="file-upload-hint">{{ __('PNG, JPG up to 5MB') }}</span>
                                                </label>
                                                <input type="file" 
                                                       name="images[]" 
                                                       id="images" 
                                                       class="file-upload-input" 
                                                       multiple 
                                                       accept="image/png,image/jpeg,image/jpg"
                                                       required>
                                            </div>
                                            <div id="image-preview" class="image-preview-grid"></div>
                                            @error('images')
                                                <span class="form-error">{{ $message }}</span>
                                            @enderror
                                            @error('images.*')
                                                <span class="form-error">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Form Actions --}}
                            <div class="form-actions">
                                <a href="{{ route('owner.products.index') }}" class="btn-secondary">
                                    {{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn-primary">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    {{ __('Add Product/Service') }}
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
    <style>
        /* ── Page header ─────────────────────────────────────── */
        .dash-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .dash-title {
            font-size: 22px;
            font-weight: 500;
            color: #111827;
            margin: 0 0 4px;
        }
        .dash-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }

        /* ── Buttons ─────────────────────────────────────────── */
        .theme-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #185FA5;
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 13px;
            text-decoration: none;
            transition: all .15s;
            border: none;
        }
        .theme-btn-primary:hover {
            background: #0F4A84;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(24, 95, 165, 0.25);
        }
        .theme-btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            color: #6b7280;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 13px;
            text-decoration: none;
            transition: all .15s;
            border: 0.5px solid #e5e7eb;
        }
        .theme-btn-secondary:hover {
            background: #f3f4f6;
            color: #374151;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* ── Form Card ───────────────────────────────────────── */
        .form-card-custom {
            max-width: 720px;
            margin: 0 auto;
        }

        /* ── Form Sections ───────────────────────────────────── */
        .form-section {
            background: #fff;
            border: 0.5px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 1.25rem;
            overflow: hidden;
        }
        .form-section__header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 1rem 1.25rem;
            background: #fafafa;
            border-bottom: 0.5px solid #e5e7eb;
        }
        .form-section__icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #E6F1FB;
            color: #185FA5;
        }
        .form-section__icon--green {
            background: #E1F5EE;
            color: #0F6E56;
        }
        .form-section__icon--purple {
            background: #EEEDFE;
            color: #534AB7;
        }
        .form-section__title {
            font-size: 15px;
            font-weight: 500;
            color: #111827;
            margin: 0;
        }
        .form-section__body {
            padding: 1.25rem;
        }

        /* ── Form Elements ───────────────────────────────────── */
        .form-row {
            margin-bottom: 1.25rem;
        }
        .form-row:last-child {
            margin-bottom: 0;
        }
        .form-row--two {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group--medium {
            max-width: 280px;
        }
        .form-label {
            font-size: 13px;
            font-weight: 500;
            color: #374151;
        }
        .required {
            color: #993C1D;
            margin-left: 2px;
        }
        .form-input {
            padding: 10px 14px;
            font-size: 14px;
            color: #111827;
            background: #fff;
            border: 0.5px solid #e5e7eb;
            border-radius: 8px;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-input:focus {
            outline: none;
            border-color: #185FA5;
            box-shadow: 0 0 0 3px rgba(24, 95, 165, 0.1);
        }
        .form-input.is-invalid {
            border-color: #993C1D;
        }
        .form-input.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(153, 60, 29, 0.1);
        }
        .form-input--with-prefix {
            padding-left: 60px;
        }
        .form-textarea {
            padding: 10px 14px;
            font-size: 14px;
            color: #111827;
            background: #fff;
            border: 0.5px solid #e5e7eb;
            border-radius: 8px;
            resize: vertical;
            font-family: inherit;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-textarea:focus {
            outline: none;
            border-color: #185FA5;
            box-shadow: 0 0 0 3px rgba(24, 95, 165, 0.1);
        }
        .form-textarea.is-invalid {
            border-color: #993C1D;
        }

        /* ── Select ──────────────────────────────────────────── */
        .select-wrapper {
            position: relative;
        }
        .form-select {
            width: 100%;
            padding: 10px 36px 10px 14px;
            font-size: 14px;
            color: #111827;
            background: #fff;
            border: 0.5px solid #e5e7eb;
            border-radius: 8px;
            appearance: none;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-select:focus {
            outline: none;
            border-color: #185FA5;
            box-shadow: 0 0 0 3px rgba(24, 95, 165, 0.1);
        }
        .select-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            pointer-events: none;
        }

        /* ── Input with Prefix ───────────────────────────────── */
        .input-with-prefix {
            position: relative;
        }
        .input-prefix {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: #6b7280;
            pointer-events: none;
        }

        /* ── File Upload ─────────────────────────────────────── */
        .file-upload-wrapper {
            border: 1.5px dashed #d1d5db;
            border-radius: 12px;
            transition: border-color .15s, background .15s;
        }
        .file-upload-wrapper:hover {
            border-color: #185FA5;
            background: #fafafa;
        }
        .file-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 2rem 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }
        .file-upload-label svg {
            color: #9ca3af;
            margin-bottom: 4px;
        }
        .file-upload-label span {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
        }
        .file-upload-hint {
            font-size: 12px !important;
            font-weight: 400 !important;
            color: #9ca3af !important;
        }
        .file-upload-input {
            display: none;
        }

        /* ── Commission preview ─────────────────────────────────────── */
        .commission-preview {
        background: #F0F7FF;
        border: 0.5px solid #B8D4F0;
        border-radius: 10px;
        padding: 1rem 1.1rem;
        margin-top: .75rem;
        }
        .commission-preview__header {
            display: flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 600; color: #185FA5;
            text-transform: uppercase; letter-spacing: .06em;
            margin-bottom: .85rem;
        }
        .commission-preview__grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .6rem .5rem;
        }
        .commission-preview__item {
            display: flex; flex-direction: column; gap: 3px;
        }
        .commission-preview__item--highlight {
            background: #fff;
            border: 0.5px solid #B8D4F0;
            border-radius: 8px;
            padding: 6px 10px;
        }
        .commission-preview__label {
            font-size: 10px; font-weight: 500;
            text-transform: uppercase; letter-spacing: .06em;
            color: #9ca3af;
        }
        .commission-preview__val {
            font-size: 14px; font-weight: 600; color: #111827;
        }
        .commission-preview__val--muted  { color: #6b7280; font-size: 13px; }
        .commission-preview__val--rate   { color: #854F0B; }
        .commission-preview__val--earn   { color: #0F6E56; font-size: 16px; }
        .commission-preview__val--fee    { color: #993C1D; font-size: 13px; }
        .commission-preview__note {
            font-size: 11px; color: #6b7280;
            margin: .75rem 0 0; line-height: 1.5;
            border-top: 0.5px solid #d0e8fb;
            padding-top: .6rem;
        }

        /* ── Image Preview ───────────────────────────────────── */
        .image-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 10px;
            margin-top: 1rem;
        }
        .preview-item {
            position: relative;
            aspect-ratio: 1 / 1;
            border-radius: 8px;
            overflow: hidden;
            border: 0.5px solid #e5e7eb;
        }
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .preview-remove {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            transition: background .15s;
        }
        .preview-remove:hover {
            background: #993C1D;
        }

        /* ── Form Error ──────────────────────────────────────── */
        .form-error {
            font-size: 12px;
            color: #993C1D;
            margin-top: 4px;
        }

        /* ── Form Actions ────────────────────────────────────── */
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 1rem;
            padding-top: 0.5rem;
        }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #185FA5;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all .15s;
        }
        .btn-primary:hover {
            background: #0F4A84;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(24, 95, 165, 0.25);
        }
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: #fff;
            color: #6b7280;
            border: 0.5px solid #e5e7eb;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            text-decoration: none;
            transition: all .15s;
        }
        .btn-secondary:hover {
            background: #f3f4f6;
            color: #374151;
        }

        /* ── Responsive ──────────────────────────────────────── */
        @media (max-width: 640px) {
            .form-row--two {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .form-group--medium {
                max-width: 100%;
            }
            .form-actions {
                flex-direction: column-reverse;
                gap: 0.75rem;
            }
            .btn-primary,
            .btn-secondary {
                width: 100%;
                justify-content: center;
            }
            .dash-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .theme-btn-secondary {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endpush

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ====================== IMAGE PREVIEW ======================
            const imageInput = document.getElementById('images');
            const previewContainer = document.getElementById('image-preview');
            let selectedFiles = new DataTransfer();

            if (imageInput) {
                imageInput.addEventListener('change', function (e) {
                    Array.from(e.target.files).forEach(file => {
                        if (file.type.startsWith('image/')) {
                            selectedFiles.items.add(file);
                            addPreviewItem(file);
                        }
                    });
                    imageInput.files = selectedFiles.files;
                });
            }

            function addPreviewItem(file) {
                const reader = new FileReader();
                reader.onload = function (ev) {
                    const item = document.createElement('div');
                    item.className = 'preview-item';
                    item.innerHTML = `
                        <img src="${ev.target.result}" alt="Preview">
                        <button type="button" class="preview-remove" onclick="removePreviewItem(this)">×</button>
                    `;
                    previewContainer.appendChild(item);
                };
                reader.readAsDataURL(file);
            }

            window.removePreviewItem = function (button) {
                const item = button.closest('.preview-item');
                const img = item.querySelector('img');

                const newDt = new DataTransfer();
                Array.from(selectedFiles.files).forEach(file => {
                    if (!img.src.includes(file.name)) {
                        newDt.items.add(file);
                    }
                });

                selectedFiles = newDt;
                if (imageInput) imageInput.files = selectedFiles.files;
                item.remove();
            };

            // ====================== COMMISSION PREVIEW ======================
            const categorySelect   = document.getElementById('productCategory');
            const categoryIdInput  = document.getElementById('productCategoryId');
            const priceInput       = document.getElementById('price');
            const previewSection   = document.getElementById('commissionPreview');

            const packageMarkup    = {{ $packageMarkup ?? 0 }};
            const packageDiscount  = {{ $packageDiscount ?? 0 }};
            const minCommission    = 3.0;
            const currencySymbol   = '{{ getCurrencySymbol() ?? "Ksh" }}';

            function updatePreview() {
                const selectedOpt = categorySelect?.options[categorySelect.selectedIndex];
                const baseCommission = Number(selectedOpt?.dataset?.baseCommission) || 0;
                const price          = Number(priceInput?.value) || 0;
                const categoryId     = selectedOpt?.dataset?.categoryId || '';

                // Update hidden field
                if (categoryIdInput) {
                    categoryIdInput.value = categoryId;
                }

                if (!price || !baseCommission || !categoryId) {
                    if (previewSection) previewSection.style.display = 'none';
                    return;
                }

                const effectiveRate    = Math.max(baseCommission + packageMarkup - packageDiscount, minCommission);
                const commissionAmount = price * (effectiveRate / 100);
                const netEarn          = price - commissionAmount;

                // Update preview values
                document.getElementById('previewPrice').textContent  = currencySymbol + ' ' + price.toFixed(2);
                document.getElementById('previewBase').textContent   = baseCommission.toFixed(1) + '%';
                document.getElementById('previewMarkup').textContent = '+' + packageMarkup.toFixed(1) + '%';
                document.getElementById('previewDiscount').textContent = '-' + packageDiscount.toFixed(1) + '%';
                document.getElementById('previewRate').textContent   = effectiveRate.toFixed(1) + '%';
                document.getElementById('previewEarn').textContent   = currencySymbol + ' ' + netEarn.toFixed(2);
                document.getElementById('previewFee').textContent    = currencySymbol + ' ' + commissionAmount.toFixed(2);

                if (previewSection) previewSection.style.display = 'block';
            }

            // Attach listeners
            if (categorySelect) categorySelect.addEventListener('change', updatePreview);
            if (priceInput)     priceInput.addEventListener('input', updatePreview);

            // Initial trigger (important for old() values after validation error)
            updatePreview();
        });
    </script>
@endpush