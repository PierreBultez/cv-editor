<?php

declare(strict_types=1);

use App\Models\Cv;

it('supprime les CV inactifs au-dela du seuil', function () {
    [$vieux] = Cv::createAnonymous();
    [$recent] = Cv::createAnonymous();

    $vieux->forceFill(['last_seen_at' => now()->subMonths(14)])->saveQuietly();
    $recent->forceFill(['last_seen_at' => now()->subDays(3)])->saveQuietly();

    $this->artisan('cv:purge')->assertSuccessful();

    expect(Cv::pluck('public_id')->all())->toBe([$recent->public_id]);
});

it('ne supprime rien en mode dry-run', function () {
    [$cv] = Cv::createAnonymous();
    $cv->forceFill(['last_seen_at' => now()->subMonths(24)])->saveQuietly();

    $this->artisan('cv:purge', ['--dry-run' => true])->assertSuccessful();

    expect(Cv::count())->toBe(1);
});

it('respecte le seuil passe en option', function () {
    [$cv] = Cv::createAnonymous();
    $cv->forceFill(['last_seen_at' => now()->subMonths(3)])->saveQuietly();

    $this->artisan('cv:purge', ['--months' => 24])->assertSuccessful();
    expect(Cv::count())->toBe(1);

    $this->artisan('cv:purge', ['--months' => 1])->assertSuccessful();
    expect(Cv::count())->toBe(0);
});
