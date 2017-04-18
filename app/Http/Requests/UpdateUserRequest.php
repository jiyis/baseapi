<?php

namespace App\Http\Requests;


class UpdateUserRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'sometimes|required|max:20|alpha_dash',
            'email' => 'sometimes|required|email',
            'password' => 'sometimes|required|max:20',
            'profile_id' => 'sometimes|required|integer',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '',
            'name.alpha_dash' => '用户仅允许字母、数字、破折号（-）以及底线（_）',
            'name.max' => '用户名称最多20个字符',
            'email.required' => '邮箱不能为空',
            'email.email' => '邮箱非法',
            'password.required' => '密码不能为空',
            'password.max' => '密码最多20个字符',
            'profile_id.required' => '资料不能为空',
        ];
    }
}
