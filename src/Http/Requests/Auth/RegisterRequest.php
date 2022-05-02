<?php

namespace Laraflow\LaraFlow\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Laraflow\Laraflow\Rules\PhoneNumber;
use Laraflow\Laraflow\Rules\Username;
use Laraflow\Laraflow\Supports\Constant;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {

        $rules = [
            'name' => 'required|string|min:3|max:255',
            'agree_terms' => 'required|string'
        ];

        //Credential Field
        if (config('triplea.credential_field') == Constant::LOGIN_EMAIL
            || (config('triplea.credential_field') == Constant::LOGIN_OTP
                && config('triplea.credential_otp_field') == Constant::OTP_EMAIL)) {
            $rules['email'] = 'required|min:10|max:255|string|email:rfc,dns';

        } elseif (config('triplea.credential_field') == Constant::LOGIN_MOBILE
            || (config('triplea.credential_field') == Constant::LOGIN_OTP
                && config('triplea.credential_otp_field') == Constant::OTP_MOBILE)) {
            $rules['mobile'] = ['required', 'string', 'min:11', 'max:11', new PhoneNumber];

        } elseif (config('triplea.credential_field') == Constant::LOGIN_USERNAME) {
            $rules['username'] = ['required', new Username, 'min:5', 'max:255', 'string'];
        }

        //Password Field
        if (config('triplea.credential_field') != Constant::LOGIN_OTP) {
            $rules['password'] = ['required', 'confirmed', 'min:' . config('triplea.minimum_password_length'), 'max:255', 'string'];
        }

        return $rules;

    }
}