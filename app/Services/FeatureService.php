<?php

namespace App\Services;

use App\Models\Feature;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class FeatureService
{
    use ResponseTrait;
    public function getAll()
    {
        return Feature::all();
    }

    public function getActiveAll()
    {
        return Feature::where('status', ACTIVE)->get();
    }

    public function getInfo($id)
    {
        return Feature::findOrFail($id);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $id = $request->get('id', '');
            if ($id != '') {
                $ticket = Feature::findOrFail($request->id);
            } else {
                $ticket = new Feature();
            }
            $ticket->title = $request->title;
            $ticket->summary = $request->summary;
            $ticket->status = $request->status;
            $ticket->save();

            DB::commit();
            $message = $request->id ? __(UPDATED_SUCCESSFULLY) : __(CREATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function delete($id)
    {
        try {
            $ticket = Feature::findOrFail($id);
            $ticket->delete();
            return redirect()->back()->with('success', __(DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }
}
