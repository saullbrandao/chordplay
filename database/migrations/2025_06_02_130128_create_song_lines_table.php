<?php

declare(strict_types=1);

use App\Enums\SongLineContentType;
use App\Models\Song;
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
        Schema::create('song_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Song::class)->constrained()->cascadeOnDelete();
            $table->integer('line_number');
            $table->enum('content_type', SongLineContentType::values());
            $table->text('content');
            $table->unique(['song_id', 'line_number']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('song_lines');
    }
};
