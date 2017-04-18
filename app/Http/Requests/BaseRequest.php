<?php
/**
 * Created by PhpStorm.
 * User: Gary.F.Dong
 * Date: 2017/4/18
 * Time: 11:07
 * Desc:
 */

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest;
use App\Exceptions\ValidationHttpException;

abstract class BaseRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

}