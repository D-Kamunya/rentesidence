<?php

namespace App\Services;

use App\Models\FileManager;
use App\Models\Testimonial;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class TestimonialService
{
    use ResponseTrait;

    public function getAll()
    {
        return Testimonial::all();
    }

    public function getActiveAll()
    {
        return Testimonial::where('status', ACTIVE)->get();
    }

    public function getInfo($id)
    {
        return Testimonial::findOrFail($id);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $id = $request->get('id', '');
            if ($id != '') {
                $testimonial = Testimonial::findOrFail($request->id);
            } else {
                $testimonial = new Testimonial();
            }
            $testimonial->name = $request->name;
            $testimonial->designation = $request->designation;
            $testimonial->comment = $request->comment;
            $testimonial->star = $request->star <= 5 ? $request->star : 5;
            $testimonial->status = $request->status;
            $testimonial->save();

            /*File Manager Call upload*/
            if ($request->hasFile('image')) {
                $new_file = FileManager::where('origin_type', 'App\Models\Testimonial')->where('origin_id', $testimonial->id)->first();
                if ($new_file) {
                    $new_file->removeFile();
                    $upload = $new_file->updateUpload($new_file->id, 'Testimonial', $request->image);
                } else {
                    $new_file = new FileManager();
                    $upload = $new_file->upload('Testimonial', $request->image);
                }

                if ($upload['status']) {
                    $upload['file']->origin_id = $testimonial->id;
                    $upload['file']->origin_type = "App\Models\Testimonial";
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
            $testimonial = Testimonial::findOrFail($id);
            $testimonial->delete();
            return redirect()->back()->with('success', __(DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return redirect()->back()->with('error', __($e->getMessage()));
        }
    }
}
