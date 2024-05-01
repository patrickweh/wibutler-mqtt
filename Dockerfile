FROM php:8.2-cli
COPY . /data
WORKDIR /data
CMD [ "php", "wibutler-websocket-mqtt", "mqtt:listen" ]
