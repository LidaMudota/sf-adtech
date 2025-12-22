<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->onDelete('cascade');
            $table->foreignId('webmaster_id')->constrained('users')->onDelete('cascade');
            $table->string('token')->unique();
            $table->decimal('webmaster_cpc', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['webmaster_id', 'offer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
