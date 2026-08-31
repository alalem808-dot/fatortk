@extends('super_admin.layout')
@section('title', 'تذكرة #' . $ticket->id)
@section('page-title')
<h6 class="mb-0 fw-bold">تذكرة #{{ $ticket->id }}: {{ Str::limit($ticket->subject, 40) }}</h6>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        {{-- محادثة التذكرة --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between">
                <div>
                    <h6 class="fw-bold mb-1">{{ $ticket->subject }}</h6>
                    <span class="text-muted small">{{ $ticket->tenant->company_name }} — {{ $ticket->created_at->format('Y-m-d H:i') }}</span>
                </div>
                <form action="{{ route('super_admin.support.status', $ticket) }}" method="POST" class="d-flex gap-1">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="open"    {{ $ticket->status=='open'    ?'selected':'' }}>مفتوح</option>
                        <option value="replied" {{ $ticket->status=='replied' ?'selected':'' }}>تم الرد</option>
                        <option value="closed"  {{ $ticket->status=='closed'  ?'selected':'' }}>مغلق</option>
                    </select>
                </form>
            </div>
            <div class="card-body">
                {{-- الرسالة الأصلية --}}
                <div class="d-flex gap-3 mb-4">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white"
                             style="width:38px;height:38px;font-weight:700">
                            {{ Str::upper(Str::substr($ticket->tenant->company_name,0,1)) }}
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="p-3 rounded-3 bg-light">
                            <div class="small fw-semibold mb-1 text-primary">{{ $ticket->tenant->company_name }}</div>
                            <div class="small">{!! nl2br(e($ticket->message)) !!}</div>
                        </div>
                    </div>
                </div>

                {{-- الردود --}}
                @foreach($ticket->replies as $reply)
                <div class="d-flex gap-3 mb-3 {{ $reply->is_admin ? 'flex-row-reverse' : '' }}">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white"
                             style="width:38px;height:38px;font-weight:700;background:{{ $reply->is_admin ? '#2563eb' : '#64748b' }}">
                            {{ Str::upper(Str::substr($reply->author_name ?? 'U',0,1)) }}
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="p-3 rounded-3 {{ $reply->is_admin ? 'bg-primary text-white' : 'bg-light' }}">
                            <div class="small fw-semibold mb-1">{{ $reply->author_name ?? 'مجهول' }}</div>
                            <div class="small">{!! nl2br(e($reply->message)) !!}</div>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.7rem">{{ $reply->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                </div>
                @endforeach

                @if($ticket->status !== 'closed')
                {{-- نموذج الرد --}}
                <form action="{{ route('super_admin.support.reply', $ticket) }}" method="POST" class="mt-4">
                    @csrf
                    <div class="mb-2">
                        <textarea name="message" class="form-control" rows="3" placeholder="اكتب ردك هنا..." required></textarea>
                    </div>
                    <button class="btn btn-primary">
                        <i class="fas fa-reply me-1"></i>إرسال الرد
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">تفاصيل التذكرة</h6>
                @foreach([
                    ['label'=>'الشركة', 'value'=>$ticket->tenant->company_name],
                    ['label'=>'الأولوية', 'value'=>['urgent'=>'عاجل','high'=>'عالي','normal'=>'عادي','low'=>'منخفض'][$ticket->priority]],
                    ['label'=>'الحالة', 'value'=>['open'=>'مفتوح','replied'=>'تم الرد','closed'=>'مغلق'][$ticket->status]],
                    ['label'=>'تاريخ الإنشاء', 'value'=>$ticket->created_at->format('Y-m-d')],
                    ['label'=>'عدد الردود', 'value'=>$ticket->replies->count()],
                ] as $d)
                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                    <span class="text-muted small">{{ $d['label'] }}</span>
                    <span class="fw-semibold small">{{ $d['value'] }}</span>
                </div>
                @endforeach

                <form action="{{ route('super_admin.support.destroy', $ticket) }}" method="POST"
                      onsubmit="return confirm('حذف هذه التذكرة نهائياً؟')" class="mt-3">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm w-100">
                        <i class="fas fa-trash me-1"></i>حذف التذكرة
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
