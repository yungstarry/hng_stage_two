<?php

use App\Models\Organisation;
use App\Models\User;
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
        Schema::create('organisation_user', function (Blueprint $table) {
            $table->uuid('userId');
            $table->uuid('orgId');
            $table->timestamps();

         
            $table->foreign('userId')->references('userId')->on('users')->cascadeOnDelete();
            $table->foreign('orgId')->references('orgId')->on('organisations')->cascadeOnDelete();

            // Composite primary key
            $table->primary(['userId', 'orgId']);
          });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_user');
    }
};
