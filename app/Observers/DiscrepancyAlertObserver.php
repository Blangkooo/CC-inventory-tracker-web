<?php

namespace App\Observers;

use App\Mail\DiscrepancyAlertMail;
use App\Models\DiscrepancyAlert;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DiscrepancyAlertObserver
{
    /**
     * Notify the branch's manager(s) and all super admins — in-app + email —
     * whenever a new discrepancy alert is raised.
     *
     * NOTE: Email delivery writes to storage/logs/laravel.log by default
     * (MAIL_MAILER=log in .env). To actually send emails, set MAIL_MAILER=smtp
     * with valid SMTP credentials in your .env file. In-app notifications
     * (Notification model) are always created regardless of mail config.
     */
    public function created(DiscrepancyAlert $discrepancyAlert): void
    {
        $recipients = User::where(function ($query) use ($discrepancyAlert) {
            $query->where('role', User::ROLE_MANAGER)
                ->where('branch_id', $discrepancyAlert->branch_id);
        })->orWhere('role', User::ROLE_SUPER_ADMIN)->get();

        $mailDriver = config('mail.default');

        foreach ($recipients as $user) {
            // Always create an in-app notification
            Notification::create([
                'user_id' => $user->id,
                'discrepancy_alert_id' => $discrepancyAlert->id,
                'title' => 'Discrepancy alert: '.$discrepancyAlert->branch->name,
                'message' => $discrepancyAlert->details,
            ]);

            // Email is best-effort — skip if mail driver is 'log' or user has no email
            if (! $user->email || $mailDriver === 'log') {
                continue;
            }

            try {
                Mail::to($user->email)->send(new DiscrepancyAlertMail($discrepancyAlert));
            } catch (Throwable $e) {
                // A down SMTP server must never block the checkout/shift-close flow
                Log::warning('Failed to send discrepancy alert email.', [
                    'user_id' => $user->id,
                    'alert_id' => $discrepancyAlert->id,
                    'mail_driver' => $mailDriver,
                    'error' => $e->getMessage(),
                ]);
            } finally {
                Mail::purge($mailDriver);
            }
        }
    }

    /**
     * Handle the DiscrepancyAlert "updated" event.
     */
    public function updated(DiscrepancyAlert $discrepancyAlert): void
    {
        //
    }

    /**
     * Handle the DiscrepancyAlert "deleted" event.
     */
    public function deleted(DiscrepancyAlert $discrepancyAlert): void
    {
        //
    }

    /**
     * Handle the DiscrepancyAlert "restored" event.
     */
    public function restored(DiscrepancyAlert $discrepancyAlert): void
    {
        //
    }

    /**
     * Handle the DiscrepancyAlert "force deleted" event.
     */
    public function forceDeleted(DiscrepancyAlert $discrepancyAlert): void
    {
        //
    }
}
