<?php

namespace App\Livewire\Pengaturan\Telegram;

use App\Livewire\AdminComponent;

class Agent extends AdminComponent
{
    public string $activeModule = 'pengaturan';
    public string $activePage = 'telegram';

    public $search = '';

    public array $agents = [
        ['id' => 1, 'name' => 'Budi Santoso', 'department' => 'NOC', 'chat_id' => '123456789', 'token_type' => 'default', 'custom_token' => '', 'status' => 'active', 'events' => 'alarm,ticket'],
        ['id' => 2, 'name' => 'Siti Aminah', 'department' => 'Billing', 'chat_id' => '987654321', 'token_type' => 'custom', 'custom_token' => 'bot_token_abc', 'status' => 'active', 'events' => 'billing'],
    ];

    public $showModal = false;
    public $form = [
        'id' => null,
        'name' => '',
        'department' => '',
        'chat_id' => '',
        'token_type' => 'default', // default, custom
        'custom_token' => '',
        'status' => 'active',
        'events' => '',
    ];

    public $testResult = null;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Pengaturan', 'url' => '#'],
            ['label' => 'Telegram Gateway', 'url' => route('pengaturan.telegram')],
            ['label' => 'Agent'],
        ];
    }

    public function create()
    {
        $this->form = [
            'id' => null,
            'name' => '',
            'department' => '',
            'chat_id' => '',
            'token_type' => 'default',
            'custom_token' => '',
            'status' => 'active',
            'events' => '',
        ];
        $this->showModal = true;
    }

    public function edit($index)
    {
        $this->form = $this->agents[$index];
        $this->showModal = true;
    }

    public function save()
    {
        if ($this->form['id']) {
            foreach ($this->agents as $k => $v) {
                if ($v['id'] === $this->form['id']) {
                    $this->agents[$k] = $this->form;
                }
            }
        } else {
            $this->form['id'] = time();
            $this->agents[] = $this->form;
        }
        $this->showModal = false;
        session()->flash('success', 'Agent berhasil disimpan.');
    }

    public function testChat($index)
    {
        // Simulasi testing chat
        $this->testResult = "Pesan berhasil dikirim ke Chat ID: " . $this->agents[$index]['chat_id'];
        session()->flash('success', $this->testResult);
    }

    public function render()
    {
        $filtered = collect($this->agents)->filter(function($item) {
            return str_contains(strtolower($item['name']), strtolower($this->search)) ||
                   str_contains(strtolower($item['chat_id']), strtolower($this->search));
        })->toArray();

        return view('livewire.pengaturan.telegram.agent', [
            'filteredAgents' => $filtered,
        ]);
    }
}
