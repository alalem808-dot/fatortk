<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\Tenant;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $tickets = SupportTicket::with('tenant')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->orderByDesc('created_at')
            ->paginate(20);
        
        $stats = [
            'open'    => SupportTicket::where('status', 'open')->count(),
            'replied' => SupportTicket::where('status', 'replied')->count(),
            'closed'  => SupportTicket::where('status', 'closed')->count(),
            'urgent'  => SupportTicket::where('priority', 'urgent')->where('status', '!=', 'closed')->count(),
        ];
        
        return view('super_admin.support.index', compact('tickets', 'stats'));
    }
    
    public function show(SupportTicket $ticket)
    {
        $ticket->load('tenant', 'replies');
        return view('super_admin.support.show', compact('ticket'));
    }
    
    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate(['message' => 'required|string']);
        
        SupportTicketReply::create([
            'ticket_id'   => $ticket->id,
            'message'     => $request->message,
            'is_admin'    => true,
            'author_name' => auth('super_admin')->user()->name,
        ]);
        
        $ticket->update(['status' => 'replied']);
        
        \App\Models\ActivityLog::log('ticket_replied', "تم الرد على تذكرة دعم #{$ticket->id}: {$ticket->subject}", $ticket->tenant);
        
        return back()->with('success', 'تم إرسال الرد.');
    }
    
    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $request->validate(['status' => 'required|in:open,replied,closed']);
        $ticket->update(['status' => $request->status]);
        return back()->with('success', 'تم تحديث حالة التذكرة.');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'subject'   => 'required|string|max:255',
            'message'   => 'required|string',
            'priority'  => 'required|in:low,normal,high,urgent',
        ]);
        
        SupportTicket::create($request->only('tenant_id', 'subject', 'message', 'priority'));
        
        return back()->with('success', 'تم إنشاء التذكرة.');
    }
    
    public function destroy(SupportTicket $ticket)
    {
        $ticket->delete();
        return back()->with('success', 'تم حذف التذكرة.');
    }
}
