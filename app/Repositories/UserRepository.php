<?php

namespace App\Repositories;

use App\Criteria\UserCriteria;
use App\Repositories\Contracts\RepositoryInterface;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\User;
use App\Validators\UserValidator;

/**
 * Class UserRepositoryEloquent
 * @package namespace App\Repositories;
 */
class UserRepository extends BaseRepository implements RepositoryInterface
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return User::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        parent::boot();
        $this->pushCriteria(app(UserCriteria::class));
    }
}
