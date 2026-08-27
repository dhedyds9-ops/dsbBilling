<?php

namespace App\Services\Adapters\Provisioning\Support;

use Exception;
use phpseclib3\Net\SSH2;
use phpseclib3\Net\Telnet;

class TelnetSshClient
{
    protected SSH2|Telnet|null $connection = null;
    protected string $mode;
    protected string $host;
    protected int $port;
    protected string $username;
    protected string $password;
    protected string $enableSecret;
    protected int $timeout;

    public function connect(string $host, string $username, string $password, string $mode = 'telnet', int $port = 0, string $enableSecret = '', int $timeout = 10): static
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->mode = $mode;
        $this->enableSecret = $enableSecret;
        $this->timeout = $timeout;

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
            if (!class_exists(Telnet::class)) {
                throw new Exception('phpseclib3 Telnet not installed. Run: composer require phpseclib/phpseclib');
            }
            $telnet = new Telnet($host, $port, $timeout);
            $telnet->login($username, $password);
            $this->connection = $telnet;
        }

        if ($enableSecret !== '') {
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
            $this->connection->read('/Password:/');
            $this->connection->write($this->enableSecret . "\n");
            $this->connection->read('/[>#]/');
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
