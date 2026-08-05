<?php

namespace App\Http\Controllers\Affiliates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\ActionTemplate;
use App\Models\LeadActivity;
use App\Services\SuggestionService;
use App\Services\TemplateSubstitutionService;

class ActionExecutionController extends Controller
{
    public function __construct(
        protected TemplateSubstitutionService $substitution,
        protected SuggestionService $suggestions,
    ) {}

    // Shared descriptive lines for materials
    protected array $materialDescriptions = [
        'pdf'     => 'Here is a document you can use to have a deeper look at it:',
        'png'     => 'Here is an image you can check out:',
        'jpg'     => 'Here is an image you can check out:',
        'jpeg'    => 'Here is an image you can check out:',
        'link'    => 'Here is a useful link you can check out:',
        'text'    => '', // text doesn’t need a prefix
        'default' => 'Here is a file you may find useful:'
    ];
    // ═══════════════════════════════════════════════════════
    // WHATSAPP
    // ═══════════════════════════════════════════════════════
    public function whatsapp($leadId, $templateId)
    {
        $lead     = Lead::with(['company', 'affiliate'])->findOrFail($leadId);
        $this->authorizeLead($lead);
        $template = ActionTemplate::with('materials')->findOrFail($templateId);

        // Substitute placeholders using the service
        $message = $this->substitution->substitute($template->message_template, $lead);

        // Append material content (text/link types only — PDFs can't go in a WA message)
        foreach ($template->materials as $material) {
            if ($material->type === 'text' && $material->content) {
                $message .= "\n\n" . $material->content;
            } elseif ($material->type === 'link' && $material->content) {
                $message .= "\n\n" . $this->materialDescriptions['link'] . "\n" . $material->content;
            } elseif ($material->file_path) {
                // For files, append a link to the publicly accessible file
                $extension = strtolower(pathinfo($material->file_path, PATHINFO_EXTENSION));
                $description = $this->materialDescriptions[$extension] 
                    ?? $this->materialDescriptions['default'];

                $message .= "\n\n" . $description . "\n" . asset('storage/' . $material->file_path);
            }
            $material->increment('usage_count');
        }

        // Normalise phone for wa.me (needs digits only, no +, no leading 0)
        $phone = preg_replace('/\D/', '', $lead->company->phone ?? '');
        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        $this->suggestions->completeForLead($lead->id, 'whatsapp', $this->suggestionId());
        $this->logActivity($lead->id, 'whatsapp_sent', 'Sent WhatsApp message using template: ' . $template->name);

        return redirect()->away("https://wa.me/{$phone}?text=" . urlencode($message));
    }

    // ═══════════════════════════════════════════════════════
    // EMAIL
    // ═══════════════════════════════════════════════════════
    public function email($leadId, $templateId)
    {
        $lead     = Lead::with(['company', 'affiliate'])->findOrFail($leadId);
        $this->authorizeLead($lead);
        $template = ActionTemplate::with('materials')->findOrFail($templateId);

        // Substitute placeholders using the service
        $message = $this->substitution->substitute($template->message_template, $lead);

        $recipientEmail = $lead->company->email;

        if (!$recipientEmail) {
            return back()->with('error', 'This lead has no email address on file.');
        }

        // Determine subject from template category
        $subjects = [
            'intro'     => 'Introducing Our Property Management Platform',
            'reminder'  => 'Reminder: Your Upcoming Demo',
            'follow_up' => 'Following Up — ' . $lead->company->company_name,
            'trial'     => 'Your Trial Account — Next Steps',
            'demo_complete' => 'How was the Demo?',
            'retention' => 'How are things for - '.$lead->company->company_name,
            'reengage'  => 'Checking In — ' . $lead->company->company_name,
        ];
        $subject = $subjects[$template->category] ?? 'Message from ' . (auth()->user()->first_name ?? 'Your Account Manager');

        \Mail::raw($message, function ($mail) use ($lead, $subject, $template) {
            $mail->to($lead->company->email)->subject($subject);

            // Attach any PDF/image materials
            foreach ($template->materials as $material) {
                if ($material->file_path && in_array($material->type, ['pdf', 'png', 'jpg', 'jpeg'])) {
                    $path = storage_path('app/public/' . $material->file_path);
                    if (file_exists($path)) {
                        // Add a descriptive line before attaching
                        $extension = strtolower(pathinfo($material->file_path, PATHINFO_EXTENSION));
                        $description = $this->materialDescriptions[$extension] 
                            ?? $this->materialDescriptions['default'];

                        // You can include this description in the email body if needed
                        $mail->body .= "\n\n" . $description;

                        // Attach the file itself
                        $mail->attach($path, ['as' => $material->file_name ?? basename($path)]);
                    }
                } elseif ($material->type === 'link' && $material->content) {
                    $description = $this->materialDescriptions['link'];
                    $mail->body .= "\n\n" . $description . "\n" . $material->content;
                }

                $material->increment('usage_count');
            }

        });

        $this->suggestions->completeForLead($lead->id, 'email', $this->suggestionId());
        $this->logActivity($lead->id, 'email_sent', 'Sent email using template: ' . $template->name);

        return back()->with('success', 'Email sent to ' . $recipientEmail);
    }

