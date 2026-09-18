<?php

namespace Modules\Grievance\Services\Ai;

interface AiClassifierDriverInterface
{
    /**
     * @param  array<int, array{id:int, name:string}>  $categories
     * @return array{category_id: ?int, confidence: float, reason: string}
     */
    public function classify(string $description, array $categories): array;
}

