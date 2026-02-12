<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Aziz'],
            ['name' => 'Aluguel'],
            ['name' => 'Plano Saúde'],
            ['name' => 'Dental'],
            ['name' => 'Água'],
            ['name' => 'Luz'],
            ['name' => 'Gás'],
            ['name' => 'Bradecard'],
            ['name' => 'XP'],
            ['name' => 'Nubank'],
            ['name' => 'Mercado'],
            ['name' => 'Transporte'],
            ['name' => 'Extras'],
            ['name' => 'X Aliança Pousada'],
            ['name' => 'Cap Bradesco'],
            ['name' => 'Seguro Carro'],
            ['name' => 'Carro'],
            ['name' => 'Academia'],
            ['name' => 'Netflix'],
            ['name' => 'Internet'],
            ['name' => 'Futebol'],
            ['name' => 'Doc Carro'],
            ['name' => 'Doações'],
            ['name' => 'Tim'],
            ['name' => 'Boxe/Manual'],
            ['name' => 'Salário'], // Adicionado como tipo de receita
        ];

        DB::table('TB_Type_Expense')->insert($types);
    }
}
