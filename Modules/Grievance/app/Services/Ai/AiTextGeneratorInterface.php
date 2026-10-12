<?php

namespace Modules\Grievance\Services\Ai;

interface AiTextGeneratorInterface
{
    public function generate(string $prompt): string;
}
