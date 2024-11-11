<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->orderBy('id', 'DESC')->paginate(12);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \App\Models\User|null
     */
    public function getAuthenticatedUser(): ?User
    {
        return Auth::user();
    }

    public function updateAccountDetails(int $user_id, array $data): bool
    {
        $user = $this->model->find($user_id);

        if (!$user) {
            return false;
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'];

        // If the password fields are filled, validate and update the password
        if (!empty($data['new_password'])) {
            if (!Hash::check($data['old_password'], $user->password)) {
                return false;
            }
            $user->password = Hash::make($data['new_password']);
        }

        return $user->save();
    }
}
