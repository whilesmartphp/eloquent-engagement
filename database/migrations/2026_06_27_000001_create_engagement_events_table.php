<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('engagement.events_table', 'engagement_events'), function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('subject');
            $table->string('name')->index();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id', 'occurred_at']);
            $table->index(['name', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('engagement.events_table', 'engagement_events'));
    }
};
