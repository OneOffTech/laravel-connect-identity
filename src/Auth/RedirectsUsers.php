<?php

namespace Oneofftech\Identities\Auth;

/**
 * Copied from https://github.com/laravel/ui/blob/3.x/auth-backend/RedirectsUsers.php
 * (c) Taylor Otwell and contributors
 */
trait RedirectsUsers
{
    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
    }
}