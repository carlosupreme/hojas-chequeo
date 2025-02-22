<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->string("name")->nullable();
            $table->string("area")->nullable();
            $table->string("department")->nullable();
            $table->string("equipment")->nullable();
            $table->string("vehicle")->nullable();
            $table->string('tag')->nullable();
            $table->string("priority")->nullable();
            $table->string("observations")->nullable();
            $table->string("failure")->nullable();
            $table->string("photo")->nullable();
            $table->foreignIdFor(\App\Models\User::class)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->dropColumn("name");
            $table->dropColumn("area");
            $table->dropColumn("department");
            $table->dropColumn("equipment");
            $table->dropColumn("vehicle");
            $table->dropColumn('tag');
            $table->dropColumn("priority");
            $table->dropColumn("observations");
            $table->dropColumn("failure");
            $table->dropColumn("photo");
            $table->dropColumn("user_id");
        });
    }
};
