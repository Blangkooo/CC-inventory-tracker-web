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
     */
    public function created(DiscrepancyAlert $discrepancyAlert): void
    {
        $recipients = User::where(function ($query) use ($discrepancyAlert) {
            $query->where('role', User::ROLE_MANAGER)
                ->where('branch_id', $discrepancyAlert->branch_id);
        })->orWhere('role', User::ROLE_SUPER_ADMIN)->get();

        foreach ($recipients as $user) {
            Notification::create([
                'user_id' => $user->id,
                'discrepancy_alert_id' => $discrepancyAlert->id,
                'title' => 'Discrepancy alert: '.$discrepancyAlert->branch->name,
                'message' => $discrepancyAlert->details,
            ]);

            if (! $user->email) {
                continue;
            }

            try {
                Mail::to($user->email)->send(new DiscrepancyAlertMail($discrepancyAlert));
            } catch (Throwable $e) {
                // Email delivery is best-effort — a down SMTP server must never
                // block the checkout/shift-close flow that triggered this alert.
                Log::error('Failed to send discrepancy alert email.', [
                    'user_id' => $user->id,
                    'alert_id' => $discrepancyAlert->id,
                    'error' => $e->getMessage(),
                ]);
            } finally {
                // Drop the cached mailer so each recipient gets a fresh SMTP
                // connection rather than one reused across the whole request.
                Mail::purge(config('mail.default'));
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
