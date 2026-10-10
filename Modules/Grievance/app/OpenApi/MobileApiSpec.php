<?php

namespace Modules\Grievance\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'GRMS Mobile Public API',
    version: '1.0.0',
    description: 'Unauthenticated mobile app endpoints for public landing data, aggregate reports, grievance lodging, and grievance tracking.',
)]
#[OA\Server(url: '/api/v1', description: 'This application')]
final class MobileApiSpec {}
