<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tournament extends Model
{
    use HasUuids;

    protected $fillable = [
        'slug', 'name', 'venue', 'timezone', 'start_date', 'end_date', 'round_count',
        'status', 'pairing_system', 'ruleset_version', 'scoring_config',
        'tie_break_config', 'initial_order_config', 'regulations_snapshot',
        'is_public', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'scoring_config' => 'array',
            'tie_break_config' => 'array',
            'initial_order_config' => 'array',
            'regulations_snapshot' => 'array',
            'is_public' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }
}
