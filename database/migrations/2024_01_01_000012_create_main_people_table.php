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
        Schema::create('main_people', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email', 191)->unique();
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->string('document_type')->nullable();
            $table->string('document_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('user_add')->nullable();
            $table->unsignedBigInteger('user_edit')->nullable();
            $table->unsignedBigInteger('user_deleted')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('is_active');
            // Limit the length of the composite index to avoid MySQL key length issues
            $table->index(['first_name', 'last_name'], 'main_people_name_index', 'btree', 191);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_people');
    }
}; 