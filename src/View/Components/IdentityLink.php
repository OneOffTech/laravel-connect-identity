<?php

namespace Oneofftech\Identities\View\Components;

use Illuminate\Support\Arr;
use Illuminate\View\Component;
use InvalidArgumentException;

class IdentityLink extends Component
{
    private static $availableActions = ['register', 'login', 'connect'];

    private static $actionLabels = [
        'register' => 'Register via :Provider',
        'login' => 'Log in via :Provider',
        'connect' => 'Connect :Provider',
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
     * Additional parameters to append on the redirect request
     *
     * @var array
     */
    public $parameters;

    /**
     * Create a new component instance.
     *
     * @param  string  $provider  The identity provider
     * @param  string  $action  The action the button should perform. Available: login, register. Default login.
     * @param  string  $label  The label for the link. It will be used as string to localize.
     *                         Default null a default label in the form "$action via $provider" will be used.
     * @param  array|null  $parameter  Additional parameters to append on the redirect request
     * @return self
     */
    public function __construct($provider, $action = 'login', $label = null, $parameters = null)
    {
        if (! in_array($action, self::$availableActions)) {
            throw new InvalidArgumentException("Specified action [$action] is not supported.");
        }

        $this->provider = $provider;
        $this->action = $action;
        $this->label = $label ?? (self::$actionLabels[$this->action] ?? ucfirst($action));
        $this->parameters = Arr::wrap($parameters) ?? [];
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return <<<'blade'
    <a href="{{ route('oneofftech::' . $action . '.provider', array_merge($parameters, ['provider' => $provider])) }}" {{ $attributes }}>
        {{ __($label, ['provider' => $provider]) }}
    </a>
blade;
    }
}
