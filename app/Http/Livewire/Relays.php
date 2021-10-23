<?php

namespace App\Http\Livewire;

use App\Models\Relay;
use Livewire\Component;

class Relays extends Component
{
    protected $listeners = [
        'refresh' => '$refresh'
    ];

    public function getRelaysProperty()
    {
        return Relay::all();
    }

    public function render()
    {
        return view('livewire.relays');
    }
}
