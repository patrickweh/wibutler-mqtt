<?php

namespace App\Dto;

use App\Enums\HomeassistantComponentEnum;
use App\Http\Integrations\Wibutler\Requests\Devices\Components\Patch;
use App\Http\Integrations\Wibutler\WibutlerConnector;
use Illuminate\Support\Str;

class Climate extends HomeassistantObject
{
    public array $attributes = [
        'state' => 'heat',
    ];

    public function getConfig(): bool|string
    {
        return json_encode(
            [
                'name' => $this->name,
                'device_class' => static::getComponent()->name,
                'unique_id' => name_to_unique_id($this->name),

                'current_temperature_topic' => $this->getMqttTopic(['tmp']),
                'current_temperature_template' => '{{ (value_json | float) * 0.01 }}',

                'temperature_state_topic' => $this->getMqttTopic(['tsp']),
                'temperature_state_template' => '{{ (value_json | float) * 0.5 + 10 }}',
                'temperature_command_topic' => $this->getMqttTopic(['set_tsp']),

                'mode_state_topic' => $this->getMqttTopic(['state']),

                'temp_step' => 0.5,

                'min_temp' => 10,
                'max_temp' => 30,

                'modes' => ['heat'],

                'command_topic' => $this->getMqttTopic(['set']),
            ]
        );
    }

    static function getComponent(): HomeassistantComponentEnum
    {
        return HomeassistantComponentEnum::climate;
    }

    public function set_tsp(int $value): void
    {
        $response = (new WibutlerConnector())->send(
            new Patch($this->id,
                'TSP',
                [
                    'value' => (string) $value,
                    'type' => 'numeric'
                ]
            )
        );
    }
}
