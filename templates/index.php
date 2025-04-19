<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orcamentos API</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        a {
            color: #3498db;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .btn {
            display: inline-block;
            background: #3498db;
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .btn:hover {
            background: #2980b9;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Orcamentos API</h1>
        <p>Welcome to the Orcamentos API. This is a RESTful API for managing budget estimates.</p>
        <p>Version: <?= $version ?></p>
        <p>
            <a href="/docs" class="btn">API Documentation</a>
        </p>
    </div>
</body>
</html>
