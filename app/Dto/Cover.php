<?php

namespace App\Dto;

use App\Enums\HomeassistantComponentEnum;
use App\Http\Integrations\Wibutler\Requests\Devices\Components\Patch;
use App\Http\Integrations\Wibutler\WibutlerConnector;
use Illuminate\Support\Str;

class Cover extends HomeassistantObject
{
    public function getConfig(): bool|string
    {
        return json_encode(
            [
                'name' => $this->name,
                'unique_id' => name_to_unique_id($this->name),

                'topic' => $this->getMqttTopic(['state']),
                'state_opening' => 'Opening',
                'state_closing' => 'Closing',
                'state_stopped' => 'Stopped',

                'command_topic' => $this->getMqttTopic(['set']),

                'set_position_topic' => $this->getMqttTopic(['set_position']),
                'position_topic' => $this->getMqttTopic(['curpos']),
                'position_open' => 0,
                'position_closed' => 100,

                'payload_open' => 'ON',
                'payload_close' => 'OFF',
            ]
        );
    }

    static function getComponent(): HomeassistantComponentEnum
    {
        return HomeassistantComponentEnum::cover;
    }

    public function set_position(int $value): void
    {
        (new WibutlerConnector())->send(
            new Patch($this->id,
                'POS',
                [
                    'value' => (string) $value,
                    'type' => 'numeric'
                ]
            )
        );
    }

    public function set(string $value): void
    {
        (new WibutlerConnector())->send(
            new Patch($this->id,
                'SWT_POS',
                [
                    'value' => $value,
                    'type' => 'switch'
                ]
            )
        );
    }
}
