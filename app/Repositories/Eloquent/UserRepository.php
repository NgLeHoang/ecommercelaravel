<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    /**
     * The model instance that this repository will interact with.
     *
     * @var \App\Models\User
     */
    protected $model;

    /**
     * UserRepository constructor.
     *
     * @param \App\Models\User $model
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Get all user items, paginated.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll()
    {
        return $this->model->orderBy('id', 'DESC')->paginate(12);
    }

    /**
     * Find a record item by its ID.
     *
     * @param int $id
     * @return \App\Models\Order|null
     */
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

    /**
     * Update the account details of a user.
     *
     * @param int $user_id 
     * @param array $data 
     * @return bool 
     */
    public function updateAccountDetails(int $user_id, array $data): bool
    {
        $user = $this->model->find($user_id);

        if (!$user) {
            return false;
        }

        // Update the user's name, email, and phone
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
