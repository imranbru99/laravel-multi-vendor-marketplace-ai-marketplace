<?php

namespace App\Http\Controllers;

use App\Domain\Audit;
use App\Models\DigitalEntitlement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function __invoke(int $id)
    {
        $asset = DB::transaction(function () use ($id) {
            $entitlement = DigitalEntitlement::where('user_id', auth()->id())->lockForUpdate()->findOrFail($id);
            abort_if($entitlement->revoked_at || $entitlement->expires_at->isPast() || $entitlement->downloads >= $entitlement->download_limit, 403, 'Download access has expired or reached its limit.');
            $asset = $entitlement->asset;
            abort_unless(Storage::disk('local')->exists($asset->path), 404, 'The file is temporarily unavailable.');
            $entitlement->increment('downloads');
            Audit::record('digital.downloaded', (string) $entitlement->id);

            return $asset;
        }, 5);

        return Storage::disk('local')->download($asset->path, basename($asset->path), ['X-Content-Type-Options' => 'nosniff']);
    }
}
