<?php

namespace App\Http\Integrations\Wibutler\Requests\Devices\Components;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class Patch extends Request implements HasBody
{
    use HasJsonBody;

    public function __construct(public string $id, public string $component, public ?array $payload = null)
    {

    }

    /**
     * Define the HTTP method
     */
    protected Method $method = Method::PATCH;

    /**
     * Define the endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/devices/'.$this->id.'/components/'.$this->component;
    }

    public function defaultBody(): ?array
    {
        return $this->payload;
    }
}
