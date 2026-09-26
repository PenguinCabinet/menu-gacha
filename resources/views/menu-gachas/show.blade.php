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

            @if (session('message'))
                <div class="alert alert-success" role="status">
                    {{ session('message') }}
                </div>
            @endif

            @php($isEditing = $errors->has('name'))

            <section class="card border-0 shadow-sm">
                <section
                    id="preview-panel"
                    role="tabpanel"
                    aria-labelledby="preview-tab"
                    tabindex="0"
                >
                    <p class="small fw-semibold text-uppercase text-secondary mb-2">Menu Gacha</p>
                    <h1 class="h2 fw-bold mb-3">{{ $menuGacha->name }}</h1>
                    <p class="text-secondary mb-0">
                        作成日時：
                        <time datetime="{{ $menuGacha->created_at?->toIso8601String() }}">
                            {{ $menuGacha->created_at?->format('Y年n月j日 H:i') }}
                        </time>
                    </p>
                </section>
                <div class="card-header bg-white border-bottom-0 px-3 px-md-4 pt-3">
                    <ul class="nav nav-tabs" id="menu-gacha-tabs" role="tablist" aria-label="メニューガチャ">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link {{ $isEditing ? '' : 'active' }} fw-semibold"
                                id="preview-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#preview-panel"
                                type="button"
                                role="tab"
                                aria-controls="preview-panel"
                                aria-selected="{{ $isEditing ? 'false' : 'true' }}"
                            >
                                プレビュー
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link {{ $isEditing ? 'active' : '' }} fw-semibold"
                                id="edit-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#edit-panel"
                                type="button"
                                role="tab"
                                aria-controls="edit-panel"
                                aria-selected="{{ $isEditing ? 'true' : 'false' }}"
                            >
                                編集
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4 p-md-5">
                    <div class="tab-content">

                        <section
                            class="tab-pane fade {{ $isEditing ? 'show active' : '' }}"
                            id="edit-panel"
                            role="tabpanel"
                            aria-labelledby="edit-tab"
                            tabindex="0"
                        >
                            <h1 class="h4 fw-bold mb-4">メニューガチャを編集</h1>

                            <form method="POST" action="{{ route('menu-gachas.update', ['id' => $menuGacha->getKey()]) }}">
                                @csrf
                                @method('PATCH')

                                <div class="mb-3">
                                    <label for="name" class="form-label">メニューガチャ名</label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $menuGacha->name) }}"
                                        maxlength="255"
                                        required
                                    >
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary">変更を保存</button>
                            </form>
                        </section>
                    </div>
                </div>
            </section>
        </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
