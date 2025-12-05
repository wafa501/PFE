<!-- resources/views/organization.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkedIn Organization Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f0f0f0;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .info-item {
            margin-bottom: 15px;
        }
        .info-item p {
            font-size: 18px;
            margin: 5px 0;
        }
        .info-item strong {
            display: block;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>LinkedIn Organization Data</h1>

        <div class="info-item">
            <strong>Nom Localisé:</strong>
            <p>{{ $pageData['localizedName'] ?? 'N/A' }}</p>
        </div>

        <div class="info-item">
            <strong>Nom Vanity:</strong>
            <p>{{ $pageData['vanityName'] ?? 'N/A' }}</p>
        </div>

        <div class="info-item">
            <strong>Type d'Organisation:</strong>
            <p>{{ $pageData['organizationType'] ?? 'N/A' }}</p>
        </div>

        <div class="info-item">
            <strong>Type Principal d'Organisation:</strong>
            <p>{{ $pageData['primaryOrganizationType'] ?? 'N/A' }}</p>
        </div>

        <div class="info-item">
            <strong>Taille:</strong>
            <p>{{ $pageData['staffCountRange'] ?? 'N/A' }}</p>
        </div>

        <div class="info-item">
            <strong>Version Tag:</strong>
            <p>{{ $pageData['versionTag'] ?? 'N/A' }}</p>
        </div>

        <div class="info-item">
            <strong>Industries:</strong>
            <p>{{ implode(', ', $pageData['industries'] ?? []) }}</p>
        </div>

        <div class="info-item">
            <strong>Specialités:</strong>
            <p>{{ implode(', ', $pageData['specialties'] ?? []) }}</p>
        </div>

        <div class="info-item">
            <strong>Specialités Localisées:</strong>
            <p>{{ implode(', ', $pageData['localizedSpecialties'] ?? []) }}</p>
        </div>

        <div class="info-item">
            <strong>ID LinkedIn:</strong>
            <p>{{ $pageData['linkedin_id'] ?? 'N/A' }}</p>
        </div>

        <div class="info-item">
            <strong>ID:</strong>
            <p>{{ $pageData['id'] ?? 'N/A' }}</p>
        </div>

        <div class="info-item">
            <strong>URN:</strong>
            <p>{{ $pageData['$URN'] ?? 'N/A' }}</p>
        </div>

        <div class="info-item">
            <strong>Localisation:</strong>
            <p>{{ $pageData['defaultLocale']['country'] ?? 'N/A' }} - {{ $pageData['defaultLocale']['language'] ?? 'N/A' }}</p>
        </div>

        <div class="info-item">
            <strong>Groupes:</strong>
            <p>{{ implode(', ', $pageData['groups'] ?? []) }}</p>
        </div>

        <div class="info-item">
            <strong>Locations:</strong>
            <p>{{ implode(', ', $pageData['locations'] ?? []) }}</p>
        </div>

        <div class="info-item">
            <strong>Alternatives:</strong>
            <p>{{ implode(', ', $pageData['alternativeNames'] ?? []) }}</p>
        </div>
    </div>
</body>
</html>
