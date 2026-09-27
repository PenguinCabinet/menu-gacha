<?php

namespace Database\Seeders;

use App\Models\MenuGachaFlag;
use App\Models\User;
use Illuminate\Database\Seeder;

class MenuGachaFlagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menuGacha = User::factory()->create()->menuGachas()->create(['name' => 'サンプルメニュー']);

        MenuGachaFlag::factory()->for($menuGacha)->create(['name' => '学割']);
    }
}
