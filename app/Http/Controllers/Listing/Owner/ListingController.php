<?php

namespace App\Http\Controllers\Listing\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListingContactReplyRequest;
use App\Http\Requests\ListingRequest;
use App\Services\Listing\ListingService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    use ResponseTrait;
    public $listingService;

    public function __construct()
    {
        if (getOption('LISTING_STATUS', 0) != ACTIVE) {
            abort(404);
        }
        $this->listingService = new ListingService;
    }

    public function index()
    {
        $data['pageTitle'] = __('All List');
        $data['listings'] = $this->listingService->getAllByOwner();
        return view('listing.owner.index', $data);
    }

    public function create()
    {
        $data['pageTitle'] = __('Upload List');
        return view('listing.owner.create', $data);
    }

    public function store(ListingRequest $request)
    {
        return $this->listingService->store($request);
    }

    public function edit($id)
    {
        $data['pageTitle'] = __('Edit List');
        $data['listing'] = $this->listingService->getById($id);
        $data['images'] = $this->listingService->getImages($id);
        $data['information'] = $this->listingService->getInfoByListId($id);
        return view('listing.owner.edit', $data);
    }

    public function delete($id)
    {
        return $this->listingService->delete($id);
    }

    public function imageDelete($id)
    {
        return $this->listingService->imageDelete($id);
    }

    public function contact(Request $request)
    {
        $data['pageTitle'] = __('Contact List');
        if ($request->ajax()) {
            return $data['listings'] = $this->listingService->getAllContactByOwner();
        }
        return view('listing.owner.contact', $data);
    }

    public function contactInfo(Request $request)
    {
        return $this->listingService->getContactInfo($request->id);
    }

    public function contactReply(ListingContactReplyRequest $request)
    {
        return $this->listingService->contactReply($request);
    }
}
