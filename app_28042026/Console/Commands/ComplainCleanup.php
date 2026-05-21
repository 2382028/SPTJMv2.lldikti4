<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ComplainCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'complain:cleanup {--days=90 : Hapus complain CLOSED lebih lama dari X hari (berdasarkan handled_at)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus complain yang sudah ditangani (setuju/tolak) dan lebih lama dari N hari, termasuk lampiran jika ada.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        if ($days <= 0) {
            $days = 90;
        }

        $threshold = now()->subDays($days);
        $deleted = 0;

        DB::table('i_complain')
            ->select(['id', 'lampiran'])
            ->whereIn('status', ['setuju', 'tolak'])
            ->whereNotNull('handled_at')
            ->where('handled_at', '<', $threshold)
            ->orderBy('id')
            ->chunkById(200, function ($rows) use (&$deleted) {
                foreach ($rows as $row) {
                    $this->deleteLampiran($row->lampiran ?? null);
                    DB::table('i_complain')->where('id', $row->id)->delete();
                    $deleted++;
                }
            });

        $this->info("Deleted {$deleted} complain(s).");
        return Command::SUCCESS;
    }

    private function deleteLampiran($lampiran): void
    {
        try {
            if (empty($lampiran)) {
                return;
            }

            $paths = [];
            if (is_string($lampiran) && trim($lampiran) !== '') {
                $raw = trim($lampiran);
                if (str_starts_with($raw, '[')) {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded)) {
                        $paths = $decoded;
                    }
                } else {
                    $paths = [$raw];
                }
            }

            foreach ($paths as $p) {
                $p = ltrim((string) $p, '/');
                if ($p === '') {
                    continue;
                }

                // Prefer public disk; fallback to default disk.
                if (Storage::disk('public')->exists($p)) {
                    Storage::disk('public')->delete($p);
                } elseif (Storage::exists($p)) {
                    Storage::delete($p);
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
