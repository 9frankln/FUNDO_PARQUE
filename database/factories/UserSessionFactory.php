<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSessionFactory extends Factory
{
    protected $model = UserSession::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'session_id' => $this->faker->uuid(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'last_activity' => now(),
        ];
    }
}
