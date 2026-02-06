<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('book_loans', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('book_reviews', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('ebook_formats', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('ppdb_documents', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('ppdb_tests', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('ppdb_announcements', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('book_loans', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('book_reviews', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('ebook_formats', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('ppdb_documents', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('ppdb_tests', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('ppdb_announcements', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
