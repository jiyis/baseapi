<?php
/**
 * Created by PhpStorm.
 * User: Gary.F.Dong
 * Date: 2017/4/17
 * Time: 18:15
 * Desc:
 */

namespace App\Services;


use App\Repositories\UserRepository;
use App\Services\Contracts\ServiceInterface;

class UserService extends BaseService implements ServiceInterface
{

    public function __construct(UserRepository $userRepository)
    {
        $this->repository = $userRepository;
    }

}