<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('DM_Month', function (Blueprint $table) {
            $table->id(); // BigInt PK
            $table->string('month', 20); // Ex: Janeiro, Fevereiro...
        });
    }

    public function down(): void {
        Schema::dropIfExists('DM_Month');
    }
};
