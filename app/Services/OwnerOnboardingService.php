<?php

namespace App\Services;

use App\Models\Owner;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * The single source of truth for creating an owner ACCOUNT with the modern onboarding lifecycle:
 * a system-generated temporary password, forced reset on first login (must_change_password), an
 * active + verified user, a trial package, and the plug-and-play owner defaults. Used by every
 * admin-driven owner creation — the manual "Add Owner" form, a tenant referral, and a trial
 * contact message — so they never drift apart.
 *
 * Deliberately does NOT open a transaction, deliver credentials, or link back to whatever record
 * sourced it — the caller owns the transaction boundary, dispatches SendLoginDetailsJob, and does
 * its own linking (lead / referral / message). Returns the user, owner, and the plaintext password
 * (held only in-memory, for credential delivery + the dev panel).
 *
 * @return array{user: User, owner: Owner, password: string}
 */
class OwnerOnboardingService
{
    public function create(array $attrs): array
    {
        $user = new User();
        $user->first_name = ($attrs['first_name'] ?? '') !== '' ? $attrs['first_name'] : 'Owner';
        $user->last_name = $attrs['last_name'] ?? '';
        $user->contact_number = $attrs['phone'] ?? null;
        $user->email = $attrs['email'];
        $plainPassword = Str::random(10);
        $user->password = Hash::make($plainPassword);
        $user->must_change_password = 1;
        $user->status = USER_STATUS_ACTIVE;
        $user->email_verified_at = Carbon::now()->format('Y-m-d H:i:s');
        $user->role = USER_ROLE_OWNER;
        $user->verify_token = str_replace('-', '', Str::uuid()->toString());
        $user->save();

        $owner = new Owner();
        $owner->user_id = $user->id;
        $owner->affiliate_id = $attrs['affiliate_id'] ?? null;
        $owner->save();

        $defaultPackage = Package::where(['is_trail' => ACTIVE])->first();
        if ($defaultPackage) {
            setUserPackage($user->id, $defaultPackage, (int) getOption('trail_duration', 1), 1);
        }

        setOwnerGateway($user->id);
        setOwnerInvoiceType($user->id);
        setOwnerDefaultMaintenanceIssue($user->id);
        setOwnerDefaultTicketTopics($user->id);
        setOwnerDefaultDocumentConfig($user->id);

        return ['user' => $user, 'owner' => $owner, 'password' => $plainPassword];
    }

    /** The dev-only credentials payload (flashed as 'dev_credentials' for the copyable panel). */
    public function devCredentials(User $user, string $password): array
    {
        return [
            'name'     => trim($user->first_name . ' ' . $user->last_name),
            'email'    => $user->email,
            'phone'    => $user->contact_number,
            'password' => $password,
        ];
    }
}
