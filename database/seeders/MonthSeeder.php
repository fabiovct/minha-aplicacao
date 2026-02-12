<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $months = [
            ['month' => 'Janeiro'],
            ['month' => 'Fevereiro'],
            ['month' => 'Março'],
            ['month' => 'Abril'],
            ['month' => 'Maio'],
            ['month' => 'Junho'],
            ['month' => 'Julho'],
            ['month' => 'Agosto'],
            ['month' => 'Setembro'],
            ['month' => 'Outubro'],
            ['month' => 'Novembro'],
            ['month' => 'Dezembro'],
        ];

        // Insere os dados na tabela DM_Month
        DB::table('DM_Month')->insert($months);
    }
}