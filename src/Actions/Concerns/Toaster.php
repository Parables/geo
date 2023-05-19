<?php

namespace Parables\Geo\Actions\Concerns;

trait Toaster
{
    public function toast(string $message, string $type = 'info'): void
    {
        $this->$type($message);
    }
}
