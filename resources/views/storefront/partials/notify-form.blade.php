@php
    // $notifyScope => scope_type / scope_owner_user_id / scope_property_id (from the controller)
    // $areaPrefill => area_city / area_state (prefilled for the property scope)
    // $variant     => 'empty' | 'watch'
    $variant = $variant ?? 'empty';
@endphp

<form method="POST" action="{{ route('storefront.notify') }}">
    @csrf
    <input type="hidden" name="scope_type" value="{{ $notifyScope['scope_type'] }}">
    <input type="hidden" name="scope_owner_user_id" value="{{ $notifyScope['scope_owner_user_id'] }}">
    <input type="hidden" name="scope_property_id" value="{{ $notifyScope['scope_property_id'] }}">
    <input type="hidden" name="return_url" value="{{ url()->current() }}">

    @if($variant === 'empty')
        <h4>{{ __('Notify me when a home opens up') }}</h4>
        <p class="hint">{{ __('We’ll email or text you the moment something matches — one message, no spam.') }}</p>
    @endif

    <div class="sf-grid2">
        <div class="sf-fld">
            <label>{{ __('Your name') }}</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('Jane Doe') }}">
        </div>
        <div class="sf-fld">
            <label>{{ __('Email') }}</label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('you@email.com') }}">
            @error('email')<span class="sf-err">{{ $message }}</span>@enderror
        </div>
        <div class="sf-fld">
            <label>{{ __('Phone') }}</label>
            <input type="text" name="contact_number" value="{{ old('contact_number') }}" placeholder="{{ __('0700 000 000') }}">
            @error('contact_number')<span class="sf-err">{{ $message }}</span>@enderror
        </div>
        <div class="sf-fld">
            <label>{{ __('Area / town you want') }}</label>
            <input type="text" name="area_city" value="{{ old('area_city', $areaPrefill['area_city'] ?? '') }}" placeholder="{{ __('e.g. Westlands') }}">
        </div>
        <div class="sf-fld full">
            <label>{{ __('County / region (optional)') }}</label>
            <input type="text" name="area_state" value="{{ old('area_state', $areaPrefill['area_state'] ?? '') }}" placeholder="{{ __('e.g. Nairobi') }}">
        </div>
    </div>

    <button type="submit" class="sf-btn">
        {{ $variant === 'watch' ? __('Watch this area') : __('Notify me') }}
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </button>
</form>
