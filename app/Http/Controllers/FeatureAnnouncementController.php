<?php

namespace App\Http\Controllers;

use App\Models\FeatureAnnouncement;
use Illuminate\Http\Request;

class FeatureAnnouncementController extends Controller
{
    /** Mark an announcement seen for the current user (idempotent) so it stops showing. */
    public function seen(Request $request, FeatureAnnouncement $announcement)
    {
        $announcement->seenBy()->syncWithoutDetaching([
            auth()->id() => ['seen_at' => now()],
        ]);

        return $request->ajax() ? response()->json(['ok' => true]) : back();
    }
}
