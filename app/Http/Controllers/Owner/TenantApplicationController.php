<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApplicationAssignRequest;
use App\Models\EmailTemplate;
use App\Models\HouseHuntApplication;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Models\TenantDetails;
use App\Models\User;
use App\Services\SmsMail\MailService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantApplicationController extends Controller
{
    use ResponseTrait;

    // ──────────────────────────────────────────────────────────────────────────
    // INDEX — list all applications for the logged-in owner's properties
    // ──────────────────────────────────────────────────────────────────────────

    public function index()
    {
        $ownerId = auth()->id();

        $applications = HouseHuntApplication::with(['propertyUnit.property'])
            ->whereHas('propertyUnit.property', function ($q) use ($ownerId) {
                $q->where('owner_user_id', $ownerId);
            })
            ->latest()
            ->get();

        return view('owner.tenants.tenant-applications.index', compact('applications'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ASSIGN — convert a pending application into a full active tenant
    //
    // Flow mirrors TenantService@step1 + step2 + activate, condensed into one
    // atomic transaction. The owner only supplies lease details in the modal;
    // all personal/address data comes from the application record itself.
    // ──────────────────────────────────────────────────────────────────────────

    public function assign(TenantApplicationAssignRequest $request)
    {
        DB::beginTransaction();
        try {
            $ownerId = auth()->id();

            // ── 1. Load & guard the application ──────────────────────────────
            $application = HouseHuntApplication::with(['propertyUnit.property'])
                ->whereHas('propertyUnit.property', function ($q) use ($ownerId) {
                    $q->where('owner_user_id', $ownerId);
                })
                ->where('status', HOUSE_HUNT_APPLICATION_PENDING)
                ->findOrFail($request->application_id);

            // ── 2. Verify the unit belongs to this owner & is still vacant ───
            $unit = PropertyUnit::whereHas('property', function ($q) use ($ownerId) {
                $q->where('owner_user_id', $ownerId);
            })->findOrFail($request->unit_id);

            $unitTaken = Tenant::where('owner_user_id', $ownerId)
                ->where('unit_id', $unit->id)
                ->where('status', TENANT_STATUS_ACTIVE)
                ->exists();

            if ($unitTaken) {
                throw new Exception(__('This unit is already occupied by an active tenant.'));
            }

            // ── 3. Enforce owner tenant limit ─────────────────────────────────
            if (!getOwnerLimit(RULES_TENANT) > 0) {
                throw new Exception(__('Your Tenant Limit is Finished. Choose or Renew Package Plan'));
            }

            // ── 4. Create or reuse the User account ───────────────────────────
            // If the applicant already has a user account (they were logged in
            // when applying), reuse it. Otherwise create a fresh account with
            // an auto-generated password that we'll email to them.
            $autoPassword = null;

            if ($application->user_id) {
                $user = User::findOrFail($application->user_id);
            } else {
                // Check if a user already exists for this email (edge case:
                // someone applied as a guest but already has an account).
                $user = User::where('email', $application->email)->first();

                if (!$user) {
                    $autoPassword = Str::random(12);
                    $user         = new User();
                    $user->password = Hash::make($autoPassword);
                }
            }

            $user->first_name      = $application->first_name;
            $user->last_name       = $application->last_name;
            $user->email           = $application->email;
            $user->contact_number  = $application->contact_number;
            $user->role            = USER_ROLE_TENANT;
            $user->status          = ACTIVE;
            $user->owner_user_id   = $ownerId;
            $user->save();

            // ── 5. Create the Tenant record ───────────────────────────────────
            $tenant                      = new Tenant();
            $tenant->user_id             = $user->id;
            $tenant->owner_user_id       = $ownerId;
            $tenant->job                 = $application->job;
            $tenant->age                 = $application->age;
            $tenant->family_member       = $application->family_member;

            // Lease / unit fields (from the assign modal)
            $tenant->property_id            = $request->property_id;
            $tenant->unit_id                = $request->unit_id;
            $tenant->lease_start_date       = $request->lease_start_date;
            $tenant->lease_end_date         = $request->lease_end_date;
            $tenant->general_rent           = $request->general_rent;
            $tenant->due_date               = $request->due_date;
            $tenant->security_deposit_type  = $request->security_deposit_type  ?? 'fixed';
            $tenant->security_deposit       = $request->security_deposit        ?? 0;
            $tenant->late_fee_type          = $request->late_fee_type           ?? 'fixed';
            $tenant->late_fee               = $request->late_fee                ?? 0;
            $tenant->incident_receipt       = $request->incident_receipt        ?? 0;

            // Activate immediately (skip step3 document upload — owner can
            // add documents later from the tenant profile).
            $tenant->status = TENANT_STATUS_ACTIVE;
            $tenant->save();

            // ── 6. Create TenantDetails (address) ────────────────────────────
            $details                          = new TenantDetails();
            $details->tenant_id               = $tenant->id;
            $details->permanent_address       = $application->permanent_address;
            $details->permanent_country_id    = $application->permanent_country_id;
            $details->permanent_state_id      = $application->permanent_state_id;
            $details->permanent_city_id       = $application->permanent_city_id;
            $details->permanent_zip_code      = $application->permanent_zip_code;
            $details->save();

            // ── 7. Mark the application as accepted ───────────────────────────
            $application->status = HOUSE_HUNT_APPLICATION_ACCEPTED;
            $application->save();

            // ── 8. Notifications ──────────────────────────────────────────────
            if (getOption('send_email_status', 0) == ACTIVE) {
                $mailService = new MailService();

                // (a) Welcome / sign-up email — only for brand-new accounts
                if ($autoPassword) {
                    $emails  = [$user->email];
                    $subject = getOption('app_name') . ' ' . __('welcome you');
                    $message = __('You have successfully been registered');

                    $template = EmailTemplate::where('owner_user_id', $ownerId)
                        ->where('category', EMAIL_TEMPLATE_SIGN_UP)
                        ->where('status', ACTIVE)
                        ->first();

                    if ($template) {
                        $fields  = [
                            '{{email}}'    => $user->email,
                            '{{password}}' => $autoPassword,
                            '{{app_name}}' => getOption('app_name'),
                        ];
                        $content = getEmailTemplate($template->body, $fields);
                        $mailService->sendCustomizeMail($emails, $template->subject, $content);
                    } else {
                        $mailService->sendSignUpMail($emails, $subject, $message, $ownerId, $autoPassword);
                    }
                }

                // (b) Tenancy-assigned notification email
                // Uses EMAIL_TEMPLATE_TENANT_ASSIGNED if the owner has configured
                // one; otherwise falls back to a generic notice.
                $assignTemplate = EmailTemplate::where('owner_user_id', $ownerId)
                    ->where('category', EMAIL_TEMPLATE_TENANT_ASSIGNED)
                    ->where('status', ACTIVE)
                    ->first();

                if ($assignTemplate) {
                    $fields  = [
                        '{{tenant_name}}'  => $user->first_name . ' ' . $user->last_name,
                        '{{property}}'     => $unit->property->name ?? '',
                        '{{unit}}'         => $unit->unit_name ?? $unit->id,
                        '{{rent}}'         => number_format($tenant->general_rent),
                        '{{start_date}}'   => $tenant->lease_start_date,
                        '{{app_name}}'     => getOption('app_name'),
                    ];
                    $content = getEmailTemplate($assignTemplate->body, $fields);
                    $mailService->sendCustomizeMail([$user->email], $assignTemplate->subject, $content);
                } else {
                    // Minimal fallback — avoids a hard dependency on the template
                    $mailService->sendMail(
                        [$user->email],
                        getOption('app_name') . ' — ' . __('Your Tenancy Has Been Confirmed'),
                        __('Congratulations! Your tenancy application has been approved. You have been assigned to') .
                            ' ' . ($unit->property->name ?? '') .
                            ', ' . ($unit->unit_name ?? 'Unit ' . $unit->id) . '.',
                        $ownerId
                    );
                }
            }

            DB::commit();

            return $this->success(
                ['tenant_id' => $tenant->id],
                __('Application approved. Tenant has been created and activated successfully.')
            );

        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], getErrorMessage($e, $e->getMessage()));
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DESTROY — reject + hard-delete in one action
    //
    // Fires a rejection notification first, then permanently removes the record.
    // Soft-delete is intentionally bypassed (forceDelete) because a rejected
    // application has no further business value and we don't want to retain PII
    // without purpose.
    // ──────────────────────────────────────────────────────────────────────────

    public function destroy(Request $request, int $id)
    {
        DB::beginTransaction();
        try {
            $ownerId = auth()->id();

            $application = HouseHuntApplication::with(['propertyUnit.property'])
                ->whereHas('propertyUnit.property', function ($q) use ($ownerId) {
                    $q->where('owner_user_id', $ownerId);
                })
                ->findOrFail($id);

            // ── Rejection notification (before delete so we still have the data)
            if (getOption('send_email_status', 0) == ACTIVE) {
                $mailService      = new MailService();
                $rejectTemplate   = EmailTemplate::where('owner_user_id', $ownerId)
                    ->where('category', EMAIL_TEMPLATE_APPLICATION_REJECTED)
                    ->where('status', ACTIVE)
                    ->first();

                if ($rejectTemplate) {
                    $fields  = [
                        '{{applicant_name}}' => $application->first_name . ' ' . $application->last_name,
                        '{{property}}'       => $application->propertyUnit?->property?->name ?? '',
                        '{{unit}}'           => $application->propertyUnit?->unit_name ?? '',
                        '{{app_name}}'       => getOption('app_name'),
                    ];
                    $content = getEmailTemplate($rejectTemplate->body, $fields);
                    $mailService->sendCustomizeMail([$application->email], $rejectTemplate->subject, $content);
                } else {
                    $mailService->sendMail(
                        [$application->email],
                        getOption('app_name') . ' — ' . __('Application Update'),
                        __('Thank you for your interest. Unfortunately your tenancy application has not been successful at this time.'),
                        $ownerId
                    );
                }
            }

            // ── Permanently remove the record ─────────────────────────────────
            $application->forceDelete();

            DB::commit();

            return $this->success([], __('Application rejected and removed successfully.'));

        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], getErrorMessage($e, $e->getMessage()));
        }
    }
}