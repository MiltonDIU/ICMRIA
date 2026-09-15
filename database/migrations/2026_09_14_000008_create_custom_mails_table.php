<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The custom-mail feature shipped with a model, a controller, routes and views but
 * no migration, so the table never existed and every page that listed the saved
 * templates failed with "table not found" — including the profile screen, for
 * everyone except authors.
 *
 * Columns follow App\Models\CustomMail and Admin\CustomMailController.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('custom_mails')) {
            return;
        }

        Schema::create('custom_mails', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->longText('mail_body');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('publication_status')->default(0);
            $table->timestamps();

            $table->index('publication_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_mails');
    }
};
