<?php

use App\Http\Controllers\Listing\Owner\ListingController;
use App\Http\Controllers\Listing\FrontendListingController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'owner', 'as' => 'owner.', 'middleware' => ['auth', 'owner']], function () {
    Route::group(['prefix' => 'listing', 'as' => 'listing.'], function () {
        Route::get('/', [ListingController::class, 'index'])->name('index');
        Route::get('create', [ListingController::class, 'create'])->name('create');
        Route::post('store', [ListingController::class, 'store'])->name('store');
        Route::get('edit/{id}', [ListingController::class, 'edit'])->name('edit');
        Route::get('delete/{id}', [ListingController::class, 'delete'])->name('delete');
        Route::get('image-delete/{id}', [ListingController::class, 'imageDelete'])->name('image.delete');
        Route::get('contact', [ListingController::class, 'contact'])->name('contact');
        Route::get('contact-info', [ListingController::class, 'contactInfo'])->name('contact.info'); // ajax
        Route::post('contact-reply', [ListingController::class, 'contactReply'])->name('contact.reply'); // ajax
    });
});

Route::group(['middleware' => ['version.update', 'addon.update']], function () {
    Route::get('properties', [FrontendListingController::class, 'list'])->name('listings');
    Route::get('properties/{slug}', [FrontendListingController::class, 'details'])->name('listing.details');
    Route::post('property-contact-store', [FrontendListingController::class, 'contactStore'])->name('listing.contact.store');
});
