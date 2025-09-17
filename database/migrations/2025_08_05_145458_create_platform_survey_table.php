<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_survey', function (Blueprint $table) {
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->foreignId('platform_id')->constrained()->onDelete('cascade');
            $table->primary(['survey_id', 'platform_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_survey');
    }
};
