<?php

namespace App\Events;

use App\Models\Producto;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockBajoEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Producto $producto;

    /**
     * Create a new event instance.
     */
    public function __construct(Producto $producto)
    {
        $this->producto = $producto;
    }
}
