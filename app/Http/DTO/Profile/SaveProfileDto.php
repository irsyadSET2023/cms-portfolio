<?php

namespace App\Http\DTO\Profile;

class SaveProfileDto
{
    public string $fullname;
    public string $email;
    public string $image_url;
    public string $description;
    public string $dob;
    public function __construct(string $name, string $email, string $image_url, string $description, string $dob)
    {
        $this->fullname = $name;
        $this->email = $email;
        $this->image_url = $image_url;
        $this->description = $description;
        $this->dob = $dob;
    }
}