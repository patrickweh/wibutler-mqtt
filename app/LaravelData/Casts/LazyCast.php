<?php

namespace App\LaravelData\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;

class LazyCast implements Cast
{
    public function __construct(public ?\Closure $condition = null)
    {

    }
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        return $this->condition ? Lazy::when($this->condition, fn() => $value) : Lazy::create(fn() => $value);
    }
}
