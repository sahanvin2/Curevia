<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary');
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('draft'); // draft, review, published
            $table->integer('read_time')->default(5);
            $table->unsignedBigInteger('views')->default(0);
            $table->json('quick_facts')->nullable();
            $table->json('meta')->nullable();
            $table->json('images')->nullable();
            $table->json('content_sections')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->fullText(['title', 'summary', 'content']);
        });

        Schema::create('article_article', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_article_id')->constrained('articles')->cascadeOnDelete();
            $table->primary(['article_id', 'related_article_id']);
        });

        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt');
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->integer('read_time')->default(8);
            $table->unsignedBigInteger('views')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->string('badge')->nullable();
            $table->string('category')->nullable();
            $table->longText('long_description')->nullable();
            $table->json('features')->nullable();
            $table->json('specifications')->nullable();
            $table->json('gallery')->nullable();
            $table->string('affiliate_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('bookmarkable');
            $table->timestamps();

            $table->unique(['user_id', 'bookmarkable_id', 'bookmarkable_type']);
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('commentable');
            $table->text('body');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('contributor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('expertise')->nullable();
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->unsignedInteger('reputation')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contributor_profiles');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('bookmarks');
        Schema::dropIfExists('products');
        Schema::dropIfExists('stories');
        Schema::dropIfExists('article_article');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('categories');
    }
};
