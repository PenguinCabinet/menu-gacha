<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $menuGacha->name }} | メニューガチャ</title>
        @include('components.ogp', [
            'title' => $menuGacha->name.' | メニューガチャ',
            'description' => '「'.$menuGacha->name.'」から今日のメニューをガチャで決めよう。',
            'url' => route('menu-gachas.show', ['id' => $menuGacha->getKey()]),
            'image' => route('og.menu', ['id' => $menuGacha->getKey()]),
            'type' => 'article',
        ])
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <main class="container py-4 py-lg-5" style="max-width: 960px;">
            @if ($isOwner)
                <a href="{{ route('dashboard') }}" class="btn btn-link px-0 mb-3">← ダッシュボードに戻る</a>
            @endif

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

            @php($isEditing = $isOwner && (request()->query('tab') === 'edit' || $errors->any()))

            <section class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 px-3 px-md-4 pt-3">
                    <p class="small fw-semibold text-uppercase text-secondary mb-2">Menu Gacha</p>
                    <h1 class="h2 fw-bold mb-3">{{ $menuGacha->name }}</h1>
                    <p class="text-secondary">
                        作成日：
                        <time datetime="{{ $menuGacha->created_at?->toIso8601String() }}">
                            {{ $menuGacha->created_at?->format('Y年n月j日') }}
                        </time>
                    </p>
                    <p class="text-secondary mb-4">
                        作成ユーザー：
                        <a href="{{ route('users.show', ['name' => $menuGacha->user->name]) }}">{{ $menuGacha->user->name }}</a>
                    </p>

                    <ul class="nav nav-tabs" id="menu-gacha-tabs" role="tablist" aria-label="メニューガチャ">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link {{ $isEditing ? '' : 'active' }} fw-semibold"
                                id="gacha-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#gacha-panel"
                                type="button"
                                role="tab"
                                aria-controls="gacha-panel"
                                aria-selected="{{ $isEditing ? 'false' : 'true' }}"
                                @if ($isEditing) tabindex="-1" @endif
                            >ガチャ</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link fw-semibold"
                                id="preview-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#preview-panel"
                                type="button"
                                role="tab"
                                aria-controls="preview-panel"
                                aria-selected="false"
                                tabindex="-1"
                            >メニュー</button>
                        </li>
                        @if ($isOwner)
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
                        @endif
                    </ul>
                </div>

                <div class="card-body p-4 p-md-5">
                    <div class="tab-content">
                        <section
                            class="tab-pane fade {{ $isEditing ? '' : 'show active' }}"
                            id="gacha-panel"
                            role="tabpanel"
                            aria-labelledby="gacha-tab"
                            tabindex="0"
                        >
                            <h1 class="h4 fw-bold mb-3">メニューガチャ</h1>
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

                            @if ($menuGacha->flags->isNotEmpty())
                                <fieldset class="mb-4" id="gacha-flags">
                                    <legend class="fs-6 fw-semibold mb-2">対象のフラグ</legend>
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach ($menuGacha->flags as $flag)
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="gacha-flag-{{ $flag->getKey() }}" value="{{ $flag->getKey() }}" checked>
                                                <label class="form-check-label" for="gacha-flag-{{ $flag->getKey() }}">{{ $flag->name }}を含む</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endif

                            <div id="gacha-result" aria-live="polite">
                            </div>
                            <p id="gacha-total" class="fw-bold mt-3 mb-0" aria-live="polite"></p>
                        </section>
                        <section
                            class="tab-pane fade"
                            id="preview-panel"
                            role="tabpanel"
                            aria-labelledby="preview-tab"
                            tabindex="0"
                        >

                            <h2 class="h5 fw-bold mb-3">食事</h2>
                            @forelse ($menuGacha->items as $item)
                                <div class="d-flex justify-content-between gap-3 border-bottom py-2">
                                    <div>
                                        <span>{{ $item->item_name }}</span>
                                        @if ($item->flags->isNotEmpty())
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                @foreach ($item->flags as $flag)
                                                    <span class="badge rounded-pill text-bg-secondary">{{ $flag->name }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <span>{{ number_format($item->price) }}円</span>
                                </div>
                            @empty
                                <p class="text-secondary mb-0">食事はまだ登録されていません。</p>
                            @endforelse
                        </section>

                        @if ($isOwner)
                        <section
                            class="tab-pane fade {{ $isEditing ? 'show active' : '' }}"
                            id="edit-panel"
                            role="tabpanel"
                            aria-labelledby="edit-tab"
                            tabindex="0"
                        >
                            <h1 class="h4 fw-bold mb-4">メニューガチャを編集</h1>
                            <p id="edit-sync-status" class="small text-secondary" role="status" aria-live="polite">同期に接続中…</p>
                            <div id="edit-save-status" class="position-fixed end-0 m-3 m-md-4 shadow-sm" style="bottom: 3rem; z-index: 1030;" role="status" aria-live="polite"></div>

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
                                <div class="mb-3">
                                    <label for="is-published" class="form-label">公開設定</label>
                                    <select
                                        id="is-published"
                                        name="is_published"
                                        form="menu-gacha-edit-form"
                                        class="form-select @error('is_published') is-invalid @enderror"
                                    >
                                        <option value="0" @selected(old('is_published', $menuGacha->is_published ? '1' : '0') == '0')>非公開</option>
                                        <option value="1" @selected(old('is_published', $menuGacha->is_published ? '1' : '0') == '1')>公開</option>
                                    </select>
                                    <div class="form-text">公開すると、ログインせずにこのメニューを閲覧できます。</div>
                                    @error('is_published')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <hr class="my-4">
                                <h2 class="h5 fw-bold mb-3">食事を編集</h2>

                            <div id="edit-items" class="d-flex flex-column gap-3 mb-4">
                                @forelse ($menuGacha->items as $item)
                                    <div id="edit-item-{{ $item->getKey() }}" class="row g-2 align-items-end border rounded p-3 bg-white">
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
                                                class="delete-item-form"
                                                onsubmit="return confirm('この食事を削除しますか？');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">削除</button>
                                            </form>
                                        </div>
                                        @if ($menuGacha->flags->isNotEmpty())
                                            <fieldset class="col-12 mt-3">
                                                <legend class="fs-6 mb-2">フラグ</legend>
                                                <div class="d-flex flex-wrap gap-3">
                                                    @foreach ($menuGacha->flags as $flag)
                                                        <div class="form-check">
                                                            <input
                                                                type="checkbox"
                                                                id="item-{{ $item->getKey() }}-flag-{{ $flag->getKey() }}"
                                                                name="items[{{ $item->getKey() }}][flag_ids][]"
                                                                form="menu-gacha-edit-form"
                                                                class="form-check-input"
                                                                value="{{ $flag->getKey() }}"
                                                                @checked(in_array($flag->getKey(), (array) (old('items.'.$item->getKey()) !== null ? old('items.'.$item->getKey().'.flag_ids', []) : $item->flags->modelKeys())))
                                                            >
                                                            <label class="form-check-label" for="item-{{ $item->getKey() }}-flag-{{ $flag->getKey() }}">{{ $flag->name }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </fieldset>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-secondary mb-0">登録済みの食事はありません。</p>
                                @endforelse
                                </div>

                            <form id="menu-gacha-edit-form" method="POST" action="{{ route('menu-gachas.update', ['id' => $menuGacha->getKey()]) }}">
                                @csrf
                                @method('PATCH')
                            </form>

                            <form id="add-item-form" method="POST" action="{{ route('menu-gachas.items.store', ['id' => $menuGacha->getKey()]) }}" class="border rounded p-3 bg-white mt-4">
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
                                    @if ($menuGacha->flags->isNotEmpty())
                                        <fieldset class="col-12 mt-3">
                                            <legend class="fs-6 mb-2">フラグ</legend>
                                            <div class="d-flex flex-wrap gap-3">
                                                @foreach ($menuGacha->flags as $flag)
                                                    <div class="form-check">
                                                        <input type="checkbox" id="new-item-flag-{{ $flag->getKey() }}" name="new_flag_ids[]" class="form-check-input" value="{{ $flag->getKey() }}" @checked(in_array($flag->getKey(), (array) old('new_flag_ids', [])))>
                                                        <label class="form-check-label" for="new-item-flag-{{ $flag->getKey() }}">{{ $flag->name }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </fieldset>
                                    @endif
                                </div>
                            </form>
                            <hr class="my-4">
                            <h2 class="h5 fw-bold mb-2">フラグを管理</h2>
                            <p class="text-secondary small">学割など、食事に付けるフラグを登録できます。</p>

                            <div id="edit-flags" class="d-flex flex-column gap-3 mb-4">
                                @forelse ($menuGacha->flags as $flag)
                                    <div id="edit-flag-{{ $flag->getKey() }}" class="border rounded p-3 bg-white">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-sm">
                                                <label for="flag-name-{{ $flag->getKey() }}" class="form-label">フラグ名</label>
                                                <input type="text" id="flag-name-{{ $flag->getKey() }}" name="flags[{{ $flag->getKey() }}][name]" form="menu-gacha-edit-form" class="form-control @error('flags.'.$flag->getKey().'.name') is-invalid @enderror" value="{{ old('flags.'.$flag->getKey().'.name', $flag->name) }}" maxlength="255" required>
                                                @error('flags.'.$flag->getKey().'.name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('menu-gachas.flags.destroy', ['id' => $menuGacha->getKey(), 'flagId' => $flag->getKey()]) }}" class="mt-2 delete-flag-form" onsubmit="return confirm('このフラグを削除しますか？');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">削除</button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="text-secondary mb-0">登録済みのフラグはありません。</p>
                                @endforelse
                            </div>

                            <form id="add-flag-form" method="POST" action="{{ route('menu-gachas.flags.store', ['id' => $menuGacha->getKey()]) }}" class="border rounded p-3 bg-white">
                                @csrf
                                <h3 class="h6 fw-bold mb-3">フラグを追加</h3>
                                <div class="row g-2 align-items-end">
                                    <div class="col-sm">
                                        <label for="new-flag-name" class="form-label">フラグ名</label>
                                        <input type="text" id="new-flag-name" name="new_flag_name" class="form-control @error('new_flag_name') is-invalid @enderror" value="{{ old('new_flag_name') }}" maxlength="255" placeholder="例：学割" required>
                                        @error('new_flag_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-sm-auto d-grid">
                                        <button type="submit" class="btn btn-success">追加</button>
                                    </div>
                                </div>
                            </form>
                            <button type="submit" form="menu-gacha-edit-form" class="btn btn-primary position-fixed bottom-0 end-0 m-3 m-md-4 shadow" style="z-index: 1030;">保存</button>
                        </section>
                        @endif
                    </div>
                </div>
            </section>
        </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script type="application/json" id="gacha-items">@json($menuGacha->items->map(fn ($item) => ['name' => $item->item_name, 'price' => $item->price, 'flagIds' => $item->flags->modelKeys()])->values(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
        <script type="module" src="{{ asset('js/menu-gacha.js') }}"></script>
        @if ($isOwner)
            @vite('resources/js/menu-gacha-sync.js')
        @endif
    </body>
</html>
