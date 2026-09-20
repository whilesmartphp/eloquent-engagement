<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('engagement.events_table', 'engagement_events'), function (Blueprint $table) {
            $table->string('client_key', 100)->nullable()->index();
            $table->string('visitor_id', 100)->nullable()->index();
            $table->string('session_id', 100)->nullable()->index();
            $table->string('source', 200)->nullable()->index();
            $table->string('utm_medium', 200)->nullable()->index();
            $table->string('utm_campaign', 200)->nullable()->index();
            $table->string('utm_term', 200)->nullable();
            $table->string('utm_content', 200)->nullable();
            $table->string('click_id', 500)->nullable()->index();
            $table->text('page_url')->nullable();
            $table->text('referrer')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(config('engagement.events_table', 'engagement_events'), function (Blueprint $table) {
            $table->dropColumn([
                'client_key', 'visitor_id', 'session_id', 'source', 'utm_medium',
                'utm_campaign', 'utm_term', 'utm_content', 'click_id', 'page_url', 'referrer',
            ]);
        });
    }
};
