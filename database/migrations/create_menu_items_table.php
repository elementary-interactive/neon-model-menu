<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neon\Models\Statuses\BasicStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('menu_id');
            $table->uuid('parent_id')
                ->nullable()
                ->default(null);
            $table->uuid('link_id')
                ->nullable()
                ->default(null);
            $table->boolean('is_outside')
                ->default(false);
            $table->string('title');
            $table->string('url')
                ->nullable()
                ->default(null);
            $table->string('target')
                ->default('_self');
            $table->tinyInteger('order');

            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');
            $table->index('status');
            $table->index('deleted_at');
            $table->foreign('link_id')->references('id')->on('links');
            $table->foreign('menu_id')->references('id')->on('menus');
            $table->foreign('parent_id')->references('id')->on('menu_items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_items');
    }
};
