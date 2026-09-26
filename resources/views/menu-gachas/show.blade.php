<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $menuGacha->name }} | メニューガチャ</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <main class="container py-4 py-lg-5" style="max-width: 960px;">
            <a href="{{ route('dashboard') }}" class="btn btn-link px-0 mb-3">← ダッシュボードに戻る</a>

            <section class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <p class="small fw-semibold text-uppercase text-secondary mb-2">Menu Gacha</p>
                    <h1 class="h2 fw-bold mb-3">{{ $menuGacha->name }}</h1>
                    <p class="text-secondary mb-0">
                        作成日時：
                        <time datetime="{{ $menuGacha->created_at?->toIso8601String() }}">
                            {{ $menuGacha->created_at?->format('Y年n月j日 H:i') }}
                        </time>
                    </p>
                </div>
            </section>
        </main>
    </body>
</html>
