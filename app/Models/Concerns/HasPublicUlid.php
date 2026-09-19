<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUlids;

trait HasPublicUlid
{
    use HasUlids;

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

//    public function getRouteKeyName(): string
//    {
//        return 'ulid';
//    }
}
