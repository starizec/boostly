@extends('layout.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-1">Svi widgeti</h4>
            <p class="text-muted mb-0">Upravljajte svojim chat widgetima</p>
        </div>
        <a href="{{ route('widgets.create') }}" class="btn btn-primary">
            <i data-lucide="plus" class="icon-sm me-1"></i>
            Kreiraj widget
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="pt-0">#</th>
                                    <th class="pt-0">Naziv</th>
                                    <th class="pt-0">Akcija</th>
                                    <th class="pt-0">Medij</th>
                                    <th class="pt-0">Status</th>
                                    <th class="pt-0 text-end">Akcije</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($widgets as $widget)
                                    <tr>
                                        <td>{{ $widget->id }}</td>
                                        <td class="fw-medium">{{ $widget->name }}</td>
                                        <td>{{ $widget->widgetAction->name ?? '—' }}</td>
                                        <td>{{ $widget->media->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge {{ $widget->active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $widget->active ? 'Aktivan' : 'Neaktivan' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('widgets.edit', $widget) }}"
                                                class="btn btn-sm btn-outline-primary me-1">Uredi</a>
                                            <form action="{{ route('widgets.destroy', $widget) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Jeste li sigurni da želite obrisati ovaj widget?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Obriši</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            Nemate još nijedan widget.
                                            <a href="{{ route('widgets.create') }}">Kreirajte prvi widget</a>.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
