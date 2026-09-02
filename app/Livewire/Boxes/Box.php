<?php

namespace App\Livewire\Boxes;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{Box as BoxModel, User};
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Box extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $paginate_cant = 25;
    public $user = '';

    public function render()
    {
        $this->user = Auth::User();
        $current_date = date('Y-m-d').' 23:59:59';

        // Historial de cierres (incluye status=0 con end_date null para no perderlos)
        $boxes = BoxModel::where(function($q) use ($current_date) {
                $q->where('end_date', '<=', $current_date)
                  ->orWhereNull('end_date');
            })
            ->orderBy('id', 'desc')
            ->paginate($this->paginate_cant);

        // Turnos abiertos actualmente (solo visible para root)
        $turnosAbiertos = [];
        if ($this->user->hasRole('root') || $this->user->name === 'TCI_DEV') {
            $turnosAbiertos = BoxModel::with('user')
                ->where('status', 0)
                ->orderBy('start_date', 'asc')
                ->get();
        }

        return view('livewire.boxes.box', [
            'boxes'          => $boxes,
            'turnosAbiertos' => $turnosAbiertos,
        ]);
    }

    // Abre el modal de denominaciones
    public function openModalMoney($box_id)
    {
        $box = BoxModel::find($box_id)?->toArray() ?? [];
        $this->dispatch('openModalMoney', [
            'box'    => count($box) ? $box : null,
            'status' => count($box) ? 1 : 0,
        ]);
    }

    // ROOT: forzar cierre de un turno atascado
    public function forceClose($box_id)
    {
        if (!Auth::User()->hasRole('root') && Auth::User()->name !== 'TCI_DEV') {
            return;
        }

        $box = BoxModel::find($box_id);
        if (!$box || $box->status !== 0) {
            return;
        }

        $box->end_date = now();
        $box->status   = 1; // cierre forzado — se marca como correcto
        $box->save();

        session()->flash('success', "Turno #{$box->id} cerrado forzosamente.");
    }

    // ROOT: reabrir un turno cerrado (por si se cerró accidentalmente)
    public function reopen($box_id)
    {
        if (!Auth::User()->hasRole('root') && Auth::User()->name !== 'TCI_DEV') {
            return;
        }

        $box = BoxModel::find($box_id);
        if (!$box) {
            return;
        }

        $box->status   = 0;
        $box->end_date = null;
        $box->save();

        session()->flash('success', "Turno #{$box->id} reabierto. El cajero puede intentar cerrar nuevamente.");
    }
}
