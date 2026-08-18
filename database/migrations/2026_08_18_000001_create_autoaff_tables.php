<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('product_title')->nullable();
            $table->text('product_description')->nullable();
            $table->string('product_price')->nullable();
            $table->string('shop_name')->nullable();
            $table->text('affiliate_url');
            $table->text('canonical_url')->nullable();
            $table->text('caption')->nullable();
            $table->json('media_files')->nullable();
            $table->string('status')->default('draft');
            $table->string('facebook_post_id')->nullable();
            $table->string('facebook_post_url')->nullable();
            $table->text('tags')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('social_accounts', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('platform');
            $table->string('name');
            $table->string('account_id')->nullable();
            $table->text('access_token')->nullable();
            $table->json('extra_config')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamp('timestamp');
            $table->string('post_id')->nullable();
            $table->string('provider');
            $table->string('model');
            $table->string('style');
            $table->integer('prompt_tokens')->default(0);
            $table->integer('completion_tokens')->default(0);
            $table->integer('total_tokens')->default(0);
            $table->decimal('estimated_cost', 10, 6)->default(0.000000);
            $table->timestamps();
        });

        Schema::create('workflow_rules', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('category');
            $table->string('frequency');
            $table->json('times')->nullable();
            $table->json('days')->nullable();
            $table->string('target_page');
            $table->json('workflow_actions')->nullable();
            $table->json('action_contexts')->nullable();
            $table->text('general_context')->nullable();
            $table->text('weather_context')->nullable();
            $table->text('occasion_context')->nullable();
            $table->json('tones')->nullable();
            $table->json('personas')->nullable();
            $table->string('custom_persona')->nullable();
            $table->text('manual_prompt')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('last_run')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_rules');
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('social_accounts');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('posts');
    }
};
