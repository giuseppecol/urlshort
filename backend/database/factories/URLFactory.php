<?php

namespace Database\Factories;

use App\Models\URL;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class URLFactory extends Factory
{
    protected $model = URL::class;

    public function definition()
    {
        return [
            'original_url' => $this->faker->url(),
            'short_code' => Str::random(8),  // Generate a random string for the short_code
            'user_id' => \App\Models\User::factory(),  // Create a new User factory or link to an existing user
        ];
    }
}
