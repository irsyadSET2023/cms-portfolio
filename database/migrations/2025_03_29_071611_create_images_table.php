<?php

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
        Schema::create('images', function (Blueprint $table) {
            // Primary key with ULID
            $table->ulid('id')->primary();

            // Image details
            $table->string('path', 255)->nullable(false);
            $table->string('original_filename', 255)->nullable(false);
            $table->string('mime_type', 100)->nullable(false);
            $table->integer('size')->unsigned()->comment('File size in bytes');
            $table->string('alt_text', 255)->nullable();
            
            // Polymorphic relationship fields
            $table->ulid('imageable_id');
            $table->string('imageable_type');
            $table->index(['imageable_id', 'imageable_type']);

            // Optional metadata
            $table->integer('order')->default(0)->comment('Order of images when multiple exist');
            $table->boolean('is_primary')->default(false);
            
            // Timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};