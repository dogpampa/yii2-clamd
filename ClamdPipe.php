<?php

namespace dogpampa\clamd;

/* This class can be used to connect to local socket, the default */
class ClamdPipe extends ClamdBase {
    private $pip;

    /* You need to pass the path to the socket pipe */
    public function __construct($pip=CLAMD_PIPE) {
        $this->pip = $pip;
    }

    protected function getSocket() {
        $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
        socket_connect($socket, $this->pip);
        return $socket;
    }
}
