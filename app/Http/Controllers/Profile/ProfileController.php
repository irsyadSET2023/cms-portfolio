<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\DTO\Profile\SaveProfileDto;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Services\ImageUpload\UploadImageService;
use App\Services\Profile\SaveProfileService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user()->fresh()->load('profile');
        return Inertia::render('Profile', [

            'status' => $request->session()->get('status'),
            'profile' => $user->profile,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): Response
    {
        try {
            $uploadImageService = new UploadImageService;
            $request->user()->fill($request->validated());
            $imagePath = null;
            $image = $request->image;
            if ($image) {
                $imageMetadata = $uploadImageService->uploadImage($image, 's3', 'profile_pictures', $image->getClientOriginalName(), true);
                if (!isset($imageMetadata['path'])) {
                    throw new \Exception('Failed to upload image');
                }
                $imagePath = $imageMetadata['path'];
            }
            if ($request->user()->isDirty('email')) {
                $request->user()->email_verified_at = null;
            }




            $saveProfileService = new SaveProfileService;
            $profileDto = new SaveProfileDto(
                $request->name,
                $request->email,
                $imagePath ?? $request->user()->profile?->image_url ?? '', // Keep existing image if no new upload
                $request->description ?? '',
                $request->dob ?? ''
            );
    
            $saveProfileResponse = $saveProfileService->saveProfile($request->user()->id, $profileDto);


            if (!$saveProfileResponse['success']) {
                return Inertia::render('Profile', [
                    'error' => $saveProfileResponse['message'],
                    'profile' => $request->user()->fresh()->load('profile')->profile,
                    'status' => session('status'),
                ]);
            }


    
            return Inertia::render('Profile', [
                'success' => 'Profile updated successfully',
                'profile' => $request->user()->fresh()->load('profile')->profile,                
                'status' => session('status'),
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Profile', [
                'error' => $e->getMessage(),
                'profile' => $request->user()->fresh()->load('profile')->profile,
                'status' => session('status'),
            ]);
        }
    }

}
