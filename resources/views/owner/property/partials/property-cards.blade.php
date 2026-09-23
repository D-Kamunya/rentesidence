                    @if($properties->isEmpty())
                        <div class="prop-empty">
                            <img src="{{ asset('assets/images/empty-img.png') }}" alt="" class="prop-empty__img">
                            <p class="prop-empty__text">{{ __('Empty Property') }}</p>
                        </div>
                    @else
                        <div class="prop-grid">
                            @foreach($properties as $property)
                            <div class="prop-card">

                                {{-- Thumbnail --}}
                                <a href="{{ route('owner.property.show', $property->id) }}"
                                   class="prop-card__img-wrap">
                                    <img src="{{ $property->thumbnail_image }}" alt="" class="prop-card__img">
                                </a>

                                <div class="prop-card__body">

                                    {{-- Title + dropdown --}}
                                    <div class="prop-card__title-row">
                                        <a href="{{ route('owner.property.show', $property->id) }}"
                                           class="prop-card__name">{{ substr_replace($property->name, '...', 20) }}</a>

                                        <div class="dropdown">
                                            <a class="prop-card__more dropdown-toggle dropdown-toggle-nocaret"
                                               href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-2-fill"></i>
                                            </a>
                                            <ul class="dropdown-menu {{ selectedLanguage()->rtl == 1 ? 'dropdown-menu-start' : 'dropdown-menu-end' }}">
                                                <li>
                                                    <a class="dropdown-item font-13"
                                                       href="{{ route('owner.property.edit', $property->id) }}"
                                                       title="{{ __('Edit') }}">{{ __('Edit') }}</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item font-13 deleteItem"
                                                       data-formid="delete_row_form_{{ $property->id }}"
                                                       href="#"
                                                       title="{{ __('Delete') }}">{{ __('Delete') }}</a>
                                                    <form action="{{ route('owner.property.destroy', [$property->id]) }}"
                                                          method="post"
                                                          id="delete_row_form_{{ $property->id }}">
                                                        {{ method_field('DELETE') }}
                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    {{-- Address --}}
                                    <div class="prop-card__address">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                        </svg>
                                        <span>{{ $property->propertyDetail?->address }}</span>
                                    </div>

                                    {{-- Stats row --}}
                                    <div class="prop-card__stats">
                                        <div class="prop-stat">
                                            <span class="prop-stat__label">{{ __('Units') }}</span>
                                            <span class="prop-stat__value">{{ $property->number_of_unit }}</span>
                                        </div>
                                        <div class="prop-stat__divider"></div>
                                        <div class="prop-stat">
                                            <span class="prop-stat__label">{{ __('Rooms') }}</span>
                                            <span class="prop-stat__value">{{ propertyTotalRoom($property->id) }}</span>
                                        </div>
                                        <div class="prop-stat__divider"></div>
                                        <div class="prop-stat">
                                            <span class="prop-stat__label">{{ __('Available') }}</span>
                                            <span class="prop-stat__value prop-stat__value--green">{{ $property->available_unit }}</span>
                                        </div>
                                    </div>

                                </div>

                                {{-- Footer CTA --}}
                                <div class="prop-card__footer">
                                    <a href="{{ route('owner.property.show', $property->id) }}"
                                       class="prop-card__cta"
                                       title="{{ __('View Details') }}">
                                        {{ __('View Details') }}
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
                                            <polyline points="9 18 15 12 9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </div>

                            </div>
                            @endforeach
                        </div>

                        <div class="prop-pagination">
                            {{ $properties->appends(request()->only("search"))->links() }}
                        </div>
                    @endif
