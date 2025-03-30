<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\DTO\Profile\SaveProfileDto;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Services\ImageUpload\UploadImageService;
use App\Services\Profile\SaveProfileService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
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
    
            // Save the user changes first
            $request->user()->save();
    
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
                return Inertia::render('settings/Profile', [
                    'error' => $saveProfileResponse['message'],
                    'user' => $request->user()->fresh()->load('profile'),
                    'status' => session('status'),
                ]);
            }
    
            return Inertia::render('pages/Profile', [
                'success' => 'Profile updated successfully',
                'user' => $request->user()->fresh()->load('profile'),
                'status' => session('status'),
            ]);
        } catch (\Exception $e) {
            return Inertia::render('pages/Profile', [
                'error' => $e->getMessage(),
                'user' => $request->user()->fresh()->load('profile'),
                'status' => session('status'),
            ]);
        }
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
