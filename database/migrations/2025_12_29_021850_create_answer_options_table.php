<?php

use App\Models\AnswerType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('answer_options', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AnswerType::class)->constrained()->cascadeOnDelete();

            $table->string('key');
            $table->string('label');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();

            $table->timestamps();

            $table->unique(['answer_type_id', 'key']);
            $table->index('answer_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answer_options');
    }
};
