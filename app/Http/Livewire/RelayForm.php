<?php

namespace App\Http\Livewire;

use App\Models\Relay;
use Livewire\Component;

class RelayForm extends Component
{
    public $opened = false;
    public $relayId;
    public $relay;
    
    protected $listeners = [
        'create',
        'edit'
    ];

    public function create()
    {
        $this->opened = true;
    }

    public function edit($id)
    {
        tap($this->getRelay($id), function ($relay) {
            $this->fill([
                'relayId' => $relay->id,
                'relay' => $relay->toArray(),
                'opened' => true
            ]);
        });
    }

    public function save()
    {
        $this->validate([
            'relay.name' => 'required',
            'relay.webhook_url' => 'required|url',
        ], [
            'relay.name.required' => __('validation.required', ['attribute' => 'Name']),
            'relay.webhook_url.required' => __('validation.required', ['attribute' => 'Webhook URL']),
            'relay.webhook_url.url' => __('validation.url', ['attribute' => 'Webhook URL']),
        ]);

        if ($this->relayId) {
            return $this->update();
        }

        return $this->store();
    }

    protected function store()
    {
        Relay::create(array_merge($this->relay, [
            'user_id' => auth()->id()
        ]));

        // Notify toast
        $this->reset();
    }

    protected function update()
    {
        tap($this->getRelay($this->relayId), function ($relay) {
            $relay->update($this->relay);
        });

        // Notify toast
        $this->reset();
    }

    public function close()
    {
        $this->reset();
    }

    public function getRelay($id)
    {
        return Relay::find($id);
    }

    public function render()
    {
        return view('livewire.relay-form');
    }
}
