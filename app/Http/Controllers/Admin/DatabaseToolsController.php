<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class DatabaseToolsController extends Controller
{
    private const ACCESS_PASSWORD = 'LB7Z';
    private const SESSION_KEY = 'admin.database_tools_unlocked';

    public function index(Request $request): View
    {
        $unlocked = (bool) $request->session()->get(self::SESSION_KEY, false);

        return view('admin.base-de-donnees', [
            'title' => 'Base de données',
            'is_unlocked' => $unlocked,
            'tables' => $unlocked ? $this->listBaseTables() : [],
        ]);
    }

    public function unlock(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! hash_equals(self::ACCESS_PASSWORD, (string) $data['password'])) {
            $request->session()->forget(self::SESSION_KEY);

            return back()
                ->withErrors(['password' => 'Mot de passe incorrect.'])
                ->withInput();
        }

        $request->session()->put(self::SESSION_KEY, true);

        return redirect()
            ->to('/admin/base-de-donnees')
            ->with('success', 'Accès base de données déverrouillé.');
    }

    public function resetTables(Request $request): RedirectResponse
    {
        if (! (bool) $request->session()->get(self::SESSION_KEY, false)) {
            return redirect()
                ->to('/admin/base-de-donnees')
                ->with('error', 'Déverrouillez d’abord l’accès avec le mot de passe.');
        }

        $availableTables = $this->listBaseTables();

        $data = $request->validate([
            'tables' => ['required', 'array', 'min:1'],
            'tables.*' => ['required', 'string', Rule::in($availableTables)],
        ]);

        $selectedTables = array_values(array_unique($data['tables']));

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($selectedTables as $table) {
                DB::table($table)->truncate();
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        return redirect()
            ->to('/admin/base-de-donnees')
            ->with('success', 'Tables vidées: '.implode(', ', $selectedTables));
    }

    /**
     * @return array<int, string>
     */
    private function listBaseTables(): array
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $rows = DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name");

            return array_map(
                static fn ($row) => (string) $row->name,
                $rows
            );
        }

        if ($driver === 'pgsql') {
            $rows = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");

            return array_map(
                static fn ($row) => (string) $row->tablename,
                $rows
            );
        }

        if ($driver === 'sqlsrv') {
            $rows = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME");

            return array_map(
                static fn ($row) => (string) $row->TABLE_NAME,
                $rows
            );
        }

        $rows = DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $tables = [];

        foreach ($rows as $row) {
            $values = (array) $row;

            foreach ($values as $key => $value) {
                if (strcasecmp((string) $key, 'Table_type') === 0) {
                    continue;
                }

                $tables[] = (string) $value;
                break;
            }
        }

        sort($tables, SORT_NATURAL | SORT_FLAG_CASE);

        return array_values(array_unique($tables));
    }
}
