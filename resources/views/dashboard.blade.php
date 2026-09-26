<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <h1 class="display-6 mb-3">dashboard</h1>
                    @if (session('message'))
                        <div class="alert alert-success d-inline-block">
                            {{ session('message') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </body>
</html>
