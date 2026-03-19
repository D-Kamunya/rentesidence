<?php

namespace App\Http\Controllers\Affiliates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademyModule;
use App\Models\AffiliateAcademyProgress;

class AcademyController extends Controller
{
    /**
     * Show list of modules
     */
    public function index()
    {
        $affiliate = auth()->user()->affiliate;

        $modules = AcademyModule::where('is_active', true)
            ->orderBy('module_order')
            ->get();

        $progress = $affiliate->academyProgress->keyBy('module_id');

        return view('affiliate.academy.index', compact('modules', 'progress', 'affiliate'));
    }

    /**
     * Show a single module
     */
    public function show(AcademyModule $module, Request $request)
    {
        $affiliate = auth()->user()->affiliate;
    
        // Load all active modules
        $modules = AcademyModule::where('is_active', true)
            ->orderBy('module_order')
            ->get();
    
        $progress = $affiliate->academyProgress->keyBy('module_id');
    
        $moduleProgress = $progress[$module->id] ?? null;
    
        $reviewMode = $request->query('review') == '1'; // review_mode triggered via ?review=1
    
    
        // Prevent access if module is locked (sequential enforcement)
        if (!$reviewMode) {
    
            $nextLockedModule = null;
    
            foreach ($modules as $m) {
                if (!isset($progress[$m->id]) || !$progress[$m->id]->completed_at) {
                    $nextLockedModule = $m;
                    break;
                }
            }
    
            if ($nextLockedModule && $module->id !== $nextLockedModule->id) {
                return redirect()->route('affiliate.academy.index');
            }
        }
    
    
        // Determine the NEXT module after the current one
        $nextModule = $modules
            ->where('module_order', '>', $module->module_order)
            ->sortBy('module_order')
            ->first();
    
    
        // Load questions & options
        $module->load('questions.options');
    
        return view('affiliate.academy.show', [
            'module' => $module,
            'progress' => $progress,
            'reviewMode' => $reviewMode,
            'moduleProgress' => $moduleProgress,
            'nextModule' => $nextModule
        ]);
    }

    /**
     * Submit module answers
     */
    public function submit(Request $request, AcademyModule $module)
    {
        $affiliate = auth()->user()->affiliate;

        $progress = AffiliateAcademyProgress::firstOrCreate([
            'affiliate_id' => $affiliate->id,
            'module_id' => $module->id
        ]);

        // Max 3 attempts
        if ($progress->attempts >= 3) {
            $progress->needs_review = true;
            $progress->save();
            return back()->with('error', 'Maximum attempts reached. Admin review required.');
        }

        $module->load('questions.options');

        $score = 0;
        $total = $module->questions->count();

        foreach ($module->questions as $question) {
            $selected = $request->input('question_'.$question->id);
            $correct = $question->options->where('is_correct', true)->first();
            if ($correct && $selected == $correct->id) $score++;
        }

        $percentage = ($score / $total) * 100;

        $progress->attempts += 1;
        $progress->score = $percentage;

        if ($percentage >= 80) {
            $progress->completed_at = now();
        }

        // Flag for admin review if failed 3 times
        if ($progress->attempts >= 3 && $percentage < 80) {
            $progress->needs_review = true;
        }

        $progress->save();

        // Update affiliate academy status
        $totalModules = AcademyModule::where('is_active', true)->count();
        $completedModules = AffiliateAcademyProgress::where('affiliate_id', $affiliate->id)
            ->whereNotNull('completed_at')
            ->count();
        
        if ($completedModules == $totalModules) {
            $affiliate->academy_status = 'completed';
            $affiliate->academy_certified_at = now();
        } else {
            $affiliate->academy_status = 'in_progress';
        }
        $affiliate->save();

        // Determine the NEXT module after the current one
        $modules = AcademyModule::where('is_active', true)
            ->orderBy('module_order')
            ->get();
        $nextModule = $modules
            ->where('module_order', '>', $module->module_order)
            ->sortBy('module_order')
            ->first();
        return view('affiliate.academy.result', [
                'module' => $module,
                'percentage' => $percentage,
                'passed' => $percentage >= 80,
                'attempts' => $progress->attempts,
                'nextModule' => $nextModule,
            ]);
    }

    /**
     * Certificate
     */
    public function certificate()
    {
        $affiliate = auth()->user()->affiliate;

        $totalModules = AcademyModule::where('is_active', true)->count();

        $completedModules = $affiliate->academyProgress()
            ->whereNotNull('completed_at')
            ->count();

        if ($completedModules < $totalModules) {
            return redirect()->route('affiliate.academy.index')
                ->with('error', 'Complete all academy modules to unlock certification.');
        }

        $user = auth()->user();

        $date = now()->format('F d, Y');

        $pdf = \PDF::loadView('affiliate.academy.certificate', [
            'user' => $user,
            'date' => $date
        ])->setPaper('a4', 'landscape')
        ->setOption('dpi', 67);
        
        return $pdf->download('centresidence-certification.pdf');
    }
}