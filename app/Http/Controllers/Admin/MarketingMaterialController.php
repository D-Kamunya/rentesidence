<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MarketingMaterial;

class MarketingMaterialController extends Controller
{

    public function index()
    {
        // $materials = MarketingMaterial::where('is_active',1)->get()->paginate(10);
        $materials = MarketingMaterial::latest()->paginate(15);
        return view('admin.affiliates.materials.index', compact('materials'));
    }
    
    public function create()
    {
        return view('admin.affiliates.materials.create'); 
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:pdf,text,link,png',
            'category' => 'required|string',
            'priority' => 'nullable|integer',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'content' => 'nullable|string',
        ]);

        // 🔥 Conditional validation
        if (in_array($request->type, ['text', 'link'])) {
            $request->validate([
                'content' => 'required|string'
            ]);
        }

        if (in_array($request->type, ['pdf', 'png'])) {
            $request->validate([
                'file' => 'required|file'
            ]);
        }

        $data = [
            'title' => $request->title,
            'type' => $request->type,
            'content' => $request->content,
            'category' => $request->category,
            'priority' => $request->priority ?? 1,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->store('materials', 'public'); // saves to storage/app/public/materials
            $data['file_name'] = $file->getClientOriginalName();
        }

        MarketingMaterial::create($data);

        return redirect()->route('admin.materials.index')->with('success', 'Material created');
    }


    public function edit($id)
    {
        $material = MarketingMaterial::findOrFail($id);
        return view('admin.affiliates.materials.edit', compact('material'));
    }

    public function update(Request $request, $id)
    {
        $material = MarketingMaterial::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:pdf,text,link,png',
            'content' => 'nullable| string',
            'category' => 'required|string',
            'priority' => 'nullable|integer',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = [
            'title' => $request->title,
            'type' => $request->type,
            'content' => $request->content,
            'category' => $request->category,
            'priority' => $request->priority ?? 1,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->store('materials', 'public');
            $data['file_name'] = $file->getClientOriginalName();
        }

        $material->update($data);

        return redirect()->route('admin.materials.index')->with('success', 'Material updated');
    }


    public function destroy($id)
    {
        MarketingMaterial::findOrFail($id)->delete();
        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Material deleted');
    }
}

