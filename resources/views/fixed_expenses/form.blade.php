@extends('layouts.app')
@section('title', ($item->exists ? 'Editar' : 'Nuevo') . ' gasto fijo')

@section('content')
    <h1 class="page-title mb-3">{{ $item->exists ? 'Editar' : 'Nuevo' }} gasto fijo</h1>
    <form method="POST" action="{{ $item->exists ? route('fixed-expenses.update', $item) : route('fixed-expenses.store') }}" class="card card-stat p-4">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $item->name) }}" placeholder="Ej: Recibo de luz" required>
        </div>

        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">Categoría</label>
                <select name="category_id" class="form-select" required>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id', $item->category_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Monto promedio</label>
                <input type="number" step="0.01" min="0" name="average_amount" class="form-control" value="{{ old('average_amount', $item->average_amount) }}" required>
            </div>
        </div>

        <div class="row g-2 mt-2">
            <div class="col-md-6">
                <label class="form-label">Quincena habitual</label>
                <select name="fortnight" class="form-select">
                    <option value="1" @selected(old('fortnight', $item->fortnight) == 1)>1ª quincena</option>
                    <option value="2" @selected(old('fortnight', $item->fortnight) == 2)>2ª quincena</option>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check form-switch">
                    <input type="hidden" name="active" value="0">
                    <input class="form-check-input" type="checkbox" name="active" value="1" id="activeSwitch" @checked(old('active', $item->active))>
                    <label class="form-check-label" for="activeSwitch">Activo (se carga automáticamente)</label>
                </div>
            </div>
        </div>

        {{-- Intervalos: frecuencia con la que se carga el gasto fijo --}}
        @php
            $intOpts = [1 => 'Cada mes', 2 => 'Cada 2 meses (bimestral)', 3 => 'Cada 3 meses (trimestral)', 6 => 'Cada 6 meses (semestral)', 12 => 'Cada 12 meses (anual)'];
            $curInterval = (int) old('interval_months', $item->interval_months ?: 1);
            $curAnchor = old('anchor', ($item->anchor_year && $item->anchor_month)
                ? sprintf('%04d-%02d', $item->anchor_year, $item->anchor_month) : '');
        @endphp
        <hr class="my-4" style="border-color: var(--border);">
        <div class="mb-2">
            <h2 class="h6 mb-1"><i class="bi bi-arrow-repeat"></i> Frecuencia</h2>
            <small class="text-muted">Para recibos que no llegan todos los meses (p. ej. el agua, cada 2 meses).</small>
        </div>
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">¿Cada cuánto llega?</label>
                <select name="interval_months" id="intervalSelect" class="form-select">
                    @foreach ($intOpts as $val => $lbl)
                        <option value="{{ $val }}" @selected($curInterval === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6" id="anchorWrap" @class(['d-none' => $curInterval <= 1])>
                <label class="form-label">Mes de referencia <small class="text-muted">(un mes en que sí llega)</small></label>
                <input type="month" name="anchor" id="anchorInput" class="form-control" value="{{ $curAnchor }}">
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary" data-bs-toggle="tooltip" title="Guardar el gasto fijo">
                <i class="bi bi-check2-circle"></i> Guardar
            </button>
            <a href="{{ route('fixed-expenses.index') }}" class="btn btn-light"
               data-bs-toggle="tooltip" title="Descartar cambios">
                <i class="bi bi-x-lg"></i> Cancelar
            </a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    (function () {
        const sel  = document.getElementById('intervalSelect');
        const wrap = document.getElementById('anchorWrap');
        if (!sel || !wrap) return;
        sel.addEventListener('change', function () {
            wrap.classList.toggle('d-none', parseInt(sel.value, 10) <= 1);
        });
    })();
</script>
@endpush
