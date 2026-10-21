<?php

namespace App\Services;

use App\Models\MaintenanceRequest;
use App\Models\MaintenanceImage;
use App\Models\Contract;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\HostelSystemNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Exception;

class MaintenanceService
{
    /**
     * Create a maintenance ticket.
     */
    public function createTicket(User $tenant, Contract $activeLease, array $data, array $photos = []): MaintenanceRequest
    {
        return DB::transaction(function () use ($tenant, $activeLease, $data, $photos) {
            $ticketNumber = 'MNT-' . date('Ymd') . '-' . Str::upper(Str::random(4));

            $ticket = MaintenanceRequest::create([
                'ticket_number' => $ticketNumber,
                'tenant_id' => $tenant->id,
                'property_id' => $activeLease->property_id,
                'room_id' => $activeLease->room_id,
                'bed_id' => $activeLease->bed_id,
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => $data['priority'] ?? 'medium',
                'status' => 'submitted',
            ]);

            // Save photos
            foreach ($photos as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('maintenance', 'public');
                    MaintenanceImage::create([
                        'maintenance_request_id' => $ticket->id,
                        'file_path' => $path,
                    ]);
                }
            }

            // Log activity
            ActivityLog::create([
                'user_id' => $tenant->id,
                'action' => 'maintenance_submitted',
                'model_type' => MaintenanceRequest::class,
                'model_id' => $ticket->id,
                'description' => "Tenant submitted maintenance ticket {$ticketNumber}.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Notify Owner
            $owner = $activeLease->property->owner ?? null;
            if ($owner) {
                $owner->notify(new HostelSystemNotification(
                    'New Maintenance Request',
                    "New ticket {$ticketNumber} submitted for {$activeLease->property->property_name} by {$tenant->name}.",
                    route('owner.maintenance.show', $ticket->id)
                ));
            }

            // Notify Assigned Managers
            $managerIds = DB::table('property_user')
                ->where('property_id', $activeLease->property_id)
                ->pluck('user_id');
            foreach ($managerIds as $mid) {
                $manager = User::find($mid);
                if ($manager) {
                    $manager->notify(new HostelSystemNotification(
                        'New Maintenance Request',
                        "New ticket {$ticketNumber} submitted for {$activeLease->property->property_name} by {$tenant->name}.",
                        route('manager.maintenance.show', $ticket->id)
                    ));
                }
            }

            // Invalidate caches
            Cache::forget('superadmin_dashboard_stats');
            Cache::forget("owner_dashboard_stats_{$activeLease->property->owner_id}");
            foreach ($managerIds as $mid) {
                Cache::forget("manager_dashboard_stats_{$mid}");
            }

            return $ticket->load('images');
        });
    }

    /**
     * Assign maintenance ticket to a staff member.
     */
    public function assignTicket(MaintenanceRequest $ticket, int $assignedToUserId, ?User $assigner = null): MaintenanceRequest
    {
        return DB::transaction(function () use ($ticket, $assignedToUserId, $assigner) {
            $assignerId = $assigner ? $assigner->id : auth()->id();

            $ticket->update([
                'assigned_to' => $assignedToUserId,
                'status' => 'assigned',
            ]);

            $staff = User::find($assignedToUserId);
            if ($staff) {
                $staff->notify(new HostelSystemNotification(
                    'Maintenance Ticket Assigned',
                    "You have been assigned to maintenance ticket {$ticket->ticket_number} for {$ticket->property->property_name}.",
                    route($staff->isOwner() ? 'owner.maintenance.show' : 'manager.maintenance.show', $ticket->id)
                ));
            }

            // Notify Tenant
            $tenant = $ticket->tenant;
            if ($tenant && $staff) {
                $tenant->notify(new HostelSystemNotification(
                    'Maintenance Ticket Assigned',
                    "Your maintenance request {$ticket->ticket_number} has been assigned to {$staff->name}.",
                    route('tenant.maintenance')
                ));
            }

            // Invalidate caches
            Cache::forget('superadmin_dashboard_stats');
            Cache::forget("owner_dashboard_stats_{$ticket->property->owner_id}");
            $managerIds = DB::table('property_user')
                ->where('property_id', $ticket->property_id)
                ->pluck('user_id');
            foreach ($managerIds as $mid) {
                Cache::forget("manager_dashboard_stats_{$mid}");
            }

            return $ticket->fresh(['assignedTo', 'images']);
        });
    }

    /**
     * Resolve a maintenance ticket.
     */
    public function resolveTicket(MaintenanceRequest $ticket, string $resolutionNotes, ?User $resolver = null): MaintenanceRequest
    {
        return DB::transaction(function () use ($ticket, $resolutionNotes, $resolver) {
            $resolverId = $resolver ? $resolver->id : auth()->id();

            $ticket->update([
                'status' => 'resolved',
                'resolution_notes' => $resolutionNotes,
                'resolved_at' => now(),
            ]);

            // Notify Tenant
            $tenant = $ticket->tenant;
            if ($tenant) {
                $tenant->notify(new HostelSystemNotification(
                    'Maintenance Request Resolved',
                    "Your maintenance request {$ticket->ticket_number} has been resolved. Note: {$resolutionNotes}",
                    route('tenant.maintenance')
                ));
            }

            // Invalidate caches
            Cache::forget('superadmin_dashboard_stats');
            Cache::forget("owner_dashboard_stats_{$ticket->property->owner_id}");
            $managerIds = DB::table('property_user')
                ->where('property_id', $ticket->property_id)
                ->pluck('user_id');
            foreach ($managerIds as $mid) {
                Cache::forget("manager_dashboard_stats_{$mid}");
            }

            return $ticket->fresh(['assignedTo', 'images']);
        });
    }
}
