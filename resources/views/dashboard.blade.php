<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>メニューガチャ | Dashboard</title>
        @include('components.ogp', [
            'title' => 'ダッシュボード | メニューガチャ',
            'description' => 'メニューガチャを作成・管理できます。',
            'url' => route('dashboard'),
            'image' => route('og.site'),
        ])
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <main class="container py-4 py-lg-5" style="max-width: 960px;">
            <header class="mb-4 d-flex align-items-center justify-content-between gap-3">
                <h1 class="mb-0">Dashboard</h1>
                <a
                    href="{{ route('users.show', ['name' => auth()->user()->name]) }}"
                    class=""
                >
                    {{ auth()->user()->name }}のユーザーページ
                </a>
            </header>

            @if (session('message'))
                <div class="alert alert-success" role="status">
                    {{ session('message') }}
                </div>
            @endif

            <section class="card border-0 shadow-sm" aria-label="ダッシュボード">
                <div class="card-header bg-white border-bottom-0 px-3 px-md-4 pt-3">
                    <ul class="nav nav-tabs" id="dashboard-tabs" role="tablist" aria-label="ダッシュボードのタブ">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link active fw-semibold"
                                id="menu-gacha-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#menu-gacha-panel"
                                type="button"
                                role="tab"
                                aria-controls="menu-gacha-panel"
                                aria-selected="true"
                            >
                                メニューガチャ
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link fw-semibold"
                                id="timeline-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#timeline-panel"
                                type="button"
                                role="tab"
                                aria-controls="timeline-panel"
                                aria-selected="false"
                                tabindex="-1"
                            >
                                タイムライン
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4 p-md-5">
                    <div class="tab-content">
                        <section
                            class="tab-pane fade show active position-relative"
                            id="menu-gacha-panel"
                            role="tabpanel"
                            aria-labelledby="menu-gacha-tab"
                            tabindex="0"
                            style="min-height: 320px; padding-bottom: 4.5rem;"
                        >
                            @if ($menuGachas->isEmpty())
                                <div class="text-center py-5">
                                    <div class="display-4 mb-3" aria-hidden="true">🍽️</div>
                                    <h2 class="h4 fw-bold mb-2">メニューガチャ</h2>
                                    <p class="text-secondary mb-0">メニューガチャを作成してみましょう。</p>
                                </div>
                            @else
                                <div class="row row-cols-1 row-cols-md-2 g-3">
                                    @foreach ($menuGachas as $menuGacha)
                                        <div class="col">
                                            <a
                                                href="{{ route('menu-gachas.show', ['id' => $menuGacha->getKey()]) }}"
                                                class="card h-100 text-decoration-none text-reset shadow-sm"
                                            >
                                                <div class="card-body">
                                                    <h3 class="h5 card-title fw-bold">{{ $menuGacha->name }}</h3>
                                                    <p class="card-text text-secondary small mb-0">
                                                        作成日: {{ $menuGacha->created_at->format('Y年n月j日') }}
                                                    </p>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <form method="POST" action="{{ route('menu-gachas.store') }}" class="position-absolute bottom-0 end-0">
                                @csrf
                                <button
                                    class="btn btn-primary rounded-circle d-inline-flex align-items-center justify-content-center shadow"
                                    type="submit"
                                    aria-label="作成"
                                    title="作成"
                                    style="width: 3.5rem; height: 3.5rem; font-size: 1.75rem;"
                                >
                                    <span aria-hidden="true">+</span>
                                </button>
                            </form>
                        </section>

                        <section
                            class="tab-pane fade"
                            id="timeline-panel"
                            role="tabpanel"
                            aria-labelledby="timeline-tab"
                            tabindex="0"
                        >
                            <div class="text-center py-5">
                                <div class="display-4 mb-3" aria-hidden="true">🕒</div>
                                <h2 class="h4 fw-bold mb-2">タイムライン</h2>
                                <p class="text-secondary mb-0">他ユーザーが作成したメニューガチャを一覧表にできます</p>
                            </div>
                        </section>
                    </div>
                </div>
            </section>
        </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
