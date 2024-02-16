<?php

namespace App\Services;

use App\Models\HowItWork;
use App\Models\FileManager;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class HowItWorkService
{
    use ResponseTrait;

    public function getAll()
    {
        return HowItWork::all();
    }

    public function getActiveAll()
    {
        return HowItWork::where('status', ACTIVE)->get();
    }

    public function getInfo($id)
    {
        return HowItWork::findOrFail($id);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $id = $request->get('id', '');
            if ($id != '') {
                $howItWork = HowItWork::findOrFail($request->id);
            } else {
                $howItWork = new HowItWork();
            }
            $howItWork->title = $request->title;
            $howItWork->summary = $request->summary;
            $howItWork->content = $request->content;
            $howItWork->status = $request->status;
            $howItWork->save();

            /*File Manager Call upload*/
            if ($request->hasFile('image')) {
                $new_file = FileManager::where('origin_type', 'App\Models\HowItWork')->where('origin_id', $howItWork->id)->first();

                if ($new_file) {
                    $new_file->removeFile();
                    $upload = $new_file->updateUpload($new_file->id, 'HowItWork', $request->image);
                } else {
                    $new_file = new FileManager();
                    $upload = $new_file->upload('HowItWork', $request->image);
                }

                if ($upload['status']) {
                    $upload['file']->origin_id = $howItWork->id;
                    $upload['file']->origin_type = "App\Models\HowItWork";
                    $upload['file']->save();
                } else {
                    throw new Exception($upload['message']);
                }
            }
            /*End*/

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
            $howItWork = HowItWork::findOrFail($id);
            $howItWork->delete();
            return redirect()->back()->with('success', __(DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return redirect()->back()->with('error', __($e->getMessage()));
        }
    }
}
