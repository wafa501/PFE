<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
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
        .profile-picture {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        .profile-info {
            margin-top: 10px;
        }
        .profile-info p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h1>Profile</h1>
        <div class="profile-info">
            <p><strong>Name:</strong> {{ $profileData['name'] }}</p>
            <p><strong>Given Name:</strong> {{ $profileData['given_name'] }}</p>
            <p><strong>Family Name:</strong> {{ $profileData['family_name'] }}</p>
            <p><strong>Email:</strong> {{ $profileData['email'] }}</p>
            <p><strong>Locale:</strong> {{ $profileData['locale']['country'] }} - {{ $profileData['locale']['language'] }}</p>
            <p><strong>Profile Picture:</strong></p>
            <img src="{{ $profileData['picture'] }}" alt="Profile Picture" class="profile-picture">
            <p><a href="{{ route('linkedin.pageOrg') }}">Organization Page</a></p>

        </div>
    </div>
</body>
</html>
