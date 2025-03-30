<?php

namespace App\Services\Profile;

use App\Http\DTO\Profile\SaveProfileDto;
use App\Models\Profile;

class SaveProfileService
{
    public function __construct() {}

    public function saveProfile(string $userId, SaveProfileDto $profileDto)
    {
        try {
         Profile::updateOrCreate([
                'user_id' => $userId,
            ],[
                'fullname' => $profileDto->fullname,
                'email' => $profileDto->email,
                'image_url' => $profileDto->image_url,
                'description' => $profileDto->description,
                'dob' => $profileDto->dob,
            ]);

            return [
                'success' => true,
                'message' => 'Profile saved successfully',
            ];
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => $th->getMessage(),
            ];
        }
        }


     
    }

