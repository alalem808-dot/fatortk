<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::query()
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->when($request->search, fn($q) => $q->where('description', 'like', "%{$request->search}%"))
            ->orderByDesc('created_at')
            ->paginate(30);
        
        $actions = ActivityLog::select('action')->distinct()->pluck('action');
        
        return view('super_admin.activity_log', compact('logs', 'actions'));
    }
}
