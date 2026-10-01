<div>
    @forelse ($menuGachas as $menuGacha)
        @if ($loop->first)
            <div class="row row-cols-1 row-cols-md-2 g-3">
        @endif
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h3 class="h5 card-title fw-bold">
                                <a
                                    href="{{ route('menu-gachas.show', ['id' => $menuGacha->getKey()]) }}"
                                    class="text-decoration-none text-reset"
                                >{{ $menuGacha->name }}</a>
                            </h3>
                            <p class="card-text text-secondary small mb-0">
                                @if ($showUser && $menuGacha->relationLoaded('user') && $menuGacha->user !== null)
                                    <a
                                        href="{{ route('users.show', ['name' => $menuGacha->user->name]) }}"
                                        class="link-secondary"
                                    >{{ $menuGacha->user->name }}</a>
                                    <span aria-hidden="true">・</span>
                                @endif
                                作成日: {{ $menuGacha->created_at?->format('Y年n月j日') }}
                            </p>
                        </div>
                    </div>
                </div>
        @if ($loop->last)
            </div>
        @endif
    @empty
        <div class="text-center py-5">
            <div class="display-4 mb-3" aria-hidden="true">🕒</div>
            <h2 class="h4 fw-bold mb-2">タイムライン</h2>
            <p class="text-secondary mb-0">{{ $emptyMessage }}</p>
        </div>
    @endforelse
</div>
