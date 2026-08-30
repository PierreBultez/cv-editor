<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un CV est anonyme : aucun compte, aucune relation utilisateur.
 *
 * - `public_id`  identifie le CV dans les URL publiques (non devinable) ;
 * - `edit_token` est le secret qui autorise l'ecriture. Il est stocke hache et
 *   conserve cote visiteur dans le localStorage du navigateur.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cvs', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('edit_token', 64)->index();

            $table->string('template', 40)->default('classic');
            $table->json('theme');
            $table->json('fonts');
            $table->json('content');
            $table->json('photo_variants')->nullable();

            $table->boolean('is_public')->default(true);
            $table->boolean('allow_indexing')->default(false);

            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cvs');
    }
};
