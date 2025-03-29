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
        Schema::create('experiences', function (Blueprint $table) {
            // Primary key with ULID
            $table->ulid('id')->primary();

            // User relationship with robust constraints
            $table->foreignUlid('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            // Company details
            $table->string('company_name', 255)
                ->nullable(false);

            // Correct column type for designation (changed from date)
            $table->string('designation', 150)
                ->nullable(false)
                ->comment('Job title or role');

            // Employment dates with more context
            $table->date('start_date')
                ->nullable(false)
                ->comment('Start date of employment');

            $table->date('end_date')
                ->nullable()
                ->comment('End date of employment (null for current job)');

            // Employment type
            $table->enum('employment_type', [
                'full-time',
                'part-time',
                'contract',
                'freelance',
                'internship',
                'temporary'
            ])->default('full-time')
                ->comment('Type of employment');

            // Detailed description
            $table->text('description')
                ->nullable()
                ->comment('Job responsibilities and achievements');

            // Additional experience details
            $table->string('location', 255)
                ->nullable()
                ->comment('Location of the workplace');

            $table->boolean('is_current')
                ->default(false)
                ->comment('Indicates if this is the current job');

            $table->string('industry', 150)
                ->nullable()
                ->comment('Industry or sector of the company');

            // Soft delete and timestamps
            $table->softDeletes();
            $table->timestamps();


            // Performance optimization indexes
            $table->index(['user_id', 'start_date', 'end_date']);
            $table->index('company_name');
            $table->index('designation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
