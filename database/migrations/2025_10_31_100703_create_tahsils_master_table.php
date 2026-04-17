<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tahsils_master', function (Blueprint $table) {
            $table->id();
            $table->string('tahsil_name');
            $table->string('short_code')->nullable();
            $table->unsignedBigInteger('state_id');
            $table->unsignedBigInteger('district_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('state_id')->references('id')->on('state_master')->onDelete('cascade');
            $table->foreign('district_id')->references('id')->on('districts_master')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahsils_master');
    }
};
