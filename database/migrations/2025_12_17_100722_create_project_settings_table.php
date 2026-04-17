<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_settings', function (Blueprint $table) {
            $table->id();
            // Project ID foreign key
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');

            // Field ka naam (e.g., 'girth', 'tree_name')
            $table->string('field_key');

            // 1 = Required, 0 = Not Required
            $table->boolean('is_required')->default(0);

            // Numeric validation ke liye (Girth, Height, etc.)
            $table->integer('min_value')->nullable();
            $table->integer('max_value')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_settings');
    }
};
