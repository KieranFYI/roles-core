<?php

namespace KieranFYI\Roles\Core\Models\Roles;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use KieranFYI\Logging\Traits\LoggingTrait;

class RoleLink extends Model
{
    use LoggingTrait;

    /**
     * @var string[]
     */
    protected $touches = ['model'];

    /**
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}