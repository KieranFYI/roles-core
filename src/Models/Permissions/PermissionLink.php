<?php

namespace KieranFYI\Roles\Models\Permissions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use KieranFYI\Logging\Traits\LoggingTrait;

class PermissionLink extends Model
{
    use LoggingTrait;

    /**
     * @return BelongsTo
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}