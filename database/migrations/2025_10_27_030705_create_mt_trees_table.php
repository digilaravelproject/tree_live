<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mt_trees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('ward_plot_no')->nullable();
            $table->string('tree_no')->nullable();
            $table->string('tree_name')->nullable();
            $table->string('scientific_name')->nullable();
            $table->string('family')->nullable();
            $table->decimal('girth', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('canopy', 8, 2)->nullable();
            $table->integer('age')->nullable();
            $table->string('condition')->nullable();
            $table->text('address')->nullable();
            $table->string('landmark')->nullable();
            $table->string('ownership')->nullable();
            $table->string('concern_person')->nullable();
            $table->text('remark')->nullable();
            $table->longText('tree_image_upload')->nullable();
            $table->longText('captured_image')->nullable();
            $table->json('all_captured_images')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('datetime')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mt_trees');
    }
};
