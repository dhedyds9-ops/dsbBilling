<?php

namespace App\Services\Adapters\Provisioning\Support;

use Exception;
use phpseclib3\Net\SSH2;

class TelnetSshClient
{
    protected SSH2|SimpleTelnet|null $connection = null;
    protected string $mode;
    protected string $host;
    protected int $port;
    protected string $username;
    protected string $password;
    protected string $enableSecret;
    protected int $timeout;

    public function connect(string $host, ?string $username, ?string $password, string $mode = 'telnet', int $port = 0, ?string $enableSecret = '', int $timeout = 10): static
    {
        $this->host     = $host;
        $this->username = $username ?? '';
        $this->password = $password ?? '';
        $this->mode     = $mode;
        $this->enableSecret = $enableSecret ?? '';
        $this->timeout  = $timeout;

        if ($mode === 'ssh') {
            if ($port === 0) {
                $port = 22;
            }
            if (!class_exists(SSH2::class)) {
                throw new Exception('phpseclib3 SSH2 not installed. Run: composer require phpseclib/phpseclib');
            }
            $ssh = new SSH2($host, $port, $timeout);
            if (!$ssh->login($username, $password)) {
                throw new Exception('SSH Login failed');
            }
            $this->connection = $ssh;
        } else {
            if ($port === 0) {
                $port = 23;
            }
            $telnet = new SimpleTelnet($host, $port, $timeout);
            $telnet->login($username, $password);
            $this->connection = $telnet;
        }

        if ($this->enableSecret !== '' || $mode === 'telnet') {
            $this->enterEnableMode();
        }

        return $this;
    }

    public function execute(string $command, bool $disablePager = true, string $endMarker = null): string
    {
        if ($this->connection === null) {
            throw new Exception('Connection not established');
        }
        if ($disablePager) {
            $this->disablePager();
        }
        if ($this->mode === 'ssh') {
            $output = $this->connection->exec($command);
            return $output !== false ? (string)$output : '';
        }
        $output = '';
        $this->connection->write($command . "\n");
        if ($endMarker) {
            $output = $this->connection->read($endMarker);
        } else {
            $output = $this->connection->read('/[>#]/');
        }
        return $output;
    }

    protected function disablePager(): void
    {
        if ($this->mode === 'ssh') {
            $this->connection->exec("terminal length 0\n");
        } else {
            $this->connection->write("terminal length 0\n");
            $this->connection->read('/[>#]/');
        }
    }

    protected function enterEnableMode(): void
    {
        if ($this->mode === 'ssh') {
            $this->connection->write("enable\n");
            $this->connection->write($this->enableSecret . "\n");
        } else {
            $this->connection->write("enable\n");
            $out = $this->connection->read('/(Password:|#)/i');
            if (str_contains(strtolower($out), 'password')) {
                $this->connection->write($this->enableSecret . "\n");
                $this->connection->read('/#/');
            }
        }
    }

    public function disconnect(): void
    {
        if ($this->connection !== null) {
            try {
                $this->connection->disconnect();
            } catch (Exception) {
            }
            $this->connection = null;
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}

class SimpleTelnet
{
    protected $socket;
    protected $host;
    protected $port;
    protected $timeout;

    public function __construct(string $host, int $port = 23, int $timeout = 10)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$this->socket) {
            throw new Exception("Cannot connect to $host:$port - $errstr ($errno)");
        }
        stream_set_timeout($this->socket, $timeout);
    }

    public function login(string $username, string $password): void
    {
        $this->read('/(User\s*name|Username|Login|login|user):/i');
        $this->write($username . "\n");
        $this->read('/Password:/i');
        $this->write($password . "\n");
        $this->read('/[>#]/');
    }

    public function write(string $buffer): void
    {
        if ($this->socket) {
            fwrite($this->socket, $buffer);
        }
    }

    public function read(string $pattern): string
    {
        if (!$this->socket) {
            return '';
        }
        $result = '';
        $start = time();
        while (!feof($this->socket)) {
            if (time() - $start > $this->timeout) {
                throw new Exception("Timeout waiting for pattern: $pattern");
            }
            $c = fgetc($this->socket);
            if ($c === false) {
                usleep(10000);
                continue;
            }

            // Telnet negotiation: IAC (255)
            if (ord($c) === 255) {
                $verb = fgetc($this->socket);
                $opt = fgetc($this->socket);
                if (ord($verb) === 253) {
                    $this->write(chr(255) . chr(252) . $opt);
                } elseif (ord($verb) === 251) {
                    $this->write(chr(255) . chr(254) . $opt);
                }
                continue;
            }

            $result .= $c;
            if (preg_match($pattern, $result)) {
                return $result;
            }
        }
        return $result;
    }

    public function disconnect(): void
    {
        if ($this->socket) {
            @fclose($this->socket);
            $this->socket = null;
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
