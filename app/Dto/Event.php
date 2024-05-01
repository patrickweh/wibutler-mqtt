<?php

namespace App\Dto;

use App\Enums\HomeassistantComponentEnum;
use Illuminate\Support\Str;

class Event extends HomeassistantObject
{
    static function getComponent(): HomeassistantComponentEnum
    {
        return HomeassistantComponentEnum::event;
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

    public function mapKeyValueForMqtt($key, $value): array
    {
        if (str_starts_with($key, 'swt_')) {
            $translated = [
                'swt_a' => 'left',
                'swt_b' => 'right',
            ];

            $value = $translated[$key] . (Str::startsWith($value, '1') ? '_lower_' : '_upper_');
            $value .= Str::endsWith($value, 'D') ? 'down' : 'up';

            $value = [
                'event_type' => $value
            ];

            return ['event', $value];
        }

        return parent::mapKeyValueForMqtt($key, $value);
    }

    public function getConfig(): bool|string
    {
        return json_encode(
            [
                'name' => $this->name,

                'state_topic' => $this->getMqttTopic(['event']),
                'event_types' => [
                    'left_lower_up',
                    'left_lower_down',
                    'left_upper_up',
                    'left_upper_down',
                    'right_lower_up',
                    'right_lower_down',
                    'right_upper_up',
                    'right_upper_down',
                ],
            ]
        );
    }
}
