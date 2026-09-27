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
                <div class="alert alert-success" role="status">{{ session('message') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php($isEditing = request()->query('tab') === 'edit' || $errors->any())

            <section class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 px-3 px-md-4 pt-3">
                    <ul class="nav nav-tabs" id="menu-gacha-tabs" role="tablist" aria-label="メニューガチャ">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link fw-semibold"
                                id="gacha-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#gacha-panel"
                                type="button"
                                role="tab"
                                aria-controls="gacha-panel"
                                aria-selected="false"
                                tabindex="-1"
                            >ガチャ</button>
                        </li>
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
                            >プレビュー</button>
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
                            >編集</button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4 p-md-5">
                    <div class="tab-content">
                        <section
                            class="tab-pane fade"
                            id="gacha-panel"
                            role="tabpanel"
                            aria-labelledby="gacha-tab"
                            tabindex="0"
                        >
                            <h1 class="h4 fw-bold mb-3">メニューガチャ</h1>
                            <p class="text-secondary">予算内で食事をランダムに選びます。同じ食事が複数回選ばれることがあります。</p>

                            <div class="row g-3 align-items-end mb-4">
                                <div class="col-sm-6 col-md-4">
                                    <label for="gacha-budget" class="form-label">予算（円）</label>
                                    <input
                                        type="number"
                                        id="gacha-budget"
                                        class="form-control"
                                        value="1000"
                                        min="0"
                                        step="1"
                                    >
                                </div>
                                <div class="col-sm-auto d-grid">
                                    <button type="button" id="run-gacha" class="btn btn-primary">ガチャを回す</button>
                                </div>
                            </div>

                            <div id="gacha-result" aria-live="polite">
                                <p class="text-secondary mb-0">ボタンを押してメニューを選びましょう。</p>
                            </div>
                            <p id="gacha-total" class="fw-bold mt-3 mb-0" aria-live="polite"></p>
                        </section>
                        <section
                            class="tab-pane fade {{ $isEditing ? '' : 'show active' }}"
                            id="preview-panel"
                            role="tabpanel"
                            aria-labelledby="preview-tab"
                            tabindex="0"
                        >
                            <p class="small fw-semibold text-uppercase text-secondary mb-2">Menu Gacha</p>
                            <h1 class="h2 fw-bold mb-3">{{ $menuGacha->name }}</h1>
                            <p class="text-secondary mb-4">
                                作成日時：
                                <time datetime="{{ $menuGacha->created_at?->toIso8601String() }}">
                                    {{ $menuGacha->created_at?->format('Y年n月j日 H:i') }}
                                </time>
                            </p>

                            <h2 class="h5 fw-bold mb-3">食事</h2>
                            @forelse ($menuGacha->items as $item)
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span>{{ $item->item_name }}</span>
                                    <span>{{ number_format($item->price) }}円</span>
                                </div>
                            @empty
                                <p class="text-secondary mb-0">食事はまだ登録されていません。</p>
                            @endforelse
                        </section>

                        <section
                            class="tab-pane fade {{ $isEditing ? 'show active' : '' }}"
                            id="edit-panel"
                            role="tabpanel"
                            aria-labelledby="edit-tab"
                            tabindex="0"
                        >
                            <h1 class="h4 fw-bold mb-4">メニューガチャを編集</h1>

                            <div class="mb-3">
                                    <label for="name" class="form-label">メニューガチャ名</label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        form="menu-gacha-edit-form"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $menuGacha->name) }}"
                                        maxlength="255"
                                        required
                                    >
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <hr class="my-4">
                                <h2 class="h5 fw-bold mb-3">食事を編集</h2>

                            <div class="d-flex flex-column gap-3 mb-4">
                                @forelse ($menuGacha->items as $item)
                                    <div class="row g-2 align-items-end border rounded p-3 bg-white">
                                        <div class="col-md-7">
                                            <label for="item-name-{{ $item->getKey() }}" class="form-label">項目名</label>
                                            <input
                                                type="text"
                                                id="item-name-{{ $item->getKey() }}"
                                                name="items[{{ $item->getKey() }}][item_name]"
                                                form="menu-gacha-edit-form"
                                                class="form-control"
                                                value="{{ old('items.'.$item->getKey().'.item_name', $item->item_name) }}"
                                                maxlength="255"
                                                required
                                            >
                                        </div>
                                        <div class="col-md-3">
                                            <label for="item-price-{{ $item->getKey() }}" class="form-label">価格（円）</label>
                                            <input
                                                type="number"
                                                id="item-price-{{ $item->getKey() }}"
                                                name="items[{{ $item->getKey() }}][price]"
                                                form="menu-gacha-edit-form"
                                                class="form-control"
                                                value="{{ old('items.'.$item->getKey().'.price', $item->price) }}"
                                                min="0"
                                                step="1"
                                                required
                                            >
                                        </div>
                                        <div class="col-md-2 d-grid">
                                            <form
                                                method="POST"
                                                action="{{ route('menu-gachas.items.destroy', ['id' => $menuGacha->getKey(), 'itemId' => $item->getKey()]) }}"
                                                onsubmit="return confirm('この食事を削除しますか？');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">削除</button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-secondary mb-0">登録済みの食事はありません。</p>
                                @endforelse
                                </div>

                            <form id="menu-gacha-edit-form" method="POST" action="{{ route('menu-gachas.update', ['id' => $menuGacha->getKey()]) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-primary">変更を保存</button>
                            </form>

                            <form method="POST" action="{{ route('menu-gachas.items.store', ['id' => $menuGacha->getKey()]) }}" class="border rounded p-3 bg-white mt-4">
                                @csrf
                                <h3 class="h6 fw-bold mb-3">食事を追加</h3>
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-7">
                                        <label for="new-item-name" class="form-label">項目名</label>
                                        <input
                                            type="text"
                                            id="new-item-name"
                                            name="new_item_name"
                                            class="form-control @error('new_item_name') is-invalid @enderror"
                                            value="{{ old('new_item_name') }}"
                                            maxlength="255"
                                            required
                                        >
                                        @error('new_item_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label for="new-price" class="form-label">価格（円）</label>
                                        <input
                                            type="number"
                                            id="new-price"
                                            name="new_price"
                                            class="form-control @error('new_price') is-invalid @enderror"
                                            value="{{ old('new_price') }}"
                                            min="0"
                                            step="1"
                                            required
                                        >
                                        @error('new_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-2 d-grid">
                                        <button type="submit" class="btn btn-success">追加</button>
                                    </div>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>
            </section>
        </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script type="application/json" id="gacha-items">@json($menuGacha->items->map(fn ($item) => ['name' => $item->item_name, 'price' => $item->price])->values(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
        <script type="module" src="{{ asset('js/menu-gacha.js') }}"></script>
    </body>
</html>
