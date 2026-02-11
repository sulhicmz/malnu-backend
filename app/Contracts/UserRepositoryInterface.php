<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;

/**
 * Repository interface for User data access operations.
 *
 * This interface provides an abstraction layer for User data operations,
 * enabling dependency injection, mocking in tests, and potential
 * data source switching (Eloquent, MongoDB, Redis, etc.).
 */
interface UserRepositoryInterface
{
    /**
     * Find a user by their email address.
     *
     * @param string $email The user's email address
     * @return User|null The user model or null if not found
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find a user by their ID.
     *
     * @param string $id The user's ID (UUID)
     * @return User|null The user model or null if not found
     */
    public function findById(string $id): ?User;

    /**
     * Create a new user with the provided data.
     *
     * @param array $data User data (name, email, password, etc.)
     * @return User The created user model
     */
    public function create(array $data): User;

    /**
     * Update an existing user.
     *
     * @param string $id The user's ID
     * @param array $data The data to update
     * @return bool True if update was successful, false otherwise
     */
    public function update(string $id, array $data): bool;

    /**
     * Delete a user by their ID.
     *
     * @param string $id The user's ID
     * @return bool True if deletion was successful, false otherwise
     */
    public function delete(string $id): bool;

    /**
     * Find a user by their ID with specified relationships loaded.
     *
     * @param string $id The user's ID
     * @param array $relationships Relationships to eager load (e.g., ['roles', 'student'])
     * @return User|null The user model or null if not found
     */
    public function findByIdWithRelations(string $id, array $relationships = []): ?User;

    public function deleteByEmail(string $email): bool;

    public function deleteById(string $id): bool;
}
