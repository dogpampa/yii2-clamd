<?php

namespace dogpampa\clamd;

/* This class can be used to connect to Clamd running over the network */
class ClamdNetwork extends ClamdBase {
    private $host;
    private $port;

    /* You need to pass the host address and the port the the server */
    public function __construct($host=CLAMD_HOST, $port=CLAMD_PORT) {
        $this->host = $host;
        $this->port = $port;
    }

    protected function getSocket() {
        $socket = socket_create(AF_INET, SOCK_STREAM, 0);
        socket_connect($socket, $this->host, $this->port);
        return $socket;
    }
}

