<?php namespace Frontend\User\Components;

use Auth;
use Mail;
use Validator;
use ValidationException;
use ApplicationException;
use Cms\Classes\ComponentBase;
use Frontend\User\Models\User as UserModel;

class ResetPassword extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Reset Password',
            'description' => 'Forgotten password form'
        ];
    }

    public function defineProperties()
    {
        return [
            'paramCode' => [
                'title'       => 'Reset Code Param',
                'description' => 'The page URL parameter used for the reset code',
                'type'        => 'string',
                'default'     => 'code'
            ]
        ];
    }

    /**
     * Trigger the password reset email
     */
    public function onRestorePassword()
    {
        $rules = [
            'email' => 'required|email|between:6,255'
        ];

        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        if (!$user = UserModel::findByEmail(post('email'))) {
            throw new ApplicationException('The user was not found with the given credentials');
        }

        $code = implode('!', [$user->id, $user->getResetPasswordCode()]);
        $link = $this->controller->currentPageUrl([
            $this->property('paramCode') => $code
        ]);

        $data = [
            'name' => $user->name,
            'link' => $link,
            'code' => $code
        ];

        Mail::send('frontend.user::mail.restore', $data, function($message) use ($user) {
            $message->to($user->email, $user->full_name);
        });
    }

    /**
     * Perform the password reset
     */
    public function onResetPassword()
    {
        $rules = [
            'code'     => 'required',
            'password' => 'required|between:4,255'
        ];

        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        /*
         * Break up the code parts
         */
        $parts = explode('!', post('code'));
        if (count($parts) != 2) {
            throw new ValidationException(['code' => 'Invalid activation code supplied']);
        }

        list($userId, $code) = $parts;

        if (!strlen(trim($userId)) || !($user = Auth::findUserById($userId))) {
            throw new ApplicationException('The user was not found with the given credentials');
        }

        if (!$user->attemptResetPassword($code, post('password'))) {
            throw new ValidationException(['code' => 'Invalid activation code supplied']);
        }
    }

    /**
     * Returns the reset password code from the URL
     * @return string
     */
    public function code()
    {
        $routeParameter = $this->property('paramCode');

        return $this->param($routeParameter);
    }
}
