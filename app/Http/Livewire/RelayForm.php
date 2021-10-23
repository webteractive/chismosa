<?php

namespace App\Http\Livewire;

use App\Models\Relay;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use App\Support\Traits\WithModalHandlers;

class RelayForm extends Component
{
    use WithModalHandlers;

    public $relayId;
    public $relay;
    
    protected $listeners = [
        'create',
        'edit'
    ];

    public function create()
    {
        $this->openModal();
    }

    public function edit($id)
    {
        tap($this->getRelay($id), function ($relay) {
            $this->fill([
                'relayId' => $relay->id,
                'relay' => $relay->toArray(),
            ]);

            $this->openModal();
        });
    }

    public function save()
    {
        $this->validate([
            'relay.name' => 'required',
            'relay.type' => 'required',
            'relay.webhook_type' => 'required',
            'relay.webhook_url' => 'required|url',
        ], [
            'relay.name.required' => __('validation.required', ['attribute' => 'Name']),
            'relay.type.required' => __('validation.required', ['attribute' => 'Type']),
            'relay.webhook_type.required' => __('validation.required', ['attribute' => 'Webhook Type']),
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
            'user_id' => auth()->id(),
            'secret' => Hash::make(config('chismosa.key'))
        ]));

        $this->emitTo('relays', 'refresh');
        // Notify toast
        $this->close();
    }

    protected function update()
    {
        tap($this->getRelay($this->relayId), function ($relay) {
            $relay->update($this->relay);
        });

        $this->emitTo('relays', 'refresh');
        // Notify toast
        $this->close();
    }

    public function close()
    {
        $this->reset();
        $this->resetErrorBag();
        $this->resetValidation();
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
