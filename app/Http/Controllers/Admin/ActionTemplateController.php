<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActionTemplate;
use App\Models\MarketingMaterial;

class ActionTemplateController extends Controller
{
    /**
     * List all templates
     */
    public function index()
    {
        $templates = ActionTemplate::with('materials')->latest()->paginate(10);
        // $template = ActionTemplate::with('materials')->findOrFail($id);
        return view('admin.affiliates.templates.index', compact('templates'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $materials = MarketingMaterial::where('is_active', 1)->get();

        return view('admin.affiliates.templates.create', compact('materials'));
    }

    /**
     * Store new template
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'action_type' => 'required|in:whatsapp,email,call',
            'category' => 'required|string|max:100',
            'message_template' => 'nullable|string',
            'material_ids' => 'nullable|array'
        ]);

        $template = ActionTemplate::create([
            'name' => $request->name,
            'action_type' => $request->action_type,
            'category' => $request->category,
            'message_template' => $request->message_template,
        ]);

        // Attach materials
        if ($request->has('material_ids')) {
            $template->materials()->sync($request->material_ids);
        }

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template created successfully');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $template = ActionTemplate::with('materials')->findOrFail($id);
        $materials = MarketingMaterial::where('is_active', 1)->get();

        return view('admin.affiliates.templates.edit', compact('template', 'materials'));
    }

    /**
     * Update template
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'action_type' => 'required|in:whatsapp,email,call',
            'category' => 'required|string|max:100',
            'message_template' => 'nullable|string',
            'material_ids' => 'nullable|array'
        ]);

        $template = ActionTemplate::findOrFail($id);

        $template->update([
            'name' => $request->name,
            'action_type' => $request->action_type,
            'category' => $request->category,
            'message_template' => $request->message_template,
        ]);

        // Sync materials
        $template->materials()->sync($request->material_ids ?? []);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template updated successfully');
    }

    /**
     * Delete template
     */
    public function destroy($id)
    {
        $template = ActionTemplate::findOrFail($id);

        $template->materials()->detach();
        $template->delete();

        return redirect()
            ->route('admin.templates.index')
            ->with('success', 'Template deleted');
    }

}