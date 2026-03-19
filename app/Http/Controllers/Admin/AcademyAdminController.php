<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AcademyModule;
use App\Models\Question;
use App\Models\Option;
use App\Models\Affiliate;
use App\Models\AffiliateAcademyProgress;


class AcademyAdminController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | MODULES
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $data['pageTitle'] = __('Academy Modules');
        $data['navAcademyModulesShowClass'] = 'active'; // for sidebar nav highlighting
        $data['modules'] = AcademyModule::orderBy('module_order')->get();

        return view('admin.academy.modules.index', $data);
    }

    public function create()
    {
        $data['pageTitle'] = __('create Module');
        $data['navAcademyModulesShowClass'] = 'active';

        return view('admin.academy.modules.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'youtube_url' => 'nullable|string',
            'duration_minutes' => 'nullable|integer',
            'content'      => 'required|string',
            'module_order' => 'required|integer',
            'is_active'    => 'nullable|boolean',
        ]);

        AcademyModule::create([
            'title'        => $request->title,
            'youtube_url' => $request->youtube_url,
            'duration_minutes' => $request->duration_minutes,
            'content'      => $request->content,
            'module_order' => $request->module_order,
            'is_active'    => $request->is_active ?? 0,
        ]);

        return redirect()
            ->route('admin.academy.index')
            ->with('success', 'Module created successfully.');
    }

    public function edit(AcademyModule $academy)
    {
        $data['pageTitle'] = __('Edit Module');
        $data['navAcademyModulesShowClass'] = 'active';
        $data['academy'] = $academy;

        return view('admin.academy.modules.edit', $data);
    }

    public function update(Request $request, AcademyModule $academy)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'youtube_url' => 'nullable|string',
            'duration_minutes' => 'nullable|integer',
            'content'      => 'required|string',
            'module_order' => 'required|integer',
            'is_active'    => 'nullable|boolean',
        ]);

        $academy->update([
            'title'        => $request->title,
            'youtube_url' => $request->youtube_url,
            'duration_minutes' => $request->duration_minutes,
            'content'      => $request->content,
            'module_order' => $request->module_order,
            'is_active'    => $request->is_active ?? 0,
        ]);

        return redirect()
            ->route('admin.academy.index')
            ->with('success', 'Module updated successfully.');
    }

    public function destroy(AcademyModule $academy)
    {
        $academy->delete();

        return redirect()
            ->route('admin.academy.index')
            ->with('success', 'Module deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | QUESTIONS (Nested Under Modules)
    |--------------------------------------------------------------------------
    */

    public function questions(AcademyModule $module)
    {
        $data['pageTitle'] = __('Manage Questions');
        $data['navAcademyModulesShowClass'] = 'active';
        $data['module'] = $module;
        $data['questions'] = $module->questions()->orderBy('question_order')->get();

        return view('admin.academy.questions.index', $data);
    }

    public function createQuestion(AcademyModule $module)
    {
        return view('admin.academy.questions.create', compact('module'));
    }

    public function storeQuestion(Request $request, AcademyModule $module)
    {
        $request->validate([
            'question'              => 'required|string|max:500',
            'question_order'        => 'required|integer',
            'options'               => 'required|array|min:2',
            'options.*.option_text' => 'required|string|max:255',
            'correct_option'        => 'required|integer',
        ]);


        DB::transaction(function () use ($request, $module) {

            $question = $module->questions()->create([
                'question'       => $request->question,
                'question_order' => $request->question_order,
            ]);

            foreach ($request->options as $index => $opt) {
                $question->options()->create([
                    'option_text' => $opt['option_text'],
                    'is_correct'  => $request->correct_option == $index ? 1 : 0,
                ]);
            }
        });

        return redirect()
            ->route('admin.academy.questions', $module->id)
            ->with('success', 'Question and options created successfully.');
    }

    public function destroyQuestion(Question $question)
    {
        $moduleId = $question->academy_module_id;

        $question->delete();

        return redirect()
            ->route('admin.academy.questions', $moduleId)
            ->with('success', 'Question deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | OPTIONS (Separate Delete Support)
    |--------------------------------------------------------------------------
    */

    public function destroyOption(Option $option)
    {
        $moduleId = $option->question->academy_module_id;

        $option->delete();

        return redirect()
            ->route('admin.academy.questions', $moduleId)
            ->with('success', 'Option deleted successfully.');
    }

    public function affiliatesPerformance()
    {
        $totalModules = AcademyModule::where('is_active', true)->count();

        // $affiliates = Affiliate::with(['user', 'academyProgress'])->get();//can be improved for performance
        $affiliates = Affiliate::with(['academyProgress.module'])->get();

        $affiliatesData = $affiliates->map(function ($affiliate) use ($totalModules) {

            $progressNeedsReview = $affiliate->academyProgress->firstWhere('needs_review', true);

            $progress = $affiliate->academyProgress;

            $completedModules = $progress
                ->whereNotNull('completed_at')
                ->count();

            $attempts = $progress->max('attempts') ?? 0;

            $lastActivity = $progress
                ->sortByDesc('updated_at')
                ->first()?->updated_at;

            $progressPercent = $totalModules > 0
                ? round(($completedModules / $totalModules) * 100)
                : 0;

            $isCertified = $totalModules > 0 && $completedModules === $totalModules;

            $needsReview = !$isCertified && $attempts >= 3;

            return [
                'id' => $affiliate->id,
                'name' => $affiliate->user->name,
                'completed_modules' => $completedModules,
                'total_modules' => $totalModules,
                'progress_percent' => $progressPercent,
                'attempts' => $attempts,
                'certified' => $isCertified,
                'module_id' => $progressNeedsReview->module_id ?? null,
                'needs_review' => $needsReview,
                'last_activity' => $lastActivity
            ];
        });

        return view('admin.affiliates.performance', [
            'affiliates' => $affiliatesData
        ]);
    }

    public function resetAffiliateModule($affiliateId, $moduleId)
    {
        // Find the affiliate
        $affiliate = Affiliate::findOrFail($affiliateId);
    
        // Find the specific progress record for this module
        $progress = AffiliateAcademyProgress::where('affiliate_id', $affiliate->id)
            ->where('module_id', $moduleId)
            ->first();
    
        if (!$progress) {
            return back()->with('error', 'Module progress not found for this affiliate.');
        }
    
        // Reset progress for this module only
        $progress->attempts = 0;
        $progress->score = null;
        $progress->completed_at = null;
        $progress->needs_review = false;
    
        $progress->save();
    
        return back()->with('success', "Module '{$progress->module->title}' has been reset. Affiliate can retake it.");
    }
    
}