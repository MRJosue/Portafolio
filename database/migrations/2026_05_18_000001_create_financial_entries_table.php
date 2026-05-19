<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_entries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('type');
            $table->date('entry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'entry_date']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_entries');
    }
};
