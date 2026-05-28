<?php

namespace Database\Factories;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TempahanFactory extends Factory
{
    protected $model = Tempahan::class;

    public function definition(): array
    {
        $sesi = $this->faker->randomElement(['pagi', 'petang']);
        $masaSesi = Tempahan::MASA_SESI[$sesi];

        return [
            'nama_mesyuarat' => $this->faker->sentence(3),
            'tarikh' => $this->faker->dateTimeBetween('today', '+30 days')->format('Y-m-d'),
            'bilik_id' => BilikMesyuarat::factory(),
            'user_id' => User::factory(),
            'sesi' => $sesi,
            'masa_mula' => $masaSesi['mula'],
            'masa_tamat' => $masaSesi['tamat'],
            'bilangan_peserta' => $this->faker->numberBetween(5, 20),
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => $this->faker->name(),
            'tujuan' => $this->faker->sentence(),
            'status' => Tempahan::STATUS_DILULUSKAN,
        ];
    }

    public function pagi(): static
    {
        return $this->state([
            'sesi' => 'pagi',
            'masa_mula' => '09:00',
            'masa_tamat' => '13:00',
        ]);
    }

    public function petang(): static
    {
        return $this->state([
            'sesi' => 'petang',
            'masa_mula' => '14:00',
            'masa_tamat' => '18:00',
        ]);
    }

    public function ditolak(): static
    {
        return $this->state(['status' => Tempahan::STATUS_DITOLAK]);
    }
}
