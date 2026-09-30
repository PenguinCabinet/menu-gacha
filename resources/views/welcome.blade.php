<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>メニューガチャ | メニューを登録して、ガチャを回そう</title>
        @include('components.ogp', [
            'title' => 'メニューガチャ',
            'description' => 'メニューを登録して、ガチャを回そう',
            'url' => route('home'),
            'image' => route('og.site'),
        ])
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: #fbf8f2; color: #26332d; }
            .site-header { border-bottom: 1px solid #e9e5da; background: #fbf8f2; }
            .brand { color: #245b46; letter-spacing: .02em; }
            .brand:hover { color: #174331; }
            .hero { background: radial-gradient(circle at 85% 15%, #e7efda 0, transparent 40%), #f5f1e6; }
            .eyebrow { color: #39715a; letter-spacing: .12em; }
            .hero-title { font-weight: 800; letter-spacing: -.04em; line-height: 1.3; }
            .hero-accent { color: #287357; }
            .hero-plate { background: #fffdf8; border: 1px solid #e9e5da; border-radius: 2rem; box-shadow: 0 20px 55px #35463216; }
            .plate-icon { font-size: clamp(5rem, 12vw, 9rem); line-height: 1.2; }
            .feature-card { background: #fff; border: 1px solid #eae7df; border-radius: 1.25rem; height: 100%; }
            .feature-icon { display: inline-flex; align-items: center; justify-content: center; width: 3rem; height: 3rem; border-radius: .9rem; background: #e9f2e9; font-size: 1.5rem; }
            .btn-brand { background: #287357; border-color: #287357; color: #fff; }
            .btn-brand:hover, .btn-brand:focus-visible { background: #1b5841; border-color: #1b5841; color: #fff; }
        </style>
    </head>
    <body>
        <header class="site-header">
            <div class="container d-flex align-items-center justify-content-between gap-3 py-3">
                <a href="{{ route('home') }}" class="brand fw-bold fs-5 text-decoration-none">🍽️ メニューガチャ</a>
                <nav class="d-flex align-items-center gap-3 gap-sm-4" aria-label="アカウント">
                    @auth
                        <a href="{{ route('dashboard') }}" class="fw-semibold text-decoration-none brand">ダッシュボード</a>
                    @else
                        <a href="{{ route('register') }}" class="fw-semibold text-decoration-none brand">登録</a>
                        <a href="{{ route('login') }}" class="fw-semibold text-decoration-none brand">ログイン</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            <section class="hero py-5 py-lg-6">
                <div class="container py-4 py-lg-5">
                    <div class="row align-items-center g-5">
                        <div class="col-lg-7">
                            <p class="eyebrow small fw-bold mb-3">今日の「何食べよう？」を、もっと楽しく。</p>
                            <h1 class="hero-title display-4 mb-4">迷ったら、<br><span class="hero-accent">メニューガチャ</span>を回そう。</h1>
                            <p class="lead text-secondary mb-4">メニューガチャは、飲食店や学食のメニューを登録・公開できるサービスです。予算を決めたら、ガチャで今日の一品を選びます。</p>
                            <a href="{{ route('register') }}" class="btn btn-brand btn-lg rounded-pill px-4">登録する <span aria-hidden="true">→</span></a>
                        </div>
                        <div class="col-lg-5">
                            <div class="hero-plate text-center p-4 p-md-5" aria-hidden="true">
                                <div class="plate-icon mb-3">🍛</div>
                                <p class="eyebrow small fw-bold mb-2">TODAY'S PICK</p>
                                <p class="fs-4 fw-bold mb-0">今日のごはんは、何が出る？</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="container py-5" aria-labelledby="features-title">
                <div class="text-center mb-4 mb-md-5">
                    <p class="eyebrow small fw-bold mb-2">HOW IT WORKS</p>
                    <h2 id="features-title" class="fw-bold">メニューガチャでできること</h2>
                </div>
                <div class="row g-3 g-md-4">
                    <div class="col-sm-6 col-lg-3">
                        <div class="feature-card p-4">
                            <span class="feature-icon mb-3" aria-hidden="true">📝</span>
                            <h3 class="h5 fw-bold">メニューを登録</h3>
                            <p class="text-secondary mb-0">飲食店や学食のメニューを登録できます。</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="feature-card p-4">
                            <span class="feature-icon mb-3" aria-hidden="true">🎲</span>
                            <h3 class="h5 fw-bold">ガチャを公開</h3>
                            <p class="text-secondary mb-0">登録したメニューをガチャとして公開できます。</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="feature-card p-4">
                            <span class="feature-icon mb-3" aria-hidden="true">💰</span>
                            <h3 class="h5 fw-bold">予算内で選ぶ</h3>
                            <p class="text-secondary mb-0">予算に合う料理からランダムに選べます。</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="feature-card p-4">
                            <span class="feature-icon mb-3" aria-hidden="true">🏷️</span>
                            <h3 class="h5 fw-bold">フラグで絞る</h3>
                            <p class="text-secondary mb-0">「学割」などのフラグを含める・含めないを選べます。</p>
                        </div>
                    </div>
                </div>
                <p class="text-center text-secondary mt-4 mb-0">ユーザーが作ったメニューも、一覧で見ることができます。</p>
            </section>
        </main>
    </body>
</html>
