<?php

namespace App\Criteria;

use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class SearchCriteria implements CriteriaInterface
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        $request = $this->request;
        $prefix = 'repository.criteria.params';
        $searchStr = $request->get(config($prefix . '.advancedSearch', 'advancedSearch'), null);
        if (!$searchStr) {
            $searchStr = $request->get(config($prefix . '.simpleSearch', 'simpleSearch'), null);
        }
        //$filterStr = $request->get(config($prefix . '.filter', 'filter'), null);
        $orderStr = $request->get(config($prefix . '.sort', 'sort'), null);
        $start = $request->get(config($prefix . '.start', 'start'), null);
        $rows = $request->get(config($prefix . '.rows', 'rows'), null);
        $with = $request->get(config($prefix . '.with', 'with'), null);

        if ($searchStr) {
            $searchData = $this->filterAvailableSearchData(
                $this->parserSearchData($searchStr),
                $repository->getSearchBlacklist()
            );
            $model = $model->where(function ($query) use ($searchData) {
                foreach ($searchData as $data) {
                    $query->orWhere(function ($query) use ($data) {
                        foreach ($data as $field => $value) {
                            foreach ($this->parseExpValue($value) as $result) {
                                list($condition, $value) = array_pad((array)$result, 2, null);
                                list($relation, $field) = array_pad($this->parseRelation($field), 2, null);
                                if (!$field || !$condition) {
                                    continue;
                                }
                                $modelTableName = $query->getModel()->getTable();
                                $fn = function ($query, $tbl = null) use ($field, $condition, $value) {
                                    $field = implode('.', array_filter([$tbl, $field]));
                                    switch (strtolower(trim($condition))) {
                                        case 'in':
                                            $query->whereIn($field, (array)$value);
                                            break;
                                        case 'not in':
                                            $query->whereNotIn($field, (array)$value);
                                            break;
                                        default:
                                            $query->where($field, $condition, $value);
                                    }
                                };
                                if ($relation) {
                                    $query->whereHas($relation, $fn);
                                } else {
                                    $fn($query, $modelTableName);
                                }
                            }
                        }
                    });
                }
            });
        }

        if (isset($orderStr) && !empty($orderStr)) {
            $orders = preg_split('/\s*,\s*/', $orderStr);
            foreach ($orders as $order) {
                list($field, $sort) = array_pad(preg_split('/\s+/', $order), 2, null);
                list($relation, $field) = array_pad($this->parseRelation($field), 2, null);
                if (!$sort) {
                    $sort = 'asc';
                }
                if ($field && $sort) {
                    if ($relation) {
                        $model = $model->with([$relation => function ($query) use ($field, $sort) {
                            $query->orderBy($field, $sort);
                        }]);
                    } else {
                        $model = $model->orderBy(implode('.', array_filter([$relation, $field])), $sort);
                    }
                }
            }
        }

        if ($start) {
            $rows = $rows ?: 20;
            $model = $model->take($rows)->skip(($start - 1) * $rows);
        }

        if ($with) {
            $with = array_filter(preg_split('/\s*,\s*/', $with));
            if (is_array($with) && !empty($with)) {
                $model = $model->with($with);
            }
        }
        return $model;
    }

    protected function parseRelation($field)
    {
        $relation = null;
        if (is_string($field) && stripos($field, '.')) {
            $explode = explode('.', $field);
            $field = array_pop($explode);
            $relation = implode('.', $explode);
        }
        return [$relation, $field];
    }

    protected function parserSearchData($search)
    {
        $searchData = [];
        foreach (preg_split('/\s+OR\s+/i', $search) as $pieces) {
            $data = [];
            foreach (preg_split('/\s+AND\s+/i', $pieces) as $piece) {
                list($field, $value) = explode(':', $piece);
                if (!is_null($field) && !is_numeric($field) && !is_null($value)) {
                    $data[$field] = urldecode($value);
                }
            }
            if (!empty($data)) {
                $searchData[] = $data;
            }
        }

        return $searchData;
    }

    protected function parseExpValue($v)
    {
        $matched = [];
        $result = [['=', $v]];
        switch (true) {
            case preg_match('/^(?:(NOT)\s+)?[^"\']*\*[^"\']*$/i', $v, $matched):
                $result = [
                    [
                        trim((isset($matched[1]) ? trim(strtolower($matched[1])) : '') . ' like'),
                        str_replace('*', '%', $v)
                    ]
                ];
                break;
            case preg_match('/^(?:(NOT)\s+)?\[(0|\-?[1-9]\d*)\s+TO\s+(\-?[1-9]\d*)\]$/i', $v, $matched):
                if ($matched[2] > $matched[3]) {
                    return [[]];
                }
                if ($matched[2] == $matched[3]) {
                    $result = [[isset($matched[1]) && $matched[1] ? '<>' : '=', $matched[2]]];
                    break;
                }
                if (isset($matched[1]) && $matched[1]) {
                    $result = [
                        ['not in', range($matched[2], $matched[3])]
                    ];
                } else {
                    $result = [
                        ['>=', $matched[2]],
                        ['<=', $matched[3]]
                    ];
                }
                break;
            case preg_match('/^(?:(NOT)\s+)?\{(0|\-?[1-9]\d*)\s+TO\s+(\-?[1-9]\d*)\}$/i', $v, $matched):
                if ($matched[3] - $matched[2] < 2) {
                    return [[]];
                }
                if ($matched[3] - $matched[2] == 2) {
                    $result = [[isset($matched[1]) && $matched[1] ? '<>' : '=', $matched[2] + 1]];
                    break;
                }
                if (isset($matched[1]) && $matched[1]) {
                    $result = [
                        ['not in', range($matched[2] + 1, $matched[3] - 1)]
                    ];
                } else {
                    $result = [
                        ['>', $matched[2]],
                        ['<', $matched[3]]
                    ];
                }
                break;
            case preg_match('/^(?:(NOT)\s+)?<((?:0|\-?[1-9]\d*)(?:,(?:0|\-?[1-9]\d*))*)>$/i', $v, $matched):
                $result = [
                    [
                        trim((isset($matched[1]) ? trim(strtolower($matched[1])) : '') . ' in'),
                        explode(',', $matched[2])
                    ]
                ];
                break;
        }
        return $result;
    }

    protected function filterAvailableSearchData($searchData, $searchBlacklist)
    {
        foreach ($searchData as $index => $data) {
            foreach ($data as $field => $value) {
                if (in_array($field, $searchBlacklist)) {
                    unset($searchData[$index][$field]);
                }
            }
        }
        $searchData = array_filter($searchData, function ($v) {
            return is_array($v) && !empty($v);
        });
        return $searchData;
    }
}
