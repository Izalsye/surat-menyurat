<?php

namespace Database\Factories;

use App\Helpers\DateHelper;
use App\Models\Disposition;
use App\Models\OutgoingLetter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * @extends Factory<OutgoingLetter>
 */
class OutgoingLetterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = $this->faker->dateTimeBetween('-6 month');
        $letterDate = $this->faker->dateTimeBetween('-9 month', $createdAt);

        $agenda = 'K-' . str_pad($this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT);
        $agenda .= '/' . DateHelper::getRomanMonth(Carbon::parse($createdAt));
        $agenda .= '/' . $createdAt->format('Y');
        $file = 'https://placehold.jp/480x640.png';

        return [
            'letter_number' => $this->faker->numberBetween(1000000000, 9999999999),
            'letter_date' => $letterDate,
            'recipient' => $this->faker->name(),
            'subject' => $this->faker->sentence(),
            'body' => $this->faker->paragraph(),
            'is_draft' => false,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'agenda_number' => $agenda,
            'file' => $file,
        ];
    }

    public function forDisposition(Disposition $disposition): self
    {
        $createdAt = $this->faker->dateTimeBetween($disposition->created_at);
        $letterDate = $createdAt;

        $agenda = 'K-' . str_pad($this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT);
        $agenda .= '/' . DateHelper::getRomanMonth(Carbon::parse($createdAt));
        $agenda .= '/' . $createdAt->format('Y');

        return $this->state([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'letter_date' => $letterDate,
            'agenda_number' => $agenda,
            'incoming_letter_id' => $disposition->incoming_letter_id,
            'disposition_id' => $disposition->id,
        ]);
    }
}
