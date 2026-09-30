<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ログイン</title>
        @include('components.ogp', [
            'title' => 'ログイン | メニューガチャ',
            'description' => 'メニューガチャにログインして、あなたのメニューを管理しましょう。',
            'url' => route('login'),
            'image' => route('og.site'),
        ])
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h1 class="h4 text-center mb-4">ログイン</h1>

                            @if (session('message'))
                                <div class="alert alert-info mb-3">
                                    {{ session('message') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login.submit') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="email" class="form-label">メールアドレス</label>
                                    <input type="email" id="email" name="email" class="form-control" placeholder="name@example.com" value="{{ old('email') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">パスワード</label>
                                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••">
                                </div>

                                <button type="submit" class="btn btn-primary w-100">ログイン</button>
                            </form>

                            <div class="mt-3 text-center">
                                <a href="{{ route('register') }}">ユーザー登録はこちら</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
