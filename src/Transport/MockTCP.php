<?php


namespace PlanetTeamSpeak\TeamSpeak3Framework\Transport;


use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TransportException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;

class MockTCP extends TCP
{
    const S_WELCOME_L0 = 'TS3';
    const S_WELCOME_L1 = 'Welcome to the TeamSpeak 3 ServerQuery interface, type "help" for a list of commands and "help <command>" for information on a specific command.';
    const S_ERROR_OK = 'error id=0 msg=ok';

    const CMD = array(
        'login serveradmin secret' => self::S_ERROR_OK,
        'login client_login_name=serveradmin client_login_password=secret' => self::S_ERROR_OK,
    );

    protected $reply = null;

    public function connect() {
        if ($this->stream !== null) {
            return;
        }

        $this->reply = sprintf("%s\n%s\n", self::S_WELCOME_L0, self::S_WELCOME_L1);
        $this->stream = true;
    }

    public function readLine($token = "\n") {
        $line = StringHelper::factory("");
        $this->connect();

        while (!$line->endsWith($token)) {
            // $this->waitForReadyRead();

            $data = $this->fget();
            Signal::getInstance()->emit(strtolower($this->getAdapterType()) . "DataRead", $data);
            if ($data === false) {
                if ($line->count()) {
                    $line->append($token);
                }
                else {
                    throw new TransportException("connection to server '" . $this->config["host"] . ":" . $this->config["port"] . "' lost");
                }
            }
            else {
                $line->append($data);
            }
        }

        return $line->trim();
    }

    public function sendLine($data, $separator = "\n") {
        $this->send($data);
    }

    /**
     * Writes data to the stream.
     *
     * @param string $data
     * @return void
     * @throws TransportException
     * @throws ServerQueryException
     */
    public function send($data) {
        $this->fwrite($data);
        Signal::getInstance()->emit(strtolower($this->getAdapterType()) . "DataSend", $data);
    }

    protected function fget() {
        $this->reply = explode("\n", $this->reply);
        $reply = array_shift($this->reply);
        $this->reply = join("\n", $this->reply);
        return $reply . "\n";
    }

    protected function fwrite($data) {

        if(!key_exists($data, self::CMD)) {
            $this->reply = "error id=1 msg=Unkown\n";
            return;
        }

        $this->reply = sprintf("%s\n%s\n", self::CMD[$data], self::S_ERROR_OK);
    }
}
