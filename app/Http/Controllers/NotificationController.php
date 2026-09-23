<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService;
    }
    /** Bell "Mark all as read" — shared by every account; marks the signed-in user's notices seen. */
    public function readAll()
    {
        \App\Models\Notification::where('user_id', auth()->id())->update(['is_seen' => ACTIVE]);
        return response()->json(['success' => true]);
    }

    public function status($id,$role,Request $request)
    {
        $data = $this->notificationService->status($id);
        if ($data->getData()->status == true) {
            $url = urldecode((string) $request->query('url'));

            // Redirect on the CURRENT host (localhost / ngrok tunnel / prod), never the
            // host that happened to generate the link (a CLI/queue job or webhook falls
            // back to APP_URL, a browser request uses its own host). Notification targets
            // are always internal routes, so redirect by path only — this also prevents
            // an ?url= open redirect to an external site.
            $parts = parse_url($url) ?: [];
            $path  = $parts['path'] ?? '/';
            if (! empty($parts['query']))    { $path .= '?' . $parts['query']; }
            if (! empty($parts['fragment'])) { $path .= '#' . $parts['fragment']; }
            if ($path === '') { $path = '/'; }

            return redirect($path);
        }else {
            return redirect()->back()->with('error', __(SOMETHING_WENT_WRONG));
        }
    }
}
