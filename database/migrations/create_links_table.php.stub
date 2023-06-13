<?php

use Neon\Models\Statuses\BasicStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            
            $table->uuid('id');

            $table->uuid('parent_id')
                ->nullable()
                ->default(null);

            $table->char('status', 1)
                ->default(BasicStatus::default()->value);
            $table->string('title');
            $table->string('slug');
            
            $table->string('og_title')
                ->nullable()
                ->default(null);
            $table->mediumText('og_description')
                ->nullable()
                ->default(null);
            $table->string('og_image')
                ->nullable()
                ->default(null);

            $table->string('url')
                ->nullable();
            $table->string('method')
                ->default('GET');
            $table->string('route')
                ->nullable();
            $table->string('link')
                ->nullable();
            $table->json('parameters')
                ->nullable();

            $table->longtext('content');

            $table->timestamp('published_at')
                ->nullable()
                ->default(null);
            $table->timestamp('expired_at')
                ->nullable()
                ->default(null);
            
            $table->timestamps();
            $table->softDeletes();
            
            /** Set keys.
             */
            $table->primary('id');
            $table->unique(['parent_id', 'slug']);
            $table->index('deleted_at');
            $table->index('published_at');
            $table->index('expired_at');
            $table->index('status');
            $table->fullText('content');

        });
    }

    public function down()
    {
        Schema::dropIfExists('links');
    }
}