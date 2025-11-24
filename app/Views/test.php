<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MVC Test</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2c2c2c;
            margin-top: 0;
        }

        .success {
            color: #28a745;
            font-size: 1.2em;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #e8e8e8;
        }

        td:first-child {
            font-weight: bold;
            width: 200px;
        }

        .code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>✅ MatraC MVC Framework</h1>

        <p class="success"><?= h($message) ?></p>

        <table>
            <tr>
                <td>PHP Version</td>
                <td><span class="code"><?= h($php_version) ?></span></td>
            </tr>
            <tr>
                <td>Server</td>
                <td><span class="code"><?= h($server) ?></span></td>
            </tr>
            <tr>
                <td>Base URL</td>
                <td><span class="code"><?= h($base_url) ?></span></td>
            </tr>
            <tr>
                <td>Asset URL (example)</td>
                <td><span class="code"><?= h($asset_url) ?></span></td>
            </tr>
            <tr>
                <td>Root Path</td>
                <td><span class="code"><?= h(ROOT_PATH) ?></span></td>
            </tr>
        </table>

        <h2 style="margin-top: 30px;">Next Steps:</h2>
        <ol>
            <li>Verify all information above is correct</li>
            <li>Test database connection</li>
            <li>Create first real controller (Auth)</li>
            <li>Migrate existing pages</li>
        </ol>
    </div>
</body>

</html>