<?php

namespace Database\Factories;

use App\Enums\ServiceInterest;
use App\Models\ContactSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactSubmission>
 */
class ContactSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'company' => fake()->company(),
            'phone' => fake()->phoneNumber(),
            'service_interest' => fake()->randomElement(ServiceInterest::cases()),
            'message' => fake()->paragraph(),
        ];
    }

    /**
     * Indicate that the enquiry omitted the optional details.
     */
    public function withoutOptionalDetails(): static
    {
        return $this->state(fn (array $attributes): array => [
            'company' => null,
            'phone' => null,
        ]);
    }
}
