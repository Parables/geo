<?php

namespace Parables\Geo\Actions\Concerns;

use Parables\Geo\Actions\Contracts\Toastable;

trait HasToastable
{
    protected  Toastable $toastable;

    public function toastable(Toastable $toastable): static
    {
        $this->toastable = $toastable;
        return $this;
    }
}
