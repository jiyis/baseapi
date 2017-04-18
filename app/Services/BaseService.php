<?php
/**
 * Created by PhpStorm.
 * User: Gary.F.Dong
 * Date: 2017/4/18
 * Time: 9:46
 * Desc:
 */

namespace App\Services;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class BaseService
{
    /**
     * 最大重插次数
     *
     * @var int $maxRedoTimes
     */
    protected static $maxRedoTimes = 1;

    /**
     * 已经加载的服务
     *
     * @var array $maxRedoTimes
     */
    protected static $loadedServices = [];

    /**
     * 已经加载的 Repository
     *
     * @var array $loadedRepositories
     */
    protected static $loadedRepositories = [];

    /**
     * 当前的repository
     * @var
     */
    protected $repository;

    /**
     * 取得一个service实例
     * @param $service
     * @return \Illuminate\Foundation\Application|mixed
     */
    protected function getService($service)
    {
        if (isset(self::$loadedServices[$service])) {
            return self::$loadedServices[$service];
        }
        $serviceInstance = app($service);
        self::$loadedServices[] = $serviceInstance;
        return $serviceInstance;
    }

    /**
     * 取得一个repository实例
     * @param $repository
     * @return \Illuminate\Foundation\Application|mixed
     */
    protected function getRepository($repository)
    {
        if (isset(self::$loadedRepositories[$repository])) {
            return self::$loadedRepositories[$repository];
        }
        $repositoryInstance = app($repository);
        self::$loadedRepositories[] = $repositoryInstance;
        return $repositoryInstance;
    }

    /**
     * 获取当前资料的列表，默认取配置的分页数
     * @return mixed
     */
    public function all()
    {
        return $this->repository->paginate();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $item = $this->repository->findWithoutFail($id);
        if(empty($item)) {
            throw new NotFoundHttpException();
        }
        return $item;
    }

    /**
     * @param $attribute
     * @param int $redo
     * @return mixed
     * @throws \Exception
     */
    public function store($attribute, $redo = 0)
    {
        try {
            return $this->repository->create($attribute);
        } catch (\Exception $e) {
            if ($redo < self::$maxRedoTimes && $e instanceof QueryException && $e->getCode() == '23000') {
                return $this->store($attribute, ++$redo);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param $attribute
     * @param $id
     * @return mixed
     */
    public function update($attribute, $id)
    {
        return $this->repository->update($attribute, $id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        if (empty($this->repository->findWithoutFail($id))) {
            throw new NotFoundHttpException();
        }
        return $this->repository->delete($id);
    }

    /**
     * @param array|null $where
     */
    public function count(array $where = null)
    {
        return $this->repository->count($where);
    }
}