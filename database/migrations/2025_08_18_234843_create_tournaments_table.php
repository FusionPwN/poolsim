<?php

use App\Enums\TournamentStatus;
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
		Schema::create('tournaments', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->enum('status', array_map(fn($case) => $case->value, TournamentStatus::cases()))->default('open')->index();
			$table->timestamp('started_at')->nullable();
			$table->timestamp('ended_at')->nullable();
			$table->timestamps();
		});
	}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
