<?php
/**
 * Created by PhpStorm.
 * User: Gary.F.Dong
 * Date: 2017/4/18
 * Time: 10:15
 * Desc:
 */

namespace App\Repositories;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Container\Container as Application;
use Prettus\Repository\Eloquent\BaseRepository as OriginBaseRepository;
use Prettus\Repository\Events\RepositoryEntityDeleted;
use Prettus\Repository\Contracts\CacheableInterface;
use App\Criteria\BaseCriteria;
use Illuminate\Support\Collection;
use Prettus\Repository\Events\RepositoryEntityUpdated;
use Prettus\Repository\Events\RepositoryEntityCreated;

abstract class BaseRepository extends OriginBaseRepository
{
    protected $searchable = false;

    protected $searchBlacklist = [];

    // 反注释后启用 API 结果缓存
    //use CacheableRepository;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->criteria = new Collection();
        $this->makeModel();
        $this->makeValidator();
        $this->boot();
    }

    public function boot()
    {
        parent::boot();
        $this->pushDefaultCriterias();
    }

    protected function pushDefaultCriterias()
    {
        $this->pushCriteria(new BaseCriteria);
        $this->searchable() &&
        $this->pushCriteria(app('App\Criteria\SearchCriteria'));
    }

    protected function searchable()
    {
        return $this->searchable;
    }

    public function getSearchBlacklist()
    {
        return $this->searchBlacklist;
    }

    public function beginTransaction()
    {
        DB::beginTransaction();
    }

    public function rollback()
    {
        DB::rollBack();
    }

    public function commit()
    {
        DB::commit();
    }

    public function transaction(Closure $transaction)
    {
        return DB::transaction($transaction);
    }

    // 以下的方法覆写 OriginBaseRepository 中的方法

    public function all($columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        if ($this->model instanceof Builder) {
            $query = $this->model->getQuery();
            if (
                property_exists($query, 'limit') && isset($query->limit) &&
                property_exists($query, 'offset') && isset($query->offset)
            ) {
                $page = $query->offset / $query->limit + 1;
                $results = $this->model->paginate($query->limit, ['*'], 'rows', $page);
            } else {
                $results = $this->model->get($columns);
            }
        } else {
            $results = $this->model->all($columns);
        }

        $this->resetModel();
        $this->resetScope();

        return $this->parserResult($results);
    }

    public function find($id, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();
        $model = $this->model->find($id, $columns);
        $this->resetModel();

        return $this->parserResult($model);
    }

    public function delete($id)
    {
        $this->applyScope();

        $result = false;
        if ($model = $this->model->find($id)) {
            $originalModel = clone $model;
        }
        $this->resetModel();
        if ($model instanceof Model && isset($originalModel) && $originalModel instanceof Model) {
            $result = $model->delete();
            event(new RepositoryEntityDeleted($this, $originalModel));
        }

        return $this->parserResult($result);
    }

    public function deleteWhere(array $where)
    {
        $this->applyScope();

        $this->applyConditions($where);

        $deleted = $this->model->delete();

        event(new RepositoryEntityDeleted($this, $this->model->getModel()));

        $this->resetModel();

        return $this->parserResult($deleted);
    }

    public function count(array $where = null)
    {
        $this->applyCriteria();
        $this->applyScope();

        if (is_array($where) && !empty($where)) {
            $this->applyConditions($where);
        }

        $count = $this->model->count();
        $this->resetModel();

        return $this->parserResult($count);
    }

    /*************************************************************************************************************/
    /*******************************       以下是覆写l5的部分方法，去除Presenter等逻辑       **************************/
    /*************************************************************************************************************/

    /**
     * Retrieve first data of repository, or return new Entity
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function firstOrNew(array $attributes = [])
    {
        $this->applyCriteria();
        $this->applyScope();

        $model = $this->model->firstOrNew($attributes);

        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * Retrieve first data of repository, or create new Entity
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function firstOrCreate(array $attributes = [])
    {
        $this->applyCriteria();
        $this->applyScope();

        $model = $this->model->firstOrCreate($attributes);

        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * Update a entity in repository by id
     *
     * @throws ValidatorException
     *
     * @param array $attributes
     * @param       $id
     *
     * @return mixed
     */
    public function update(array $attributes, $id)
    {
        $this->applyScope();

        if (!is_null($this->validator)) {
            // we should pass data that has been casts by the model
            // to make sure data type are same because validator may need to use
            // this data to compare with data that fetch from database.
            $attributes = $this->model->newInstance()->forceFill($attributes)->makeVisible($this->model->getHidden())->toArray();

            $this->validator->with($attributes)->setId($id)->passesOrFail(ValidatorInterface::RULE_UPDATE);
        }

        $model = $this->model->findOrFail($id);
        $model->fill($attributes);
        $model->save();

        $this->resetModel();

        event(new RepositoryEntityUpdated($this, $model));

        return $this->parserResult($model);
    }

    /**
     * Update or Create an entity in repository
     *
     * @throws ValidatorException
     *
     * @param array $attributes
     * @param array $values
     *
     * @return mixed
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        $this->applyScope();

        if (!is_null($this->validator)) {
            $this->validator->with($attributes)->passesOrFail(ValidatorInterface::RULE_UPDATE);
        }


        $model = $this->model->updateOrCreate($attributes, $values);

        $this->resetModel();

        event(new RepositoryEntityUpdated($this, $model));

        return $this->parserResult($model);
    }


    /**
     * @param mixed $result
     * @return mixed
     */
    public function parserResult($result)
    {
        return $result;
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed|void
     */
    public function findWithoutFail($id, $columns = ['*'])
    {
        try {
            return $this->find($id, $columns);
        } catch (Exception $e) {
            return;
        }
    }

}