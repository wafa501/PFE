@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Gestion des Organisations LinkedIn</h2>
    
    <!-- Messages de statut -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Formulaire d'ajout -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Ajouter une nouvelle organisation</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('organizations.store') }}" method="POST">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="vanity_name" class="form-label">Vanity Name</label>
                        <input type="text" name="vanity_name" class="form-control @error('vanity_name') is-invalid @enderror" 
                               value="{{ old('vanity_name') }}" placeholder="ex: microsoft" required>
                        @error('vanity_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="linkedin_id" class="form-label">ID LinkedIn</label>
                        <input type="text" name="linkedin_id" class="form-control @error('linkedin_id') is-invalid @enderror" 
                               value="{{ old('linkedin_id') }}" placeholder="ex: 123456" required>
                        @error('linkedin_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="name" class="form-label">Nom affiché</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" placeholder="ex: Microsoft" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-success">Ajouter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des organisations -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Organisations suivies ({{ $organizations->count() }})</h5>
            @if($organizations->count() > 0)
                <button onclick="syncAll()" class="btn btn-outline-primary btn-sm">
                    Synchroniser toutes
                </button>
            @endif
        </div>
        <div class="card-body">
            @if($organizations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Nom</th>
                                <th>Vanity Name</th>
                                <th>ID LinkedIn</th>
                                <th>Status</th>
                                <th>Dernière synchro</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($organizations as $org)
                            <tr>
                                <td>
                                    <strong>{{ $org->name }}</strong>
                                    @if($org->is_active)
                                        <span class="badge bg-success ms-1">Active</span>
                                    @endif
                                </td>
                                <td>
                                    <code>{{ $org->vanity_name }}</code>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $org->linkedin_id }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $org->is_active ? 'success' : 'danger' }}">
                                        {{ $org->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    @if($org->last_synced_at)
                                        <span title="{{ $org->last_synced_at->format('d/m/Y H:i') }}">
                                            {{ $org->last_synced_at->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-muted">Jamais</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button onclick="toggleStatus({{ $org->id }})" 
                                                class="btn btn-{{ $org->is_active ? 'warning' : 'success' }}">
                                            {{ $org->is_active ? 'Désactiver' : 'Activer' }}
                                        </button>
                                        <button onclick="syncNow({{ $org->id }})" 
                                                class="btn btn-info" {{ !$org->is_active ? 'disabled' : '' }}>
                                            Synchro
                                        </button>
                                        <button onclick="deleteOrg({{ $org->id }})" 
                                                class="btn btn-danger">Supprimer</button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <p class="text-muted">Aucune organisation configurée pour le moment.</p>
                    <p>Ajoutez votre première organisation ci-dessus.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
function toggleStatus(id) {
    if (confirm('Changer le statut de cette organisation?')) {
        fetch(`/organizations/toggle/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du changement de statut');
        });
    }
}

function syncNow(id) {
    if (confirm('Forcer la synchronisation maintenant?')) {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Sync...';
        btn.disabled = true;

        fetch(`/organizations/sync/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la synchronisation');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
}

function syncAll() {
    if (confirm('Synchroniser toutes les organisations actives?')) {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Sync toutes...';
        btn.disabled = true;

        fetch('/organizations/sync-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la synchronisation');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
}

function deleteOrg(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer définitivement cette organisation?')) {
        fetch(`/organizations/delete/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la suppression');
        });
    }
}

</script>
@endsection