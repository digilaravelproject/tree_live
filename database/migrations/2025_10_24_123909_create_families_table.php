<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tree_id')->unique(); // one-to-one with tree
            $table->string('family_name');
            $table->timestamps();

            $table->foreign('tree_id')->references('id')->on('trees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
