<?php

namespace App\Console\Commands;

use App\Models\Equipo;
use Illuminate\Console\Command;

class ListEquiposTags extends Command
{
    protected $signature = 'equipos:tags {--filter= : Filter tags containing this string}';

    protected $description = 'List all equipo tags as a flat array';

    public function handle(): int
    {
        $query = Equipo::orderBy('tag');

        if ($filter = $this->option('filter')) {
            $query->where('tag', 'like', "%{$filter}%");
        }

        $tags = $query->pluck('tag')->toArray();

        if (empty($tags)) {
            $this->warn('No equipos found.');

            return self::FAILURE;
        }

        $this->line(json_encode($tags, JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
