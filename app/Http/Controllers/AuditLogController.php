<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use Illuminate\Http\Request;

/**
 * Controller: AuditLogController
 */
class AuditLogController extends Controller
{
    public function __construct(
        private AuditService $auditService,
    ) {}

    public function index(Request $request)
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Acesso restrito ao administrador.');
        }

        $filters = $request->only(['user_id', 'action', 'date_from', 'date_to']);
        $logs = $this->auditService->getLogs($filters);

        return view('audit.index', compact('logs', 'filters'));
    }
}
