<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;

class EloquentUserRepository implements UserRepositoryInterface
{
    private User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findById(string $id): ?User
    {
        return $this->model->find($id);
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }

        return $user->update($data) > 0;
    }

    public function delete(string $id): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }

        return $user->delete() > 0;
    }

    public function findByIdWithRelations(string $id, array $relationships = []): ?User
    {
        $query = $this->model;

        if (!empty($relationships)) {
            $query = $query->with($relationships);
        }

        return $query->find($id);
    }

    public function deleteByEmail(string $email): bool
    {
        $user = $this->findByEmail($email);
        if (!$user) {
            return false;
        }

        return $user->delete() > 0;
    }

    public function deleteById(string $id): bool
    {
        return $this->delete($id);
    }
}
