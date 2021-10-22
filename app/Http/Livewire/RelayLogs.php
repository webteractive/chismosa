<?php

namespace App\Http\Livewire;

use App\Models\Relay;
use Livewire\Component;
use Livewire\WithPagination;
use App\Support\Traits\WithModalHandlers;

class RelayLogs extends Component
{
    use WithModalHandlers, WithPagination;
    
    public $relayId;

    protected $listeners = [
        'viewLogs'
    ];

    public function viewLogs($relayId)
    {
        $this->fill(['relayId' => $relayId]);
        $this->openModal();
    }

    public function close()
    {
        $this->closeModal();
        $this->reset();
    }

    public function getRelay($id)
    {
        return Relay::query()
            ->find($id);
    }

    public function getRelayProperty()
    {
        return $this->getRelay($this->relayId);
    }

    public function getLogsProperty()
    {
        if ($relay = $this->getRelayProperty()) {
            return $relay->logs()
                ->latest()
                ->paginate(10);
        }

        return collect([]);
    }

    public function render()
    {
        return view('livewire.relay-logs');
    }
}
