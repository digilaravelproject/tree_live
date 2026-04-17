<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->unsignedBigInteger('state_id');          // ← yaha
            $table->string('client_name');
            $table->string('company_name');
            $table->unsignedBigInteger('field_officer_id');  // ← yaha
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('state_id')
                ->references('id')
                ->on('state_master')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('field_officer_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropForeign(['field_officer_id']);
        });

        Schema::dropIfExists('projects');
    }
};
