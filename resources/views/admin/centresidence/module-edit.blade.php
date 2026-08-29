@extends('admin.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('admin.centresidence._nav', ['active' => 'modules'])

        <div class="cs-titlebar"><h1 class="cs-title" style="font-size:18px;">{{ __('Edit module') }} — {{ $module->name }}</h1></div>

        @if (session('success')) <div class="cs-alert is-success">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif
        @if ($errors->any()) <div class="cs-alert is-danger">{{ $errors->first() }}</div> @endif

        <form method="POST" action="{{ route('admin.centresidence.modules.update', $module->id) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row" style="margin-bottom:26px;">
                <div class="col-lg-8">
                    <div class="cs-card"><div class="cs-card__body">
                        <div class="row">
                            <div class="col-md-7 cs-field"><label class="cs-label">{{ __('Name') }}</label><input id="fName" name="name" class="cs-input" value="{{ old('name', $module->name) }}" required></div>
                            <div class="col-md-5 cs-field"><label class="cs-label">{{ __('Accent colour') }}</label><input id="fColor" type="color" name="accent_color" class="cs-input" style="height:40px;padding:4px;" value="{{ old('accent_color', $module->displayColor()) }}"></div>
                        </div>
                        <div class="cs-field"><label class="cs-label">{{ __('Tagline (card subtitle)') }}</label><input id="fTag" name="tagline" class="cs-input" value="{{ old('tagline', $module->tagline) }}" maxlength="255"></div>
                        <div class="row">
                            <div class="col-md-6 cs-field"><label class="cs-label">{{ __('Icon (remix icon class)') }}</label><input id="fIcon" name="icon" class="cs-input" value="{{ old('icon', $module->icon) }}" placeholder="ri-drop-line"></div>
                            <div class="col-md-6 cs-field"><label class="cs-label">{{ __('Image (optional, replaces icon)') }}</label><input type="file" name="image" class="cs-input" accept="image/*"></div>
                        </div>
                        @if ($module->image_url)
                            <div class="cs-field"><img src="{{ $module->image_url }}" alt="" style="max-height:80px;border-radius:8px;border:0.5px solid var(--gray-200);"></div>
                        @endif
                        <div class="cs-field"><label class="cs-label">{{ __('Short description') }}</label><textarea name="description" class="cs-input" rows="2">{{ old('description', $module->description) }}</textarea></div>
                        <div class="cs-field"><label class="cs-label">{{ __('How it grows cashflow (owner-facing)') }}</label><textarea name="cashflow_benefit" class="cs-input" rows="3">{{ old('cashflow_benefit', $module->cashflow_benefit) }}</textarea></div>
                        <div class="cs-field"><label class="cs-label">{{ __('Financier overview (partner-facing)') }}</label><textarea name="financier_overview" class="cs-input" rows="3" placeholder="{{ __('The financing opportunity, demand, and how repayment is secured — shown to finance partners.') }}">{{ old('financier_overview', $module->financier_overview) }}</textarea></div>
                        <div class="cs-field"><label class="cs-label">{{ __('How it works (one step per line)') }}</label><textarea name="how_it_works" class="cs-input" rows="4">{{ old('how_it_works', $module->how_it_works) }}</textarea></div>
                        <div class="cs-field"><label class="cs-label">{{ __('Benefits (one per line)') }}</label><textarea name="benefits" class="cs-input" rows="4">{{ old('benefits', implode("\n", $module->benefits ?? [])) }}</textarea></div>
                    </div></div>

                    <div class="cs-card">
                        <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Pricing & visibility') }}</h2></div>
                        <div class="cs-card__body">
                            <div class="row">
                                <div class="col-md-4 cs-field"><label class="cs-label">{{ __('Hardware price (KES)') }}</label><input type="number" step="0.01" name="unit_price" class="cs-input" value="{{ old('unit_price', optional($catalogue)->unit_price) }}"></div>
                                <div class="col-md-4 cs-field"><label class="cs-label">{{ __('Installation cost (KES)') }}</label><input type="number" step="0.01" name="installation_cost" class="cs-input" value="{{ old('installation_cost', optional($catalogue)->installation_cost) }}"></div>
                                <div class="col-md-4 cs-field"><label class="cs-label">{{ __('Visibility') }}</label>
                                    <div style="display:flex;gap:16px;padding-top:8px;">
                                        <label style="font-size:13px;"><input type="checkbox" name="is_financeable" value="1" @checked($module->is_financeable)> {{ __('Financeable') }}</label>
                                        <label style="font-size:13px;"><input type="checkbox" name="is_active" value="1" @checked($module->is_active)> {{ __('Active') }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="cs-field">
                                <label class="cs-label">{{ __('Facility paid to (settlement target)') }}</label>
                                <select name="settlement_target" class="cs-select">
                                    <option value="centresidence" @selected(($module->settlement_target ?? 'centresidence') === 'centresidence')>{{ __('Centresidence (official installer — infrastructure modules)') }}</option>
                                    <option value="owner" @selected(($module->settlement_target ?? '') === 'owner')>{{ __('Owner (owner is the receiving party)') }}</option>
                                </select>
                                <small class="cs-muted">{{ __('Who the financier disburses to. Infrastructure modules are installed and invoiced by Centresidence.') }}</small>
                            </div>

                            @if ($module->is_metered)
                                <div class="row" style="border-top:0.5px solid var(--gray-200);padding-top:14px;margin-top:6px;">
                                    <div class="col-12"><div class="cs-section__label" style="margin-bottom:8px;">{{ __('Token economics (metered)') }}</div></div>
                                    <div class="col-md-4 cs-field">
                                        <label class="cs-label">{{ __('Units per KES') }}</label>
                                        <input type="number" step="0.0001" min="0" name="token_units_per_kes" class="cs-input" value="{{ old('token_units_per_kes', $module->token_units_per_kes) }}" placeholder="e.g. 5">
                                        <small class="cs-muted">{{ $module->token_unit_label ?? __('units') }} {{ __('a tenant gets per KES paid.') }}</small>
                                    </div>
                                    <div class="col-md-4 cs-field">
                                        <label class="cs-label">{{ __('Commission per unit (KES)') }}</label>
                                        <input type="number" step="0.0001" min="0" name="token_commission_per_unit" class="cs-input" value="{{ old('token_commission_per_unit', $module->token_commission_per_unit ?? 0) }}" placeholder="0">
                                        <small class="cs-muted">{{ __('Centresidence income share per unit. Keep 0 — owners keep utility revenue — and set only for an income-share module (e.g. gas).') }}</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <button type="submit" class="cs-btn cs-btn--primary">{{ __('Save module') }}</button>
                    <a href="{{ route('admin.centresidence.modules') }}" class="cs-btn cs-btn--ghost">{{ __('Cancel') }}</a>
                </div>

                {{-- Live card preview --}}
                <div class="col-lg-4" style="align-self:flex-start;position:sticky;top:20px;">
                    <div class="cs-section__label">{{ __('Owner card preview') }}</div>
                    <div class="cs-modcard" style="max-width:280px;">
                        <div id="pvMedia" class="cs-modcard__media" style="background:linear-gradient(135deg, {{ $module->displayColor() }}, {{ $module->displayColor() }}cc);">
                            <i id="pvIcon" class="{{ $module->displayIcon() }}"></i>
                        </div>
                        <div class="cs-modcard__body">
                            <div id="pvName" class="cs-modcard__name">{{ $module->name }}</div>
                            <div id="pvTag" class="cs-modcard__tag">{{ $module->tagline ?? $module->description }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Cost components — the cost model + rate that drive owner billing (handbook §20) --}}
        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Cost components (billing)') }}</h2></div>
            <div class="cs-card__body">
                <p class="cs-muted" style="margin-bottom:14px;">
                    {{ __('These define what the owner is billed each cycle — the cost model and rate of each component. Edit a row inline and Save, or add a new one below.') }}
                </p>

                {{-- Per-row update + delete forms (referenced via the HTML5 form attribute) --}}
                @foreach ($module->costComponents as $c)
                    <form id="ccform-{{ $c->id }}" method="POST" action="{{ route('admin.centresidence.cost-components.update', $c->id) }}" class="d-none">@csrf @method('PUT')</form>
                    <form id="ccdel-{{ $c->id }}" method="POST" action="{{ route('admin.centresidence.cost-components.destroy', $c->id) }}" class="d-none" data-cs-confirm="{{ __('Remove this cost component?') }}" data-cs-confirm-tone="danger" data-cs-confirm-ok="{{ __('Remove') }}">@csrf @method('DELETE')</form>
                @endforeach

                <div class="cs-tablewrap">
                    <table class="cs-table">
                        <thead><tr>
                            <th>{{ __('Component') }}</th><th>{{ __('Cost model') }}</th><th>{{ __('Rate (KES)') }}</th>
                            <th>{{ __('Gateway') }}</th><th>{{ __('Fallback') }}</th><th>{{ __('Prorated') }}</th><th>{{ __('Status') }}</th><th></th>
                        </tr></thead>
                        <tbody>
                            @forelse ($module->costComponents as $c)
                                <tr>
                                    <td><input name="component_name" form="ccform-{{ $c->id }}" class="cs-input cs-input--sm" value="{{ $c->component_name }}" style="min-width:150px;"></td>
                                    <td>
                                        <select name="cost_model" form="ccform-{{ $c->id }}" class="cs-input cs-input--sm">
                                            @foreach ($costModels as $cm)
                                                <option value="{{ $cm }}" @selected($c->cost_model === $cm)>{{ str_replace('_', ' ', $cm) }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" step="0.0001" min="0" name="rate" form="ccform-{{ $c->id }}" class="cs-input cs-input--sm" value="{{ $c->rate }}" style="width:110px;"></td>
                                    <td style="text-align:center;"><input type="checkbox" name="requires_gateway" value="1" form="ccform-{{ $c->id }}" @checked($c->requires_gateway)></td>
                                    <td style="text-align:center;"><input type="checkbox" name="is_fallback_eligible" value="1" form="ccform-{{ $c->id }}" @checked($c->is_fallback_eligible)></td>
                                    <td style="text-align:center;"><input type="checkbox" name="is_prorated" value="1" form="ccform-{{ $c->id }}" @checked($c->is_prorated)></td>
                                    <td>
                                        <select name="status" form="ccform-{{ $c->id }}" class="cs-input cs-input--sm">
                                            <option value="active" @selected($c->status === 'active')>{{ __('Active') }}</option>
                                            <option value="inactive" @selected($c->status === 'inactive')>{{ __('Inactive') }}</option>
                                        </select>
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <button type="submit" form="ccform-{{ $c->id }}" class="cs-btn cs-btn--ghost cs-btn--sm">{{ __('Save') }}</button>
                                        <button type="submit" form="ccdel-{{ $c->id }}" class="cs-btn cs-btn--ghost cs-btn--sm">✕</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="cs-empty">{{ __('No cost components yet — add one below.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Add a cost component --}}
                <form method="POST" action="{{ route('admin.centresidence.modules.cost-components.store', $module->id) }}" style="margin-top:16px;border-top:0.5px solid var(--gray-200);padding-top:16px;">
                    @csrf
                    <div class="cs-section__label">{{ __('Add a cost component') }}</div>
                    <div class="row">
                        <div class="col-md-3 cs-field"><label class="cs-label">{{ __('Component name') }}</label><input name="component_name" class="cs-input" required placeholder="e.g. platform_software_fee"></div>
                        <div class="col-md-3 cs-field"><label class="cs-label">{{ __('Cost model') }}</label>
                            <select name="cost_model" class="cs-select">
                                @foreach ($costModels as $cm)<option value="{{ $cm }}">{{ str_replace('_', ' ', $cm) }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-2 cs-field"><label class="cs-label">{{ __('Rate (KES)') }}</label><input type="number" step="0.0001" min="0" name="rate" class="cs-input" value="0" required></div>
                        <div class="col-md-2 cs-field"><label class="cs-label">{{ __('Status') }}</label>
                            <select name="status" class="cs-select"><option value="active">{{ __('Active') }}</option><option value="inactive">{{ __('Inactive') }}</option></select>
                        </div>
                        <div class="col-md-2 cs-field" style="display:flex;align-items:flex-end;"><button type="submit" class="cs-btn cs-btn--primary" style="width:100%;justify-content:center;">{{ __('Add') }}</button></div>
                    </div>
                    <div style="display:flex;gap:18px;font-size:12.5px;color:var(--gray-700);">
                        <label><input type="checkbox" name="requires_gateway" value="1"> {{ __('Requires gateway') }}</label>
                        <label><input type="checkbox" name="is_fallback_eligible" value="1"> {{ __('Fallback eligible') }}</label>
                        <label><input type="checkbox" name="is_prorated" value="1" checked> {{ __('Prorated') }}</label>
                    </div>
                </form>
            </div>
        </div>

        <script>
            (function () {
                var color = document.getElementById('fColor'), icon = document.getElementById('fIcon'),
                    name = document.getElementById('fName'), tag = document.getElementById('fTag');
                function sync() {
                    var c = color.value || '#185FA5';
                    document.getElementById('pvMedia').style.background = 'linear-gradient(135deg,' + c + ',' + c + 'cc)';
                    document.getElementById('pvIcon').className = icon.value || 'ri-dashboard-3-line';
                    document.getElementById('pvName').textContent = name.value || 'Module';
                    document.getElementById('pvTag').textContent = tag.value || '';
                }
                [color, icon, name, tag].forEach(function (el) { el.addEventListener('input', sync); });
            })();
        </script>
    </div>
</div></div></div>
@endsection
