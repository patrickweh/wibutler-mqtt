<?php

namespace App\Http\Integrations\Wibutler;

use App\Http\Integrations\Wibutler\Auth\WibutlerAuthenticator;
use Illuminate\Support\Str;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class WibutlerConnector extends Connector
{
    use AcceptsJson;

    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return Str::finish(config('app.wibutler_host'), ':8081/').'api';
    }

    /**
     * Default headers for every request
     *
     * @return string[]
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Default HTTP client options
     *
     * @return string[]
     */
    protected function defaultConfig(): array
    {
        return [];
    }

    public function defaultAuth(): WibutlerAuthenticator
    {
        return new WibutlerAuthenticator();
    }
}
