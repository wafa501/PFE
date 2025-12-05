<!DOCTYPE html>
<html lang="en">
<head>
<meta name="csrf-token" content="{{ csrf_token() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .error-container {
            text-align: center;
            margin-top: 50px;
        }
        .error-code {
            font-size: 100px;
            color: #dc3545; /* Couleur rouge pour l'erreur */
        }
        .error-message {
            font-size: 24px;
            color: #343a40; /* Couleur sombre */
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">Oops!</h1>
        <h2 class="error-message">Une erreur est survenue.</h2>
        <p>Nous sommes désolés, mais quelque chose n'a pas fonctionné comme prévu.</p>
        
        <p>Erreur: {{ session('error_message', 'Erreur inconnue') }}</p>

        <a href="{{ url('/') }}" class="btn btn-primary mt-3">Retour à la page d'accueil</a>
        <a href="javascript:history.back()" class="btn btn-secondary mt-3">Retourner à la page précédente</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
