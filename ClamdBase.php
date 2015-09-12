<?php

namespace dogpampa\clamd;

/* clamd.php v0.1 ClamAV daemon interface.
 *
 * Author  : Barakat S. <b4r4k47@hotmail.com>
 * Licence : MIT
 */

define('CLAMD_PIPE', '/var/run/clamav/clamd.ctl');
define('CLAMD_HOST', '127.0.0.1');
define('CLAMD_PORT', 3310);
define('CLAMD_MAXP', 20000);

/* EICAR is a simple test for AV scanners, see: https://en.wikipedia.org/wiki/EICAR_test_file */
$EICAR_TEST = 'X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*';


/* An abstract class that `ClamdPipe` and `ClamdNetwork` will inherit. */
abstract class ClamdBase {
    
    abstract protected function getSocket();

    /* Send command to Clamd */
    private function sendCommand($command) {
        $return = null;

        $socket = $this->getSocket();
        socket_send($socket, $command, strlen($command), 0);
        socket_recv($socket, $return, CLAMD_MAXP, 0);
        socket_close($socket);

        return $return;
    }
    
    /* `ping` command is used to see whether Clamd is alive or not */
    public function ping() {
        $return = $this->sendCommand('PING');
        return strcmp($return, 'PONG') ? true : false;
    }

    /* `version` is used to receive the version of Clamd */
    public function version() {
        return trim($this->sendCommand('VERSION'));
    }

    /* `reload` Reload Clamd */
    public function reload() {
        return $this->sendCommand('RELOAD');
    }

    /* `shutdown` Shutdown Clamd */
    public function shutdown() {
        return $this->sendCommand('SHUTDOWN');
    }

    /* `fileScan` is used to scan single file. */
    public function fileScan($file) {
        list($file, $stats) = explode(':', $this->sendCommand('SCAN ' .  $file));

        return array( 'file' => $file, 'stats' => trim($stats));
    }

    /* `continueScan` is used to scan multiple files/directories.  */
    public function continueScan($file) {
        $return = array();
        
        foreach( explode("\n", trim($this->sendCommand('CONTSCAN ' .  $file))) as $results ) {
            list($file, $stats) = explode(':', $results);
            array_push($return, array( 'file' => $file, 'stats' => trim($stats) ));
        }
        return $return;
    }
    
    /* `streamScan` is used to scan a buffer. */
    public function streamScan($buffer) {
        $port    = null;
        $socket  = null;
        $command = 'STREAM';
        $return  = null;
  
        $socket = $this->getSocket();
        socket_send($socket, $command, strlen($command), 0);
        socket_recv($socket, $return, CLAMD_MAXP, 0);

        sscanf($return, 'PORT %d\n', $port);

        $stream = socket_create(AF_INET, SOCK_STREAM, 0);
        socket_connect($stream, CLAMD_HOST, $port);
        socket_send($stream, $buffer, strlen($buffer), 0);
        socket_close($stream);
        
        socket_recv($socket, $return, CLAMD_MAXP, 0);

        socket_close($socket);
  
        return array('stats' => trim(str_replace('stream: ', '', $return)));
    }
}
