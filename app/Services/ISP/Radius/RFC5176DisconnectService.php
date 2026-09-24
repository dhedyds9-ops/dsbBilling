<?php

namespace App\Services\ISP\Radius;

use App\Models\ISP\RadiusNas;
use Illuminate\Support\Facades\Log;
use Throwable;

class RFC5176DisconnectService
{
    public const CODE_DISCONNECT_REQUEST = 40;
    public const CODE_DISCONNECT_ACK = 41;
    public const CODE_DISCONNECT_NAK = 42;
    public const CODE_COA_REQUEST = 43;
    public const CODE_COA_ACK = 44;
    public const CODE_COA_NAK = 45;

    public const ATTR_USER_NAME = 1;
    public const ATTR_NAS_IP_ADDRESS = 4;
    public const ATTR_NAS_PORT = 5;
    public const ATTR_FRAMED_IP_ADDRESS = 8;
    public const ATTR_CALLING_STATION_ID = 31;
    public const ATTR_CALLED_STATION_ID = 30;
    public const ATTR_ACCT_SESSION_ID = 44;
    public const ATTR_MESSAGE_AUTHENTICATOR = 80;
    public const ATTR_ERROR_CAUSE = 101;

    public function sendDisconnect(
        RadiusNas $nas,
        array $identifiers,
        int $port = 3799,
        int $timeoutSec = 3,
        int $retries = 2,
    ): array {
        $packet = $this->buildPacket(self::CODE_DISCONNECT_REQUEST, $nas, $identifiers);
        return $this->sendAndReceive($nas, $packet, self::CODE_DISCONNECT_ACK, $port, $timeoutSec, $retries);
    }

    public function sendCoA(
        RadiusNas $nas,
        array $identifiers,
        array $changeAttrs = [],
        int $port = 3799,
        int $timeoutSec = 3,
        int $retries = 2,
    ): array {
        $allAttrs = array_merge($identifiers, $changeAttrs);
        $packet = $this->buildPacket(self::CODE_COA_REQUEST, $nas, $allAttrs);
        return $this->sendAndReceive($nas, $packet, self::CODE_COA_ACK, $port, $timeoutSec, $retries);
    }

    private function buildPacket(int $code, RadiusNas $nas, array $identifiers): string
    {
        $identifier = random_int(0, 255);
        $secret = (string)($nas->nas_secret ?? '');

        $attrs = '';
        $attrs .= $this->encodeAttrs($identifiers);

        $lengthPlaceholder = 4 + strlen($attrs) + 18;
        $header = pack('CCn', $code, $identifier, $lengthPlaceholder);
        $authenticator = $this->requestAuthenticator();

        $tempPacket = $header . $authenticator . $attrs . $this->attrZeroMA();
        $msgAuth = hash_hmac('md5', $tempPacket, $secret, true);
        $maAttr = $this->encodeAttr(self::ATTR_MESSAGE_AUTHENTICATOR, $msgAuth);

        $attrsWithMa = $attrs . $maAttr;
        $length = 4 + 16 + strlen($attrsWithMa);
        $header = pack('CCn', $code, $identifier, $length);

        $preAuth = $header . $authenticator . $attrsWithMa;
        $finalAuth = md5($preAuth . $secret, true);

        return $header . $finalAuth . $attrsWithMa;
    }

    private function encodeAttrs(array $attrs): string
    {
        $out = '';
        foreach ($attrs as $type => $value) {
            if (is_int($type)) {
                $out .= $this->encodeAttr($type, $value);
            }
        }
        return $out;
    }

    private function encodeAttr(int $type, string|int $value): string
    {
        if (is_int($value) && in_array($type, [self::ATTR_NAS_IP_ADDRESS, self::ATTR_FRAMED_IP_ADDRESS], true)) {
            if (filter_var($value, FILTER_VALIDATE_IP)) {
                $value = inet_pton($value);
            } else {
                $value = pack('N', $value);
            }
        } else {
            $value = (string)$value;
        }

        $len = 2 + strlen($value);
        if ($len > 253) {
            $value = substr($value, 0, 251);
            $len = 253;
        }
        return pack('CC', $type, $len) . $value;
    }

    private function attrZeroMA(): string
    {
        return pack('CC', self::ATTR_MESSAGE_AUTHENTICATOR, 18) . str_repeat("\x00", 16);
    }

    private function requestAuthenticator(): string
    {
        return random_bytes(16);
    }

    private function sendAndReceive(
        RadiusNas $nas,
        string $packet,
        int $expectCode,
        int $port,
        int $timeoutSec,
        int $retries,
    ): array {
        $ip = $nas->nas_ip_address;
        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return [
                'success' => false,
                'code' => 0,
                'error' => 'Invalid NAS IP address',
                'nas' => $nas->nas_name,
            ];
        }

