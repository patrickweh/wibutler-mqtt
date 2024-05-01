<?php

namespace App\Commands;

use App\Enums\HomeassistantComponentEnum;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use PhpMqtt\Client\Facades\MQTT;

class MqttSubscriber extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'mqtt:listen';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle(): void
    {
        $mqtt = MQTT::connection();
        $topic = implode(
        '/',
            [
                config('app.mqtt_topic'),
                '+',
                'wibutler',
                '+',
                '+'
            ],
        );

        $this->info('Subscribing to ' . $topic);

        $mqtt->subscribe($topic, function (string $topic, string $message) {
            $topicArray = explode('/', $topic);
            $type = $topicArray[1];
            $id = $topicArray[3];
            $method = $topicArray[4];

            $enum = HomeassistantComponentEnum::{$type};
            $dto = $enum->getDto();
            $dto->id = $id;

            if (str_starts_with($method, 'set')) {
                $this->info($topic . ' -> ' . $message);
                $dto->{$method}($message);
            }
        }, 1);
        $mqtt->loop();
    }
}
