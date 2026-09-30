<?php

namespace App\Http\Controllers;

use App\Models\MenuGacha;
use App\Models\User;
use App\Support\OgImageRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OgImageController extends Controller
{
    public function site(): Response
    {
        return $this->image('メニューガチャ', 'ガチャを回してメニューを決めよう', 'メニュー選びをもっと楽しく');
    }

    public function menu(Request $request, int $id): Response
    {
        $menuGacha = MenuGacha::with('user')->findOrFail($id);
        $isOwner = $request->user()?->getKey() === $menuGacha->user_id;
        abort_unless($isOwner || $menuGacha->is_published, 404);

        return $this->image($menuGacha->name.' by '.$menuGacha->user->name, '今日のメニューをガチャで決めよう', 'MENU GACHA');
    }

    public function user(string $name): Response
    {
        $user = User::where('name', $name)->firstOrFail();

        return $this->image($user->name, $user->name.'さんの公開中のメニューガチャをチェック', 'CREATED BY');
    }

    private function image(string $title, string $description, string $eyebrow): Response
    {
        $png = app(OgImageRenderer::class)->render($title, $description, $eyebrow);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
