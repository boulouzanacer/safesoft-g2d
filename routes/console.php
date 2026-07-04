<?php

use App\Models\Client;
use App\Models\Cmd1;
use App\Models\Fournisseur;
use App\Models\VisitPlan;
use App\Services\VisitPlanningService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('clients:cleanup-root-duplicates {--execute : Apply the cleanup instead of running a dry run}', function () {
    $execute = (bool) $this->option('execute');

    $rootClients = Client::query()
        ->simpleRoot()
        ->whereNotNull('email')
        ->orderBy('id')
        ->get();

    $rows = [];
    $toDelete = collect();

    foreach ($rootClients as $rootClient) {
        $email = trim((string) $rootClient->email);
        if ($email === '') {
            continue;
        }

        $supplierClients = Client::query()
            ->where('email', $email)
            ->whereNotNull('id_frs')
            ->with('fournisseur:id,nom_frs')
            ->get();

        if ($supplierClients->isEmpty()) {
            continue;
        }

        $ordersCount = Cmd1::withTrashed()
            ->where('id_client', $rootClient->id)
            ->count();

        $rows[] = [
            'root_id' => (string) $rootClient->id,
            'email' => $email,
            'supplier_accounts' => (string) $supplierClients->count(),
            'suppliers' => $supplierClients
                ->pluck('fournisseur.nom_frs')
                ->filter()
                ->unique()
                ->implode(', '),
            'orders' => (string) $ordersCount,
            'action' => $ordersCount === 0 ? ($execute ? 'deleted' : 'would_delete') : 'kept_has_orders',
        ];

        if ($ordersCount === 0) {
            $toDelete->push($rootClient);
        }
    }

    if (count($rows) === 0) {
        $this->info('No duplicate root clients found.');
        return;
    }

    $this->table(
        ['root_id', 'email', 'supplier_accounts', 'suppliers', 'orders', 'action'],
        $rows
    );

    if (! $execute) {
        $this->warn('Dry run only. Re-run with --execute to delete duplicate root clients without orders.');
        return;
    }

    $deleted = 0;
    foreach ($toDelete as $client) {
        $client->delete();
        $deleted++;
    }

    $this->info("Cleanup completed. Deleted {$deleted} duplicate root client(s).");
})->purpose('Remove duplicate root website clients once supplier-linked client records already exist');

Artisan::command('visits:generate {--frs=} {--days=60}', function (VisitPlanningService $service) {
    $days = max(1, (int) $this->option('days'));
    $from = Carbon::today();
    $to = Carbon::today()->addDays($days);
    $frsId = $this->option('frs');

    $fournisseurIds = VisitPlan::query()
        ->where('is_active', 1)
        ->when($frsId, fn ($query) => $query->where('id_frs', (int) $frsId))
        ->distinct()
        ->pluck('id_frs');

    foreach ($fournisseurIds as $supplierId) {
        $service->regenerateForFournisseur((int) $supplierId, $from, $to);
    }

    $this->info('Planning de visite et tournees regeneres avec succes.');
})->purpose('Generate visit cache for the next days');

Artisan::command('fournisseurs:sync-expiration', function () {
    $expired = Fournisseur::query()
        ->whereNotNull('expires_at')
        ->whereDate('expires_at', '<', Carbon::today()->toDateString())
        ->where('actif', 1)
        ->get();

    $count = 0;
    foreach ($expired as $fournisseur) {
        $fournisseur->update(['actif' => 0]);
        $count++;
    }

    $this->info("{$count} fournisseur(s) expire(s) ont ete desactive(s).");
})->purpose('Disable expired fournisseurs automatically');

Schedule::command('visits:generate --days=60')->dailyAt('00:00');
Schedule::command('fournisseurs:sync-expiration')->dailyAt('00:00');
