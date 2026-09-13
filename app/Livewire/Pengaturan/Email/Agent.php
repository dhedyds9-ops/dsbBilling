<?php

namespace App\Livewire\Pengaturan\Email;

use App\Livewire\AdminComponent;

class Agent extends AdminComponent
{
    public string $activeModule = 'pengaturan';
    public string $activePage = 'email';

    public $search = '';

    public array $agents = [
        ['id' => 1, 'name' => 'Finance Dept', 'email' => 'finance@domain.com', 'status' => 'active', 'events' => 'billing'],
        ['id' => 2, 'name' => 'IT Support', 'email' => 'support@domain.com', 'status' => 'active', 'events' => 'ticket,alarm'],
    ];

    public array $smtp = [
        'host' => 'smtp.mailtrap.io',
        'port' => 2525,
        'username' => '',
        'password' => '',
        'encryption' => 'tls',
        'from_address' => 'noreply@domain.com',
        'from_name' => 'dsBilling Enterprise'
    ];

    public $showModal = false;
    public $form = [
        'id' => null,
        'name' => '',
        'email' => '',
        'status' => 'active',
        'events' => '',
    ];

    public function mount()
    {
        parent::mount();
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Pengaturan', 'url' => '#'],
            ['label' => 'Email Gateway', 'url' => '#'],
            ['label' => 'Agent'],
        ];
    }

    public function saveSmtp()
    {
        session()->flash('success_smtp', 'Konfigurasi SMTP berhasil disimpan.');
    }

    public function create()
    {
        $this->form = [
            'id' => null,
            'name' => '',
            'email' => '',
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
        session()->flash('success', 'Agent Email berhasil disimpan.');
    }

    public function render()
    {
        $filtered = collect($this->agents)->filter(function($item) {
            return str_contains(strtolower($item['name']), strtolower($this->search)) ||
                   str_contains(strtolower($item['email']), strtolower($this->search));
        })->toArray();

        return view('livewire.pengaturan.email.agent', [
            'filteredAgents' => $filtered,
        ]);
    }
}
