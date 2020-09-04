<?php

namespace Oneofftech\Identities\View\Components;

use Illuminate\View\Component;
use InvalidArgumentException;

class IdentityLink extends Component
{
    private static $availableActions = ['register', 'login'];
    
    private static $actionLabels = [
        'register' => 'Register via :Provider',
        'login' => 'Log in via :Provider'
    ];

    /**
     * The action the button should do
     *
     * @var string
     */
    public $action;

    /**
     * The identity provider to use
     *
     * @var string
     */
    public $provider;
    
    /**
     * The label for the link
     *
     * @var string
     */
    public $label;

    /**
     * Create a new component instance.
     *
     * @param string $provider The identity provider
     * @param string $action The action the button should perform. Available: login, register. Default login.
     * @param string $label The label for the link. It will be used as string to localize.
     *                      Default null a default label in the form "$action via $provider" will be used.
     * @return self
     */
    public function __construct($provider, $action = 'login', $label = null)
    {
        if (! in_array($action, self::$availableActions)) {
            throw new InvalidArgumentException("Specified action [$action] is not supported.");
        }

        $this->provider = $provider;
        $this->action = $action;
        $this->label = $label ?? (self::$actionLabels[$this->action] ?? ucfirst($action));
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return <<<'blade'
            <a href="{{ route('oneofftech::' . $action . '.provider', ['provider' => $provider]) }}" {{ $attributes }}>
                {{ __($label, ['provider' => $provider]) }}
            </a>
        blade;
    }
}