    // ═══════════════════════════════════════════════════════
    // CALL
    // ═══════════════════════════════════════════════════════

    public function callView($leadId, $templateId)
    {
        $lead     = Lead::with(['company', 'affiliate'])->findOrFail($leadId);
        $this->authorizeLead($lead);
        $template = ActionTemplate::findOrFail($templateId);
        $script   = $this->substitution->substitute($template->message_template, $lead);
        $phone = $lead->company->phone ?? '';

        return view('affiliate.leads.call', [
            'lead'         => $lead,
            'script'       => $script,
            'phone'        => $phone,
            'suggestionId' => $this->suggestionId(),
        ]);
    }

    public function call(Request $request, $leadId)
    {
        $lead = Lead::with(['company', 'affiliate'])->findOrFail($leadId);
        $this->authorizeLead($lead);

        // Normalize phone
        $rawPhone = preg_replace('/\D/', '', $lead->company->phone ?? '');

        // If the number starts with "0", treat it as a legacy local number
        // and prepend the affiliate/company's default country code
        if (preg_match('/^0/', $rawPhone)) {
            $defaultCountryCode = $lead->affiliate->country_code ?? '254'; // fallback to Kenya
            $phone = $defaultCountryCode . substr($rawPhone, 1);
        } else {
            // Otherwise, assume the number already includes a valid country code
            $phone = $rawPhone;
        }

        // Always log here
        $this->suggestions->completeForLead($lead->id, 'call', $this->suggestionId());
        $this->logActivity($lead->id, 'call_made', 'Called lead — ' . $lead->company->company_name);

        // Direct dial
        return redirect()->away("tel:{$phone}");
    }


    // ═══════════════════════════════════════════════════════
    // SHARED HELPERS
    // ═══════════════════════════════════════════════════════

    /**
     * Enforce that the affiliate acting on this lead actually owns it. The blade
     * hides buttons for non-owned leads, but the action URLs are guessable, so
     * the guard must live here (prevents IDOR — acting on a competitor's lead).
     */
    private function authorizeLead(Lead $lead): void
    {
        if ((int) $lead->affiliate_id !== (int) auth()->id()) {
            abort(403);
        }
    }

    /** The suggestion the affiliate acted from, if the action link carried it. */
    private function suggestionId(): ?int
    {
        $id = request('suggestion');

        return $id ? (int) $id : null;
    }

    private function logActivity(int $leadId, string $type, string $description): void
    {
        LeadActivity::create([
            'lead_id'     => $leadId,
            'user_id'     => auth()->id(),
            'type'        => $type,
            'description' => $description,
        ]);
    }

}