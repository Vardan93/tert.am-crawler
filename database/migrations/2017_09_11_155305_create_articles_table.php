<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('article_id')->unique()->index();
            $table->string('title')->nullable();
            $table->string('image')->nullable();
            $table->string('date')->nullable();
            $table->string('image_url')->nullable();
            $table->string('article_url')->nullable();
            $table->longText('description')->nullable();

            $table->tinyInteger('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
