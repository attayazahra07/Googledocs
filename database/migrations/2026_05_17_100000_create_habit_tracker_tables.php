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
        Schema::create('habit_boards', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('board_collaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('habit_boards')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['board_id', 'user_id']); // Prevent duplicate invites
        });

        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('habit_boards')->onDelete('cascade');
            $table->string('title');
            $table->boolean('is_completed')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('board_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('habit_boards')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // e.g., 'added_habit', 'checked_habit'
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_versions');
        Schema::dropIfExists('habits');
        Schema::dropIfExists('board_collaborators');
        Schema::dropIfExists('habit_boards');
    }
};
