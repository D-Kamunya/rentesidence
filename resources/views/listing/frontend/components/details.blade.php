<div class="card property-woner-location-info">
    <img src="{{ $image }}" class="card-img-top property-woner-img" alt="{{ $listing->name }}">
    <div class="woner-property-card-body">
        <h5 class="card-title woner-property-title">{{ $listing->name }}</h5>
        <p class="card-text woner-property-details">{{ Str::limit($listing->details, 100, '...') }}</p>
        <a href="{{ route('listing.details', $listing->slug) }}" class="details-btu-property">{{ __('Details') }}</a>
    </div>
</div>
