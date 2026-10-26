<?php

namespace App\Http\Controllers\Affiliates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademyModule;
use App\Models\AffiliateAcademyProgress;

class AcademyController extends Controller
{
    /** Resolve the signed-in user's affiliate profile, or fail cleanly (no null crash). */
    private function affiliateOrRedirect()
    {
        $affiliate = auth()->user()?->affiliate;
        abort_unless($affiliate, 403, __('Your affiliate profile is not set up yet. Please contact support.'));

        return $affiliate;
    }

    /**
     * Show list of modules
     */
    public function index()
    {
        $affiliate = $this->affiliateOrRedirect();

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
        $affiliate = $this->affiliateOrRedirect();
    
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
        $affiliate = $this->affiliateOrRedirect();

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

        // Flag for admin review if failed 3 times — and alert admins so a blocked (paused) affiliate
        // never sits unnoticed. Fires once, on the submit that reaches the 3rd failed attempt.
        if ($progress->attempts >= 3 && $percentage < 80) {
            $progress->needs_review = true;
            $this->notifyAdminsOfBlock($module);
        }

        $progress->save();

        // Update affiliate academy status
        $totalModules = AcademyModule::where('is_active', true)->count();
        $completedModules = AffiliateAcademyProgress::where('affiliate_id', $affiliate->id)
            ->whereNotNull('completed_at')
            ->count();
        
        // Graduation is STICKY and forward-only: once an affiliate has completed the course they
        // stay 'completed' even after we ADD new modules later — new content never un-certifies
        // them or re-locks their tools (no forced re-training). A not-yet-graduated affiliate still
        // needs every current module to reach 'completed'; new ones surface as optional "new content".
        if ($completedModules >= $totalModules) {
            if ($affiliate->academy_status !== 'completed') {
                $affiliate->academy_certified_at = now();
            }
            $affiliate->academy_status = 'completed';
        } elseif ($affiliate->academy_status !== 'completed') {
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

    /** Alert every admin (bell) that the signed-in affiliate is blocked on a module and needs a reset. */
    private function notifyAdminsOfBlock(AcademyModule $module): void
    {
        try {
            $affiliateUser = auth()->user();
            if (! $affiliateUser) {
                return;
            }
            $who = trim(($affiliateUser->first_name ?? '') . ' ' . ($affiliateUser->last_name ?? '')) ?: ($affiliateUser->email ?? __('An affiliate'));
            $url = \Illuminate\Support\Facades\Route::has('admin.affiliates.performance')
                ? route('admin.affiliates.performance') : url('/admin');

            \App\Models\User::where('role', USER_ROLE_ADMIN)->pluck('id')->each(function ($adminId) use ($who, $module, $affiliateUser, $url) {
                addNotification(
                    __('An affiliate is blocked on the academy'),
                    __(':who failed ":module" 3 times — reset their retake so they can continue.', ['who' => $who, 'module' => $module->title]),
                    $url,
                    null,
                    $adminId,
                    (int) $affiliateUser->id
                );
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Academy block notify failed: ' . $e->getMessage());
        }
    }

    /**
     * Certificate
     */
    public function certificate()
    {
        $affiliate = $this->affiliateOrRedirect();

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