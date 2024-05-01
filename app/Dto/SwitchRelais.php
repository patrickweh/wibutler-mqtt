<?php

namespace App\Dto;

use App\Enums\HomeassistantComponentEnum;
use Illuminate\Support\Str;

class SwitchRelais extends HomeassistantObject
{
    static function getComponent(): HomeassistantComponentEnum
    {
        return HomeassistantComponentEnum::switch;
    }

    public function __set(string $name, mixed $value)
    {
        if (str_starts_with($name, 'swt_')) {
            $value = [
                'event_type' => Str::after($name, 'swt') . $value
            ];

            parent::__set('event', $value);

            return;
        }

        parent::__set($name, $value);
    }

    public function getConfig(): bool|string
    {
        return json_encode(
            [
                'name' => $this->name,
                'device_class' => static::getComponent()->name,

                'state_topic' => $this->getMqttTopic(['event']),
                'command_topic',

                'state_on',
                'state_off',

            ]
        );
    }
}
