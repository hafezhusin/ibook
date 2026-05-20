<?php

namespace Database\Factories;

use App\Models\BilikMesyuarat;
use Illuminate\Database\Eloquent\Factories\Factory;

class BilikMesyuaratFactory extends Factory
{
    protected $model = BilikMesyuarat::class;

    public function definition(): array
    {
        return [
            'nama'     => 'Bilik Mesyuarat ' . $this->faker->unique()->word(),
            'kapasiti' => $this->faker->numberBetween(10, 50),
            'lokasi'   => 'Tingkat ' . $this->faker->numberBetween(1, 10),
            'status'   => 'aktif',
            'kemudahan' => ['Papan Putih', 'Sistem Audio'],
            'gambar'   => null,
        ];
    }

    public function nyahaktif(): static
    {
        return $this->state(['status' => 'tidak_aktif']);
    }

    public function kapasiti(int $kapasiti): static
    {
        return $this->state(['kapasiti' => $kapasiti]);
    }
}
