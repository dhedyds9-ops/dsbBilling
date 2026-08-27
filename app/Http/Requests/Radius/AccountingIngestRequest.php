<?php

namespace App\Http\Requests\Radius;

use Illuminate\Foundation\Http\FormRequest;

class AccountingIngestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'acct_session_id' => ['required', 'string', 'max:255'],
            'acct_status_type' => ['nullable'],
            'username' => ['nullable', 'string', 'max:255'],
            'nas_ip_address' => ['nullable', 'ip'],
            'nas_name' => ['nullable', 'string', 'max:255'],
            'nas_port_id' => ['nullable', 'string', 'max:64'],
            'framed_ip_address' => ['nullable', 'ip'],
            'framed_protocol' => ['nullable'],
            'acct_start_time' => ['nullable'],
            'acct_stop_time' => ['nullable'],
            'acct_input_octets' => ['nullable', 'integer', 'min:0'],
            'acct_output_octets' => ['nullable', 'integer', 'min:0'],
            'acct_input_gigawords' => ['nullable', 'integer', 'min:0'],
            'acct_output_gigawords' => ['nullable', 'integer', 'min:0'],
            'acct_input_packets' => ['nullable', 'integer', 'min:0'],
            'acct_output_packets' => ['nullable', 'integer', 'min:0'],
            'acct_session_time' => ['nullable', 'integer', 'min:0'],
            'acct_delay_time' => ['nullable', 'integer', 'min:0'],
            'acct_terminate_cause' => ['nullable', 'string', 'max:64'],
            'terminate_cause_id' => ['nullable', 'integer', 'min:0'],
            'calling_station_id' => ['nullable', 'string', 'max:64'],
            'called_station_id' => ['nullable', 'string', 'max:128'],
            'connect_info' => ['nullable', 'string', 'max:128'],
            'acct_unique_session_id' => ['nullable', 'string', 'max:128'],
            'raw_payload' => ['nullable', 'array'],
            'ingest_source' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function normalized(): array
    {
        $data = $this->validated();
        $data['nas_ip_address'] ??= $this->attributes->get('radius_nas')?->nas_ip_address ?? $this->ip();
        $data['raw_payload'] = $data['raw_payload'] ?? $this->except(['raw_payload', 'signature', 'message_authenticator']);
        return $data;
    }
}