        $lastError = 'Unknown error';
        $attempts = 0;

        while ($attempts <= $retries) {
            $attempts++;
            try {
                $sock = @fsockopen('udp://' . $ip, $port, $errno, $errstr, $timeoutSec);
                if (!$sock) {
                    $lastError = "fsockopen failed ($errno): $errstr";
                    usleep(300_000);
                    continue;
                }

                stream_set_timeout($sock, $timeoutSec);
                fwrite($sock, $packet);
                $response = '';
                $start = microtime(true);
                while (!feof($sock)) {
                    if (microtime(true) - $start > $timeoutSec) {
                        break;
                    }
                    $chunk = @fread($sock, 4096);
                    if ($chunk === false || $chunk === '') {
                        break;
                    }
                    $response .= $chunk;
                    if (strlen($response) >= 20) {
                        break;
                    }
                }
                @fclose($sock);

                if (strlen($response) < 20) {
                    $lastError = 'Empty or too-short response from NAS (timeout?)';
                    usleep(200_000);
                    continue;
                }

                $unpacked = unpack('Ccode/Cid/nlength/H32auth', substr($response, 0, 20));
                if (!$unpacked) {
                    $lastError = 'Failed to parse response header';
                    continue;
                }

                $resCode = (int)($unpacked['code'] ?? 0);
                $resNak = $expectCode + 1;
                $success = $resCode === $expectCode;

                $errorCause = null;
                if ($resCode === $resNak && strlen($response) > 20) {
                    $errorCause = $this->decodeErrorCause(substr($response, 20));
                }

                return [
                    'success' => $success,
                    'code' => $resCode,
                    'code_name' => $this->codeName($resCode),
                    'identifier' => (int)$unpacked['id'],
                    'length' => (int)$unpacked['length'],
                    'error_cause' => $errorCause,
                    'nas' => $nas->nas_name,
                    'nas_ip' => $ip,
                    'attempts' => $attempts,
                    'error' => $success ? null : ($errorCause ?? ($resCode === $resNak ? 'NAS returned NAK' : 'Unexpected response code')),
                ];
            } catch (Throwable $e) {
                $lastError = get_class($e) . ': ' . $e->getMessage();
                Log::warning('RFC5176 send exception', [
                    'nas' => $nas->nas_name,
                    'ip' => $ip,
                    'err' => $e->getMessage(),
                ]);
                usleep(200_000);
            }
        }

        return [
            'success' => false,
            'code' => 0,
            'error' => "Max retries reached: {$lastError}",
            'nas' => $nas->nas_name,
            'nas_ip' => $ip,
            'attempts' => $attempts,
        ];
    }

    private function decodeErrorCause(string $attrsPayload): ?string
    {
        $map = [
            401 => 'Residual Session Context Removed',
            402 => 'Invalid EAP Packet (Reserved)',
            403 => 'Unsupported Attribute',
            404 => 'Invalid Attribute Value',
            405 => 'NAS Identification Mismatch',
            406 => 'Missing Requested Attribute',
            407 => 'NAS Authentication Failure',
            501 => 'Administratively Prohibited',
            502 => 'Request Not Routable (Proxy)',
            503 => 'Session Context Not Found',
            504 => 'Session Context Not Removable',
            505 => 'Other Proxy Processing Error',
            506 => 'Resources Unavailable',
            507 => 'Billing Not Supported',
        ];
        try {
            $pos = 0;
            $len = strlen($attrsPayload);
            while ($pos + 2 <= $len) {
                $type = ord($attrsPayload[$pos]);
                $attrLen = ord($attrsPayload[$pos + 1]);
                if ($attrLen < 2 || $pos + $attrLen > $len) {
                    break;
                }
                $value = substr($attrsPayload, $pos + 2, $attrLen - 2);
                if ($type === self::ATTR_ERROR_CAUSE && strlen($value) >= 4) {
                    $u = unpack('Nc', $value);
                    $code = (int)($u['c'] ?? 0);
                    return $map[$code] ?? "Error-Cause={$code}";
                }
                $pos += $attrLen;
            }
        } catch (Throwable $e) {
        }
        return null;
    }

    private function codeName(int $code): string
    {
        return match ($code) {
            self::CODE_DISCONNECT_ACK => 'Disconnect-ACK',
            self::CODE_DISCONNECT_NAK => 'Disconnect-NAK',
            self::CODE_COA_ACK => 'CoA-ACK',
            self::CODE_COA_NAK => 'CoA-NAK',
            self::CODE_DISCONNECT_REQUEST => 'Disconnect-Request',
            self::CODE_COA_REQUEST => 'CoA-Request',
            default => 'Unknown(' . $code . ')',
        };
    }
}
