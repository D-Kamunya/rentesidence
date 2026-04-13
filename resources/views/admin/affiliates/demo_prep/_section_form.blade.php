@php $model = $model ?? null; @endphp

<div class="row g-3">
    <div class="col-md-8">
        <div class="at-field">
            <label class="at-label">Section Title <span class="at-required">*</span></label>
            <input type="text" name="title" class="at-input"
                   value="{{ old('title', $model?->title) }}"
                   placeholder="e.g. Recommended Walkthrough Order" required>
            @error('title')<p class="at-field-error">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="col-md-2">
        <div class="at-field">
            <label class="at-label">Order</label>
            <input type="number" name="sort_order" class="at-input"
                   value="{{ old('sort_order', $model?->sort_order ?? 0) }}"
                   min="0" placeholder="0">
        </div>
    </div>
    <div class="col-md-2">
        <div class="at-field">
            <label class="at-label">Visible</label>
            <label class="dp-toggle">
                <input type="checkbox" name="is_active" value="1"
                       {{ old('is_active', $model?->is_active ?? true) ? 'checked' : '' }}>
                <span class="dp-toggle__track"></span>
            </label>
        </div>
    </div>
    <div class="col-12">
        <div class="at-field">
            <label class="at-label">Content <span class="at-required">*</span></label>
            <p class="at-field-hint">Plain text. Use numbered lists or dashes — formatting is preserved.</p>
            <textarea name="content" class="at-textarea" rows="6"
                      placeholder="1. Start with the dashboard&#10;2. Show property management&#10;3. ..."
                      required>{{ old('content', $model?->content) }}</textarea>
            @error('content')<p class="at-field-error">{{ $message }}</p>@enderror
        </div>
    </div>
</div>