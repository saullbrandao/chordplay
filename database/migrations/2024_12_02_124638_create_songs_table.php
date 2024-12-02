<?php

use App\Enums\SongKeyEnum;
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
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('artist_id')->constrained();
            $table->enum('key', array_column(SongKeyEnum::cases(), 'name'))->nullable();
            $table->timestamps();
        });
    }
    /**

     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('songs');
    }
};
