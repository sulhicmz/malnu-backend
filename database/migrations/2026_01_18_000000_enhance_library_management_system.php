<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Book Authors (support multiple authors per book)
        Schema::create('book_authors', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('book_id');
            $table->string('author_name', 100);
            $table->integer('author_order')->default(0);
            $table->datetimes();
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->index(['book_id', 'author_order']);
        });

        // Book Categories (hierarchical categorization)
        Schema::create('book_categories', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->uuid('parent_id')->nullable();
            $table->text('description')->nullable();
            $table->datetimes();
            $table->foreign('parent_id')->references('id')->on('book_categories')->onDelete('set null');
            $table->index('code');
        });

        // Book Subjects (subject classification)
        Schema::create('book_subjects', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->datetimes();
            $table->index('code');
        });

        // Book-Category pivot table
        Schema::create('book_category_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('book_id');
            $table->uuid('category_id');
            $table->datetimes();
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('book_categories')->onDelete('cascade');
            $table->unique(['book_id', 'category_id']);
        });

        // Book-Subject pivot table
        Schema::create('book_subject_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('book_id');
            $table->uuid('subject_id');
            $table->datetimes();
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('book_subjects')->onDelete('cascade');
            $table->unique(['book_id', 'subject_id']);
        });

        // Book Holds (reservations)
        Schema::create('book_holds', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('book_id');
            $table->uuid('patron_id');
            $table->date('hold_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_ready')->default(false);
            $table->boolean('is_cancelled')->default(false);
            $table->string('status', 20)->default('pending');
            $table->datetimes();
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('patron_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['book_id', 'status']);
            $table->index(['patron_id', 'status']);
        });

        // Library Cards (patron management)
        Schema::create('library_cards', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('user_id');
            $table->string('card_number', 50)->unique();
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status', 20)->default('active');
            $table->datetimes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('card_number');
            $table->index(['user_id', 'is_active']);
        });

        // Reading History (patron book history)
        Schema::create('reading_history', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('book_id');
            $table->uuid('user_id');
            $table->date('loan_date');
            $table->date('return_date');
            $table->datetimes();
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'loan_date']);
        });

        // Loan Policies (borrowing rules)
        Schema::create('loan_policies', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 100);
            $table->string('user_type', 20);
            $table->integer('max_books')->default(5);
            $table->integer('loan_duration_days')->default(14);
            $table->integer('renewal_limit')->default(2);
            $table->decimal('fine_per_day', 8, 2)->default(0.00);
            $table->integer('grace_period_days')->default(3);
            $table->boolean('is_active')->default(true);
            $table->datetimes();
            $table->index(['user_type', 'is_active']);
        });

        // Enhance books table with additional fields
        Schema::table('books', function (Blueprint $table) {
            $table->string('subtitle', 200)->nullable();
            $table->string('language', 10)->default('en');
            $table->integer('pages')->nullable();
            $table->string('edition', 50)->nullable();
            $table->string('genre', 50)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('location', 100)->nullable();
            $table->string('call_number', 50)->nullable();
            $table->boolean('is_reference_only')->default(false);
            $table->integer('total_copies')->default(1);
        });

        // Enhance book_loans table with fine tracking
        Schema::table('book_loans', function (Blueprint $table) {
            $table->integer('renewal_count')->default(0);
            $table->date('due_date_original')->nullable();
            $table->decimal('fine_amount', 10, 2)->default(0.00);
            $table->boolean('fine_paid')->default(true);
            $table->date('fine_paid_date')->nullable();
            $table->uuid('library_card_id')->nullable();
            $table->foreign('library_card_id')->references('id')->on('library_cards')->onDelete('set null');
            $table->index(['borrower_id', 'status']);
            $table->index(['due_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_loans', function (Blueprint $table) {
            $table->dropForeign(['library_card_id']);
            $table->dropIndex(['borrower_id', 'status']);
            $table->dropIndex(['due_date', 'status']);
            $table->dropColumn([
                'renewal_count',
                'due_date_original',
                'fine_amount',
                'fine_paid',
                'fine_paid_date',
                'library_card_id'
            ]);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'subtitle',
                'language',
                'pages',
                'edition',
                'genre',
                'price',
                'location',
                'call_number',
                'is_reference_only',
                'total_copies'
            ]);
        });

        Schema::dropIfExists('loan_policies');
        Schema::dropIfExists('reading_history');
        Schema::dropIfExists('library_cards');
        Schema::dropIfExists('book_holds');
        Schema::dropIfExists('book_subject_mappings');
        Schema::dropIfExists('book_category_mappings');
        Schema::dropIfExists('book_subjects');
        Schema::dropIfExists('book_categories');
        Schema::dropIfExists('book_authors');
    }
};
