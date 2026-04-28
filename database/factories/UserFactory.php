<?php

/**
 * UserFactory - Fábrica de Usuarios para Testing
 * 
 * Genera datos de prueba para el modelo User con roles específicos
 * del sistema de gestión de cisternas.
 */

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Contraseña por defecto para todos los usuarios generados.
     */
    protected static ?string $password;

    /**
     * Define el estado por defecto del modelo User.
     *
     * @return array<string, mixed> Datos por defecto para un usuario
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            
            // Campos personalizados del sistema
            'role' => fake()->randomElement(['Usuario', 'operario']),
            'is_active' => true,
            'fecha_registro' => now(),
        ];
    }

    /**
     * Indica que el email del usuario no está verificado.
     *
     * @return static Estado con email no verificado
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Crea un usuario con rol de Administrador.
     *
     * @return static Estado con rol de administrador
     */
    public function administrador(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'Administrador',
            'email' => 'admin' . fake()->unique()->randomNumber(3) . '@cisternas.local',
        ]);
    }

    /**
     * Crea un usuario con rol de Root.
     *
     * @return static Estado con rol de root
     */
    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'Root',
            'email' => 'root' . fake()->unique()->randomNumber(3) . '@cisternas.local',
        ]);
    }

    /**
     * Crea un usuario con rol de Operario.
     *
     * @return static Estado con rol de operario
     */
    public function operario(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'operario',
            'email' => 'operario' . fake()->unique()->randomNumber(3) . '@cisternas.local',
        ]);
    }

    /**
     * Crea un usuario inactivo.
     *
     * @return static Estado con usuario desactivado
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
