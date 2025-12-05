<!-- resources/views/success.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success</title>
</head>
<body>
    <h1>Authentication Successful!</h1>
    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif
</body>
</html>
