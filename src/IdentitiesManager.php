<?php

namespace Oneofftech\Identities;

use InvalidArgumentException;
use Illuminate\Support\Manager;
use Laravel\Socialite\Facades\Socialite;

class IdentitiesManager extends Manager
{
    /**
     * @inheritdoc
     */
    protected function createDriver($driver)
    {
        // Attempt to create a driver
        // or fallback to Socialite

        try {
            return parent::createDriver($driver);
        } catch (InvalidArgumentException $iaex) {
            return Socialite::driver($driver);
        }
    }
    
    /**
     * Create an instance of the gitlab driver.
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function createGitlabDriver()
    {
        $config = $this->container->make('config')['services.gitlab'];

        return Socialite::with('gitlab')
            ->scopes([
                'openid',
                'read_api',
                // todo: make scopes configurable
            ]);
    }

    /**
     * Create an instance of the dropbox driver.
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function createDropboxDriver()
    {
        $config = $this->container->make('config')['services.dropbox'];

        return Socialite::driver('dropbox');
    }
    
    /**
     * Get the default driver name.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getDefaultDriver()
    {
        throw new InvalidArgumentException('No Identity or Socialite driver was specified.');
    }
}
