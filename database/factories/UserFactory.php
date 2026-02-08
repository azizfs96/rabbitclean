<?php

namespace Database\Factories;

<<<<<<< HEAD
use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;
=======
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;
>>>>>>> 7d2250222b1076404c7124acb2f73be59dd3ce1a

    /**
     * Define the model's default state.
     *
<<<<<<< HEAD
     * @return array
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail(),
            'mobile' => $this->faker->unique()->e164PhoneNumber,
            'email_verified_at' => now(),
            'mobile_verified_at' => now(),
            'password' => Hash::make('secret'), // secret
            'remember_token' => Str::random(10),
            'is_active' => $this->faker->boolean(),
            'profile_photo_id' => Media::factory()->create(),
=======
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
>>>>>>> 7d2250222b1076404c7124acb2f73be59dd3ce1a
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
<<<<<<< HEAD
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
                'mobile_verified_at' => null,
            ];
        });
=======
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
>>>>>>> 7d2250222b1076404c7124acb2f73be59dd3ce1a
    }
}
