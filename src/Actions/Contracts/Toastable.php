<?php

namespace Parables\Geo\Actions\Contracts;

interface Toastable
{
    public function toast(string $message, string $type = 'info'): void;
}
