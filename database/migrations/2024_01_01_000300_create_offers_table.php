<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertiser_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->decimal('price_per_click', 10, 2);
            $table->string('target_url');
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');
            $table->timestamps();
        });

        Schema::create('offer_topic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->onDelete('cascade');
            $table->foreignId('topic_id')->constrained('topics')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['offer_id', 'topic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_topic');
        Schema::dropIfExists('offers');
    }
};
