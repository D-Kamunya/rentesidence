<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutreachCampaign extends Model
{
    protected $fillable = [
        'name', 'type', 'subject', 'body', 'status',
        'follow_up_days', 'max_follow_ups', 'send_per_hour', 'created_by', 'sent_at',
    ];

    /** Default paced-send rate (emails/hour) — conservative vs the typical cPanel 200/hr cap. */
    public const DEFAULT_SEND_PER_HOUR = 100;

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public const TYPE_RECRUITMENT = 'recruitment';
    public const TYPE_GENERAL     = 'general';

    public function recipients(): HasMany
    {
        return $this->hasMany(OutreachRecipient::class, 'campaign_id');
    }

    public function isRecruitment(): bool
    {
        return $this->type === self::TYPE_RECRUITMENT;
    }

    /** Prefill for a new recruitment campaign — the approved opt-in copy (plain text + placeholders). */
    public static function defaultRecruitmentSubject(): string
    {
        return __('You\'ve been selected to partner with :app', ['app' => getOption('app_name')]);
    }

    public static function defaultRecruitmentBody(): string
    {
        $app = getOption('app_name') ?: 'Centresidence';

        return "Hi {{first_name}},\n\n"
            . "Thanks for applying. We've reviewed the applications, and we'd love to have you on board as a {$app} Partner.\n\n"
            . "{$app} is a fast-growing property-management platform. As a partner, you introduce landlords and property owners to the system and earn recurring commission on what they do on it — all remotely, on your own schedule.\n\n"
            . "Before you say yes, here's the honest picture:\n\n"
            . "– **Commission-only.** No salary or retainer — you earn when the owners you bring on use {$app}, and that commission recurs for a set period on each owner.\n"
            . "– **Genuinely flexible.** Fully remote, your own hours, and no sales targets or quota pressure — no one chasing you. Great as a side hustle — no need to quit your job — and because it recurs, it can grow into something you do full-time.\n"
            . "– **Easy to say yes to.** {$app} is free for owners to start, so you offer something useful from day one. You earn as they get active and pay for what they genuinely need.\n"
            . "– **You're set up to win.** A partner academy that certifies you, ready-made marketing materials, support, and a dashboard that pays straight to M-Pesa.\n\n"
            . "As {$app} expands across East Africa, your reach — and your earning potential — grows with it.\n\n"
            . "Because you've already been selected, your spot is reserved. Use the button below and your partner account is created right away — your login details arrive by email and SMS.\n\n"
            . "Your invitation is open for a limited time, so it's best to set up your account soon.\n\n"
            . "Welcome aboard,\nThe {$app} Team";
    }
}
