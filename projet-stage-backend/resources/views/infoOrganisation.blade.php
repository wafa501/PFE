<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            min-height: 100vh;
            background-color: #f0f0f0;
        }
        .circle {
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
            margin: 20px;
        }
        .circle h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .circle a {
            font-size: 18px;
            margin: 5px 0;
            text-decoration: none;
            color: #007bff;
        }
        .circle a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="circle">
        <div>
        <h2><a href="{{ route('linkedin.page', ['pageId' => '103732173']) }}">Info Générale de notre organisation</a></h2>
        </div>
    </div>
    <div class="circle">
        <div>
        <h2><a href="{{ route('linkedin.mypost', ['id' => '103732173']) }}">Info Détailées </a></h2>
        </div>
    </div>
    <div class="circle">
        <div>
            <h2><a href="{{ route('linkedin.pageAutreOrg') }}">Autres organisations</a></h2>

            
        </div>
    </div>
</body>
</html>
