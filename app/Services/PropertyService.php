<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PropertyService
{
    /**
     * Create a new property along with amenities.
     */
    public function createProperty(array $data, array $amenities = []): Property
    {
        return DB::transaction(function () use ($data, $amenities) {
            $property = Property::create($data);

            if (!empty($amenities)) {
                $property->amenities()->sync($amenities);
            }

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id() ?? $property->owner_id,
                'action' => 'property_created',
                'model_type' => Property::class,
                'model_id' => $property->id,
                'description' => "Property '{$property->property_name}' was created.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Clear cache
            Cache::forget('featured_properties');
            Cache::forget('admin_dashboard_stats');

            return $property;
        });
    }

    /**
     * Update an existing property.
     */
    public function updateProperty(Property $property, array $data, array $amenities = []): Property
    {
        return DB::transaction(function () use ($property, $data, $amenities) {
            $property->update($data);

            if (isset($amenities)) {
                $property->amenities()->sync($amenities);
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'property_updated',
                'model_type' => Property::class,
                'model_id' => $property->id,
                'description' => "Property '{$property->property_name}' was updated.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            Cache::forget('featured_properties');
            Cache::forget("property_details_{$property->id}");

            return $property;
        });
    }

    /**
     * Upload and attach images to a property, building, or room.
     */
    public function uploadImages(Property $property, array $uploadedFiles, ?int $roomId = null, ?int $buildingId = null): array
    {
        $savedImages = [];

        foreach ($uploadedFiles as $file) {
            // Validate the file is indeed an image and not executable
            if (!$file->isValid() || !in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                continue;
            }

            // Save to storage
            $path = $file->store('properties', 'public');

            // Find current sort order
            $nextSort = PropertyImage::where('property_id', $property->id)->max('sort_order') + 1;

            // Check if there is already a primary image, if not, set this as primary
            $hasPrimary = PropertyImage::where('property_id', $property->id)->where('is_primary', true)->exists();

            $image = PropertyImage::create([
                'property_id' => $property->id,
                'room_id' => $roomId,
                'building_id' => $buildingId,
                'file_path' => $path,
                'is_primary' => !$hasPrimary,
                'sort_order' => $nextSort,
            ]);

            $savedImages[] = $image;
        }

        Cache::forget("property_details_{$property->id}");

        return $savedImages;
    }

    /**
     * Set primary image for a property.
     */
    public function setPrimaryImage(Property $property, int $imageId): bool
    {
        return DB::transaction(function () use ($property, $imageId) {
            // Reset all
            PropertyImage::where('property_id', $property->id)->update(['is_primary' => false]);
            // Set target
            $updated = PropertyImage::where('id', $imageId)
                ->where('property_id', $property->id)
                ->update(['is_primary' => true]);

            Cache::forget("property_details_{$property->id}");
            return (bool) $updated;
        });
    }

    /**
     * Delete property image.
     */
    public function deleteImage(int $imageId): bool
    {
        $image = PropertyImage::find($imageId);
        if (!$image) {
            return false;
        }

        $propertyId = $image->property_id;

        // Delete from local disk
        Storage::disk('public')->delete($image->file_path);
        $image->delete();

        // If we deleted the primary, make the first remaining one primary
        $hasPrimary = PropertyImage::where('property_id', $propertyId)->where('is_primary', true)->exists();
        if (!$hasPrimary) {
            $nextPrimary = PropertyImage::where('property_id', $propertyId)->orderBy('sort_order')->first();
            if ($nextPrimary) {
                $nextPrimary->update(['is_primary' => true]);
            }
        }

        Cache::forget("property_details_{$propertyId}");
        return true;
    }

    /**
     * Update verification status.
     */
    public function changeVerificationStatus(Property $property, string $status, ?string $notes = null): bool
    {
        $updated = $property->update([
            'verification_status' => $status,
            'verification_notes' => $notes,
        ]);

        if ($updated) {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'property_approved',
                'model_type' => Property::class,
                'model_id' => $property->id,
                'description' => "Property '{$property->property_name}' verification status set to '{$status}'. Notes: {$notes}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            Cache::forget('admin_dashboard_stats');
            Cache::forget('featured_properties');
            Cache::forget("property_details_{$property->id}");
        }

        return $updated;
    }
}
