<?php

declare(strict_types=1);

use App\Enums\SongKeyEnum;
use App\Enums\SongLineContentType;
use App\Models\Artist;
use App\Models\Song;
use App\Models\SongSubmission;
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
        Schema::create('song_submissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->enum('key', SongKeyEnum::values());
            $table->foreignIdFor(Song::class)->nullable()->cascadeOnDelete();
            $table->foreignIdFor(Artist::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('song_submission_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(SongSubmission::class)->constrained()->cascadeOnDelete();
            $table->integer('line_number');
            $table->enum('content_type', SongLineContentType::values());
            $table->text('content');
            $table->timestamps();
            $table->unique(['song_submission_id', 'line_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('song_submissions');
        Schema::dropIfExists('song_submissions_lines');
    }
};
