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
        Schema::create('educations', function (Blueprint $table) {
            // Primary key with ULID for better distributed ID generation
            $table->ulid('id')->primary();

            // User relationship with cascading delete and foreign key constraint
            $table->foreignUlid('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            // University name with more robust constraints
            $table->string('university_name', 255)
                ->nullable(false)
                ->index();

            // Degree information
            $table->string('degree_type', 100)
                ->nullable(false)
                ->comment('Type of degree (e.g., Bachelor, Master, PhD)');

            $table->string('field_of_study', 255)
                ->nullable(false)
                ->comment('Major or area of specialization');

            // Date ranges with validation
            $table->date('start_date')
                ->nullable(false)
                ->comment('Start date of education');

            $table->date('end_date')
                ->nullable()
                ->comment('Graduation date or expected graduation date');

            // Additional educational details
            $table->decimal('gpa', 3, 2)
                ->nullable()
                ->comment('Grade Point Average (optional)');

            $table->text('description')
                ->nullable()
                ->comment('Additional details about education');

            $table->boolean('is_current')
                ->default(false)
                ->comment('Indicates if this is the current/ongoing education');

            $table->string('location', 255)
                ->nullable()
                ->comment('Location of the university');

            // Certification or honors
            $table->string('honors', 255)
                ->nullable()
                ->comment('Academic honors or achievements');

            // Soft delete and timestamps
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint to prevent duplicate entries
            // $table->unique([
            //     'user_id',
            //     'university_name',
            //     'degree_type',
            //     'field_of_study',
            //     'start_date'
            // ], 'unique_education_entry');

            // Index for performance optimization
            $table->index(['user_id', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educations');
    }
};
