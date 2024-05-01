<?php

namespace App\Commands;

use App\Dto\HomeassistantObject;
use App\Enums\HomeassistantComponentEnum;
use App\Http\Integrations\Wibutler\Requests\Devices;
use App\Http\Integrations\Wibutler\Requests\Login;
use App\Http\Integrations\Wibutler\WibutlerConnector;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use WebSocket\Client;

class WebSocketListen extends Command
{
    protected $signature = 'websocket:listen';
    protected $description = 'Listen to a WebSocket stream';

    private WibutlerConnector $wibutlerConnector;

    public array $devices = [];

    public array $createdDevices = [];

    public function __construct()
    {
        $this->wibutlerConnector = new WibutlerConnector();

        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Obtaining devices from wibutler');
        $this->getDevices();
        $this->info('Sending birth messages');
        $this->sendBirthMessage();

        $this->info('Obtaining wibutler token');
        $token = $this->wibutlerConnector->send(new Login());
        if ($token->successful()) {
            $token = $token->json('sessionToken');
        } else {
            return;
        }

        $url = Str::finish(str_replace(['http://', 'https://'], ['ws://', 'wss://'], config('app.wibutler_host')), '/') . 'api/stream/' . $token;
        $this->info('Connecting to: ' . $url);
        $options = [
            'timeout' => 60
        ];

        $client = new Client($url, $options);

        while (true) {
            $message = $client->receive();
            $messageArray = json_decode($message, true);
            $device = null;

            // send message to mqtt
            /** @var HomeassistantObject $device */
            $device = $this->createdDevices[data_get($messageArray, 'data.id')] ?? false;

            if (! $device) {
                continue;
            }

            $attributes = Arr::pluck(data_get($messageArray, 'data.components', []), 'value', 'name');
            $device->clear();
            $device->fill($attributes);

            $response = $device->publish();

            foreach ($response as $topic => $value) {
                $this->info('Message published to mqtt: ' . $topic . ' -> ' . $value);
            }
        }
    }

    public function getDevices(): void
    {
        $response = $this->wibutlerConnector->send(new Devices());

        $this->devices = $response->json('devices');
    }

    public function sendBirthMessage(): void
    {
        foreach ($this->devices ?? [] as $device) {
            /** @var HomeassistantObject $dto */
            $dto = HomeassistantComponentEnum::dto($device);

            if (! $dto) {
                continue;
            }

            $this->createdDevices[$dto->id] = $dto;

            $dto->sendConfigToMqtt();

            $birthPublish = $dto->publish();
            foreach ($birthPublish as $topic => $value) {
                $this->info('Birth message published to mqtt: ' . $topic . ' -> ' . $value);
            }
        }
    }

    private function wibutlerDeviceToHomeassistant(array $device)
    {
        // HMIs
        // RoomOperatingPanels
        // Blind
        // SwitchingRelays
        // Switches
        // WeatherSensors
        // FloorHeatingController
        // Gateway
    }
}
