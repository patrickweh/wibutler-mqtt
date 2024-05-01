<?php

namespace App\Http\Integrations\Wibutler\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class Login extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method
     */
    protected Method $method = Method::POST;

    /**
     * Define the endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/login';
    }

    public function defaultBody()
    {
        return [
            'username' => config('app.wibutler_username'),
            'password' => config('app.wibutler_password'),
        ];
    }
}
