<?php

namespace App\Enums;

use App\Dto\Event;
use App\Dto\Climate;
use App\Dto\Cover;
use App\Dto\HomeassistantObject;
use Illuminate\Support\Arr;

enum HomeassistantComponentEnum: string
{
    case binary_sensor = 'binary_sensor';
    case cover = 'cover';
    case fan = 'fan';
    case light = 'light';
    case lock = 'lock';
    case sensor = 'sensor';
    case switch = 'switch';
    case vacuum = 'vacuum';
    case weather = 'weather';
    case camera = 'camera';
    case climate = 'climate';
    case media_player = 'media_player';
    case remote = 'remote';
    case scene = 'scene';
    case script = 'script';
    case sun = 'sun';
    case timer = 'timer';
    case zone = 'zone';

    case event = 'event';

    public static function fromWibutler(string $component): ?HomeassistantComponentEnum
    {
        return match ($component) {
            'Blind' => self::cover,
            'SwitchingRelays'=> self::switch,
            'Switches' => self::event,
            'RoomOperatingPanels' => self::climate,
            'Gateway' => self::sensor,
            default => null,
        };
    }

    public static function dto(array $wibutlerDevice): ?HomeassistantObject
    {
        $type = $wibutlerDevice['type'];

        $component = self::fromWibutler($type);

        $attributes = Arr::pluck(data_get($wibutlerDevice, 'components', []), 'value', 'name');
        $attributes = array_merge($attributes, $wibutlerDevice);

        return match ($component) {
            //static::binary_sensor => new BinarySensor($wibutlerDevice),
            static::cover => new Cover($attributes),
            static::event => new Event($attributes),
            static::climate => new Climate($attributes),
            //static::sensor => new Sensor($wibutlerDevice),
            default => null,
        };
    }

    public function getDto(): ?HomeassistantObject
    {
        return match ($this) {
            //static::binary_sensor => new BinarySensor($wibutlerDevice),
            static::cover => new Cover(),
            //static::switch => new SwitchDevice($wibutlerDevice),
            static::climate => new Climate(),
            static::event => new Event(),
            //static::sensor => new Sensor($wibutlerDevice),
            default => null,
        };
    }
}
