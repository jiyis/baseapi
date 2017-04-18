<?php
/**
 * Created by PhpStorm.
 * User: Gary.F.Dong
 * Date: 2017/4/17
 * Time: 18:20
 * Desc:
 */

namespace App\Http\Controllers\Api\V1;


use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use App\Transformers\UserTransformer;

class UserController extends BaseController
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $users = $this->userService->all();
        return $this->response->paginator($users, new UserTransformer());
    }

    public function show($id)
    {
        $user = $this->userService->show($id);
       return $this->response->item($user, new UserTransformer());
    }

    public function store(CreateUserRequest $request)
    {
        try {
            $user = $this->userService->store($request->all());
            return $this->response->item($user, new UserTransformer());
        } catch (ValidatorException $e) {
            throw new UpdateResourceFailedException('无法新增用户信息：'. $e->getMessage());
        }

    }

    public function update(UpdateUserRequest $request, $id)
    {
        try {
            $user = $this->userService->update($request->all(), $id);
            return $this->response->item($user, new UserTransformer());
        } catch (ValidatorException $e) {
            throw new UpdateResourceFailedException('无法更新用户信息：'. $e->getMessage());
        }

    }

    public function destroy($id)
    {
        $result = $this->userService->destroy($id);
        return $this->response->array(compact('result'));
    }

    public function count()
    {
        $count = $this->userService->count();
        return $this->response->array(compact('count'));
    }

}