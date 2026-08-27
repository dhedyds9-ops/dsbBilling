<?php

namespace App\Http\Requests\Radius;

use Illuminate\Foundation\Http\FormRequest;

class PreAuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'protocol' => ['nullable', 'string', 'in:pppoe,hotspot'],
            'nas_ip_address' => ['nullable', 'ip'],
            'nas_name' => ['nullable', 'string', 'max:255'],
            'calling_station_id' => ['nullable', 'string', 'max:64'],
            'called_station_id' => ['nullable', 'string', 'max:128'],
            'framed_ip_address' => ['nullable', 'ip'],
            'service_type' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function normalized(): array
    {
        $data = $this->validated();
        $data['protocol'] ??= 'pppoe';
        $data['nas_ip_address'] ??= $this->attributes->get('radius_nas')?->nas_ip_address ?? $this->ip();
        return $data;
    }
}
