<?php

namespace App\Dto;

use App\Enums\HomeassistantComponentEnum;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use PhpMqtt\Client\Facades\MQTT;

abstract class HomeassistantObject implements Arrayable
{
    protected array $attributes = [];

    protected static array $blacklist = [
        'unifiederr',
        'stateimage',
        'outputs',
        'tags',
        'protected',
        'components',
        'imagename',
        'inputs',
        'centerx',
        'centery',
        'category',
        'components',
        '_prf_0_title',
        'etsp',
    ];

    public string $id;

    public ?string $name = null;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    abstract static function getComponent(): HomeassistantComponentEnum;

    abstract public function getConfig(): bool|string;

    public function getMqttTopic(array $path = []): string
    {
        return implode(
            '/',
            array_merge(
                [
                    config('app.mqtt_topic'),
                    static::getComponent()->name,
                    'wibutler',
                    $this->id,
                ],
                $path
            )
        );
    }

    public function sendConfigToMqtt(): void
    {
        $mqtt = MQTT::connection();

        $mqtt->publish($this->getMqttTopic(['config']), $this->getConfig(), 1, false);

        $mqtt->disconnect();
    }

    public function publish(): array
    {
        $mqtt = MQTT::connection();

        $response = [];
        $attributes = array_change_key_case($this->attributes, CASE_LOWER);
        foreach (Arr::except($attributes, static::$blacklist) as $key => $value) {
            [$key, $value] = $this->mapKeyValueForMqtt($key, $value);
            $topic = $this->getMqttTopic([strtolower($key)]);

            if (is_array($value)) {
                $value = json_encode($value);
            }

            $mqtt->publish($topic, $value, 1, false);

            $response[$topic] = $value;
        }

        $mqtt->disconnect();

        return $response;
    }

    public function mapKeyValueForMqtt($key, $value): array
    {
        return [$key, $value];
    }

    public function fill(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function clear(): void
    {
        $this->attributes = [];
    }

    public function __set(string $name, mixed $value)
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        } else {
            if (in_array($name, static::$blacklist)) {
                return;
            }

            $this->attributes[$name] = $value;
        }
    }

    public function __get(string $name): mixed
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return $this->attributes[$name] ?? null;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
