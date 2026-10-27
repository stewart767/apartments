<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Notifications\HostelSystemNotification;

class SendRentReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reliance:send-rent-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send automated notifications for upcoming and overdue rent payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking rent due dates and sending reminders...');

        // 1. Remind tenants with rent due in 3 days
        $targetDate = now()->addDays(3)->toDateString();
        $upcomingRent = Payment::with(['tenant', 'property'])
            ->where('payment_type', 'rent')
            ->whereIn('status', ['pending'])
            ->whereDate('due_date', $targetDate)
            ->get();

        $upcomingCount = 0;
        foreach ($upcomingRent as $payment) {
            $tenant = $payment->tenant;
            if ($tenant) {
                $tenant->notify(new HostelSystemNotification(
                    'Upcoming Rent Due Reminder',
                    "Your rent of TSh " . number_format($payment->amount) . " for {$payment->property->property_name} is due in 3 days ({$payment->due_date}).",
                    route('tenant.payments')
                ));
                $upcomingCount++;
            }
        }

        // 2. Alert for overdue rent (due before today and still pending)
        $overdueRent = Payment::with(['tenant', 'property.owner'])
            ->where('payment_type', 'rent')
            ->where('status', 'pending')
            ->whereDate('due_date', '<', now()->toDateString())
            ->get();

        $overdueCount = 0;
        foreach ($overdueRent as $payment) {
            $payment->update(['status' => 'overdue']);
            $overdueCount++;

            $tenant = $payment->tenant;
            if ($tenant) {
                $tenant->notify(new HostelSystemNotification(
                    'Rent Payment Overdue',
                    "Your rent of TSh " . number_format($payment->amount) . " for {$payment->property->property_name} is overdue. Please settle or upload receipt proof.",
                    route('tenant.payments')
                ));
            }

            $owner = $payment->property->owner ?? null;
            if ($owner) {
                $owner->notify(new HostelSystemNotification(
                    'Tenant Rent Overdue',
                    "Tenant {$payment->tenant->name} has an overdue rent payment of TSh " . number_format($payment->amount) . " for {$payment->property->property_name}.",
                    route('owner.payments')
                ));
            }
        }

        $this->info("Sent {$upcomingCount} upcoming reminders and flagged {$overdueCount} overdue payments.");
        return 0;
    }
}
