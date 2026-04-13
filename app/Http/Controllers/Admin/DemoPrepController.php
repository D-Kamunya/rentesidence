<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoPrepSection;
use App\Models\DemoSetting;
use Illuminate\Http\Request;

class DemoPrepController extends Controller
{
    public function index()
    {
        $sections = DemoPrepSection::orderBy('sort_order')->get();
        $settings = DemoSetting::current();
        return view('admin.affiliates.demo_prep.index', compact('sections', 'settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'demo_login_url' => 'nullable|url',
            'demo_email'     => 'nullable|email',
            'demo_password'  => 'nullable|string|max:255',
            'demo_notes'     => 'nullable|string|max:1000',
        ]);

        DemoSetting::current()->update($request->only([
            'demo_login_url',
            'demo_email',
            'demo_password',
            'demo_notes',
        ]));

        return back()->with('success', 'Demo account details updated.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'content'    => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'nullable|boolean',
        ]);

        DemoPrepSection::create([
            'title'      => $request->title,
            'content' => trim($request->content),
            'sort_order' => $request->sort_order ?? DemoPrepSection::max('sort_order') + 1,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()-> route('admin.demo_prep.index')
            ->with('success', 'Section added succesfully.');
    }

    public function edit($id)
    {
        $section  = DemoPrepSection::findOrFail($id);
        $sections = DemoPrepSection::orderBy('sort_order')->get();
        $settings = DemoSetting::current();
        return view('admin.affiliates.demo_prep.index', compact('sections', 'settings', 'section'));
        
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'content'    => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'nullable|boolean',
        ]);

        DemoPrepSection::findOrFail($id)->update([
            'title'      => $request->title,
            'content' => trim($request->content),
            'sort_order' => $request->sort_order ?? 0,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.demo_prep.index')
            ->with('success', 'Section updated.');
    }

    public function destroy($id)
    {
        DemoPrepSection::findOrFail($id)->delete();
        return redirect()->route('admin.demo_prep.index')
            ->with('success', 'Section deleted.');
    }
}