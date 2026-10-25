{{-- Shared affiliate location fields (admin add + edit). Reads the SAME config('centresidence.markets')
     as the public apply form (single source of truth, expansion = config only). Optional on the admin
     side — an admin often won't know the county — but a chosen county must belong to the chosen country.
     Powers the admin coverage cards. Pass current values via $locCountry / $locCounty / $locTown. --}}
@php
  $locCountry = $locCountry ?? '';
  $locCounty  = $locCounty ?? '';
  $locTown    = $locTown ?? '';
  $__affMarkets = collect(csMarkets())->mapWithKeys(fn ($m) => [$m['name'] => array_values($m['divisions'] ?? [])])->all();
@endphp
<div class="add-property-title border-bottom pb-25 mb-25 mt-25">
  <h4>{{ __('Location') }} <span style="font-weight:400;color:#9aa2ad;font-size:13px;">— {{ __('optional; powers the coverage report') }}</span></h4>
</div>
<div class="row">
  <div class="col-md-4 mb-25">
    <label class="label-text-title color-heading font-medium mb-2">{{ __('Country') }}</label>
    <select name="country" id="affLocCountry" class="form-control">
      <option value="">{{ __('Select country') }}</option>
      @foreach (csMarketCountries() as $c)
        <option value="{{ $c }}" {{ $locCountry === $c ? 'selected' : '' }}>{{ $c }}</option>
      @endforeach
    </select>
    @error('country')<span class="text-danger">{{ $message }}</span>@enderror
  </div>
  <div class="col-md-4 mb-25">
    <label class="label-text-title color-heading font-medium mb-2">{{ __('County') }}</label>
    <select name="county" id="affLocCounty" class="form-control" data-old="{{ $locCounty }}">
      <option value="">{{ __('Select county') }}</option>
    </select>
    @error('county')<span class="text-danger">{{ $message }}</span>@enderror
  </div>
  <div class="col-md-4 mb-25">
    <label class="label-text-title color-heading font-medium mb-2">{{ __('Town / area') }}</label>
    <input type="text" name="town" class="form-control" value="{{ $locTown }}" placeholder="{{ __('e.g. Ruaka') }}">
    @error('town')<span class="text-danger">{{ $message }}</span>@enderror
  </div>
</div>
<script>
  (function () {
    var MARKETS = @json($__affMarkets);
    var country = document.getElementById('affLocCountry');
    var county  = document.getElementById('affLocCounty');
    if (!country || !county) return;
    function fill(preselect) {
      var list = MARKETS[country.value] || [];
      county.innerHTML = '<option value="">{{ __('Select county') }}</option>';
      list.forEach(function (name) {
        var o = document.createElement('option');
        o.value = name; o.textContent = name;
        if (name === preselect) o.selected = true;
        county.appendChild(o);
      });
    }
    fill(county.getAttribute('data-old') || '');
    country.addEventListener('change', function () { fill(''); });
  })();
</script>
