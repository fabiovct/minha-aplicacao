<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('TB_Expense_Month_Year', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com o Tipo de Despesa
            $table->foreignId('id_type_expense')
                  ->constrained('TB_Type_Expense')
                  ->onDelete('cascade');

            // Relacionamento com o Mês
            $table->foreignId('id_month')
                  ->constrained('DM_Month');

            $table->integer('year');
            
            // Usando decimal para valores financeiros (precisão de 2 casas decimais)
            $table->decimal('cost', 10, 2)->default(0.00);
            
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('TB_Expense_Month_Year');
    }
};
