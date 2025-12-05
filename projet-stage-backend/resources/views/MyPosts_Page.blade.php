<!DOCTYPE html>
<html lang="fr">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile et Publications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .profile-container {
            max-width: 600px;
            margin: auto;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-info {
            margin-top: 10px;
        }
        .profile-info p {
            margin: 5px 0;
        }
        .table-responsive {
            max-height: 400px; /* Limite de hauteur */
            max-width: 100%; /* Limite de largeur */
            overflow-x: auto; /* Défilement horizontal */
            overflow-y: auto; /* Défilement vertical */
        }
        .media-container img, .media-container video {
            max-width: 100%; /* Responsive media */
            height: auto;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    
    <div class="container my-4">
        <h2 class="mb-4">Liste des Publications</h2>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Auteur</th>
                        <th>Date de création</th>
                        <th>Date de publication</th>
                        <th>Dernière modification</th>
                        <th>État du cycle de vie</th>
                        <th>Visibilité</th>
                        <th>Contenu</th>
                        <th>Commentaire</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($allPosts as $post)
                        <tr>
                            <td>{{ $post['id'] ?? 'N/A' }}</td>
                            <td>{{ isset($post['createdAt']) ? \Carbon\Carbon::parse($post['createdAt'])->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>{{ isset($post['publishedAt']) ? \Carbon\Carbon::parse($post['publishedAt'])->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>{{ isset($post['lastModifiedAt']) ? \Carbon\Carbon::parse($post['lastModifiedAt'])->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>{{ $post['lifecycleState'] ?? 'Unknown' }}</td>
                            <td>{{ $post['visibility'] ?? 'Unknown' }}</td>
                            <td>{{ !empty($post['content']) ? json_encode($post['content']) : 'N/A' }}</td> 
                            <td>{{ $post['commentary'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h2><a href="{{ route('linkedin.stats', ['idOrg' => '103732173']) }}">statistiques</a></h2>

        <!-- Pagination -->
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                @if ($previousPage !== null)
                    <li class="page-item">
                        <a class="page-link" href="{{ url()->current() }}?start={{ $previousPage }}">Précédente</a>
                    </li>
                @endif
                @if ($nextPage !== null)
                    <li class="page-item">
                        <a class="page-link" href="{{ url()->current() }}?start={{ $nextPage }}">Suivante</a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>

</body>
</html>
