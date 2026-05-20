<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'     => $this->faker->name(),
            'email'    => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'jabatan'  => $this->faker->company(),
            'peranan'  => User::PERANAN_STAF,
            'aktif'    => true,
        ];
    }

    public function pentadbir(): static
    {
        return $this->state(['peranan' => User::PERANAN_PENTADBIR]);
    }

    public function urusSetia(): static
    {
        return $this->state(['peranan' => User::PERANAN_URUS_SETIA]);
    }

    public function staf(): static
    {
        return $this->state(['peranan' => User::PERANAN_STAF]);
    }

    public function nyahaktif(): static
    {
        return $this->state(['aktif' => false]);
    }
}
