<?php

namespace Oneofftech\Identities\Support;

trait InteractsWithAdditionalAttributes
{
    
    /**
     * The attributes that should be retrieved from
     * the request to append to the redirect
     *
     * @var array
     */
    protected function redirectAttributes()
    {
        if (method_exists($this, 'attributes')) {
            return $this->attributes();
        }

        return property_exists($this, 'attributes') ? $this->attributes : [];
    }

    protected function pushAttributes($request)
    {
        $attributes = $this->redirectAttributes() ?? [];

        if (empty($attributes)) {
            return;
        }
        
        $request->session()->put('_oot.identities.attributes', json_encode($request->only($attributes)));
    }

    protected function pullAttributes($request)
    {
        $attributes = $this->redirectAttributes() ?? [];

        if (empty($attributes)) {
            return [];
        }

        $savedAttributes = $request->session()->pull('_oot.identities.attributes') ?? null;

        if (! $savedAttributes) {
            return [];
        }

        return json_decode($savedAttributes, true);
    }
}
