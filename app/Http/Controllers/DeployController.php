<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Run deployment tasks over HTTP.
 *
 * The hosting for this project has no SSH, so migrations cannot be run with
 * `php artisan migrate` after a deploy. These endpoints cover that gap.
 *
 * DEPLOY_TOKEN in .env is OPTIONAL:
 *   - empty -> the plain URL works, no token needed (the site owner's choice)
 *   - set   -> ?token=... (or an X-Deploy-Token header) becomes mandatory,
 *              and must be at least 20 characters
 * Set it whenever these endpoints should be locked down again.
 *
 * Guard rails that always apply:
 *   - Only additive/safe commands are exposed. Anything that can destroy data
 *     (migrate:fresh, migrate:reset, migrate:rollback, db:wipe) is NOT reachable
 *     here by design — those stay manual. `migrate` only applies pending files,
 *     so an unwanted hit cannot wipe anything.
 *   - Every call is logged with the caller's IP.
 *   - Routes are rate limited (see routes/web.php).
 */
class DeployController extends Controller
{
    /** GET /migration — apply any pending migrations. */
    public function migrate(Request $request)
    {
        if ($deny = $this->guard($request, 'migrate')) {
            return $deny;
        }

        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();

        // Migrations often change what the cached config/views assume.
        Artisan::call('config:clear');
        Artisan::call('view:clear');

        return $this->respond('Migration', $output . "\nconfig:clear + view:clear done.");
    }

    /** GET /migration/status — what has run and what is pending. */
    public function status(Request $request)
    {
        if ($deny = $this->guard($request, 'migrate:status')) {
            return $deny;
        }

        Artisan::call('migrate:status');

        return $this->respond('Migration status', Artisan::output());
    }

    /** GET /migration/clear-cache — flush config/route/view/app caches. */
    public function clearCache(Request $request)
    {
        if ($deny = $this->guard($request, 'cache:clear')) {
            return $deny;
        }

        $out = '';
        foreach (['config:clear', 'route:clear', 'view:clear', 'cache:clear'] as $cmd) {
            Artisan::call($cmd);
            $out .= str_pad($cmd, 16) . trim(Artisan::output() ?: 'done') . "\n";
        }

        return $this->respond('Cache cleared', $out);
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    /**
     * Returns a response when the request must be refused, or null to proceed.
     */
    private function guard(Request $request, string $action)
    {
        $expected = (string) config('deploy.token');
        $given    = (string) ($request->query('token') ?? $request->header('X-Deploy-Token', ''));
        $ip       = $request->ip();

        // No token configured => the endpoint is open, by the site owner's choice.
        // `migrate` only applies pending migration files, so an unwanted hit
        // cannot delete data; the destructive commands are not routed at all.
        if ($expected === '') {
            Log::warning("[deploy] {$action} run WITHOUT a token (DEPLOY_TOKEN is empty)", ['ip' => $ip]);
            return null;
        }

        // A short token is not a token. Fail closed rather than pretend to be safe.
        if (strlen($expected) < 20) {
            Log::error("[deploy] {$action} refused: DEPLOY_TOKEN too short", ['ip' => $ip]);
            abort(403, 'Deploy token is too short to be safe. Use at least 20 characters, or leave it empty.');
        }

        if ($given === '' || !hash_equals($expected, $given)) {
            Log::warning("[deploy] {$action} refused: bad token", ['ip' => $ip]);
            abort(403, 'Forbidden.');
        }

        Log::info("[deploy] {$action} allowed", ['ip' => $ip]);

        return null;
    }

    private function respond(string $title, string $output)
    {
        return response(
            "== {$title} ==\n\n" . trim($output) . "\n",
            200,
            ['Content-Type' => 'text/plain; charset=utf-8']
        );
    }
}
