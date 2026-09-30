<?php

namespace App\Http\Controllers;

use App\Models\MenuGacha;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OgImageController extends Controller
{
    public function site(): Response
    {
        return $this->image('メニューガチャ', '今日の食事を、楽しく決めよう。', 'メニュー選びをもっと楽しく');
    }

    public function menu(Request $request, int $id): Response
    {
        $menuGacha = MenuGacha::with('user')->findOrFail($id);
        $isOwner = $request->user()?->getKey() === $menuGacha->user_id;
        abort_unless($isOwner || $menuGacha->is_published, 404);

        return $this->image($menuGacha->name, '今日のメニューをガチャで決めよう。', 'MENU GACHA · '.$menuGacha->user->name);
    }

    public function user(string $name): Response
    {
        $user = User::where('name', $name)->firstOrFail();

        return $this->image($user->name, '公開中のメニューガチャをチェック。', 'CREATED BY');
    }

    private function image(string $title, string $description, string $eyebrow): Response
    {
        return response()->view('og.image', [
            'title' => $title,
            'description' => $description,
            'eyebrow' => $eyebrow,
        ], 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
