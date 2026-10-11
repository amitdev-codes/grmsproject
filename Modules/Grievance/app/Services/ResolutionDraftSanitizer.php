<?php

namespace Modules\Grievance\Services;

class ResolutionDraftSanitizer
{
    public function sanitize(string $html): string
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,strong,em,s,u,ul,ol,li,blockquote,h2,h3');
        $config->set('URI.Disable', true);

        return (new \HTMLPurifier($config))->purify($html);
    }
}
