<?php namespace Frontend\User\Components;

use Lang;
use Auth;
use Event;
use Flash;
use Request;
use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use ValidationException;

class Session extends ComponentBase
{
    const ALLOW_ALL = 'all';
    const ALLOW_GUEST = 'guest';
    const ALLOW_USER = 'user';

    public function componentDetails()
    {
        return [
            'name'        => 'Session',
            'description' => 'Adds the user session to a page and can restrict page access'
        ];
    }

    public function defineProperties()
    {
        return [
            'security' => [
                'title'       => 'Allow only',
                'description' => 'Who is allowed to access this page',
                'type'        => 'dropdown',
                'default'     => 'all',
                'options'     => [
                    'all'   => 'All',
                    'user'  => 'Users',
                    'guest' => 'Guests'
                ]
            ],
            'redirect' => [
                'title'       => 'Redirect to',
                'description' => 'Page name to redirect if access is denied',
                'type'        => 'dropdown',
                'default'     => ''
            ]
        ];
    }

    public function getRedirectOptions()
    {
        return [''=>'- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Executed when this component is bound to a page or layout.
     */
    public function onRun()
    {
        $redirectUrl = $this->controller->pageUrl($this->property('redirect'));
        $allowedGroup = $this->property('security', self::ALLOW_ALL);
        $isAuthenticated = Auth::check();

        if (!$isAuthenticated && $allowedGroup == self::ALLOW_USER) {
            return Redirect::guest($redirectUrl);
        }
        elseif ($isAuthenticated && $allowedGroup == self::ALLOW_GUEST) {
            return Redirect::guest($redirectUrl);
        }

        $this->page['user'] = $this->user();
    }

    /**
     * Log out the user
     *
     * Usage:
     *   <a data-request="onLogout">Sign out</a>
     *
     * With the optional redirect parameter:
     *   <a data-request="onLogout" data-request-data="redirect: '/good-bye'">Sign out</a>
     *
     */
    public function onLogout()
    {
        $user = Auth::getUser();

        Auth::logout();

        if ($user) {
            Event::fire('frontend.user.logout', [$user]);
        }

        $url = post('redirect', Request::fullUrl());
        Flash::success('You have been successfully logged out!');

        return Redirect::to($url);
    }

    /**
     * Returns the logged in user, if available, and touches
     * the last seen timestamp.
     * @return RainLab\User\Models\User
     */
    public function user()
    {
        if (!$user = Auth::getUser()) {
            return null;
        }

        $user->touchLastSeen();

        return $user;
    }
}
