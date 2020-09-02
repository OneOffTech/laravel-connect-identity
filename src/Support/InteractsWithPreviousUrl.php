<?php

namespace Oneofftech\Identities\Support;

trait InteractsWithPreviousUrl
{
    protected function savePreviousUrl()
    {
        session()->put('_oot.identities.previous_url', url()->previous());
    }

    protected function getPreviousUrl()
    {
        return session()->get('_oot.identities.previous_url', url()->previous());
    }
}
