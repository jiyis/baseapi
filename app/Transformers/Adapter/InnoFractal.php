<?php
/**
 * Created by PhpStorm.
 * User: Gary.F.Dong
 * Date: 2017/4/18
 * Time: 9:32
 * Desc: 覆写Fractal，使其兼容RESTFUL风格输出
 */

namespace App\Transformers\Adapter;

use Dingo\Api\Transformer\Adapter\Fractal as BaseAdapter;
use League\Fractal\Manager as FractalManager;
use App\Serializer\InnoSerializer;

class InnoFractal extends BaseAdapter
{
    public function __construct(
        FractalManager $fractal,
        $includeKey = 'include',
        $includeSeparator = ',',
        $eagerLoading = true
    ) {
        $fractal->setSerializer(new InnoSerializer());
        parent::__construct($fractal, $includeKey, $includeSeparator, $eagerLoading);
    }

}