<?php

namespace App\Support\Traits;

trait WithModalHandlers
{
    public $opened = false;

    public function openModal()
    {
        $this->opened = true;
    }

    public function closeModal()
    {
        $this->opened = false;
    }
}
