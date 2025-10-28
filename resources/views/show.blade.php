@extends('sms-catcher::layout')

@section('content')
    <div class="grid">
        <div class="panel">
            <div class="panel-header">
                <div>
                    <strong>Message details</strong>
                    <div class="meta">Sent {{ \Carbon\Carbon::parse($message['timestamp'])->toDayDateTimeString() }}</div>
                </div>
                <div class="actions">
                    <form method="POST" action="{{ route('sms-catcher.destroy', $message['id']) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn" type="submit">Delete</button>
                    </form>
                    <a class="btn" href="{{ route('sms-catcher.index') }}">Back</a>
                </div>
            </div>
            <div style="padding: 1.5rem; display: grid; gap: 1rem;">
                <div>
                    <div class="meta">To</div>
                    <div><strong>{{ $message['to'] }}</strong></div>
                </div>
                @if($message['from'])
                    <div>
                        <div class="meta">From</div>
                        <div>{{ $message['from'] }}</div>
                    </div>
                @endif
                <div>
                    <div class="meta">Notification</div>
                    <div><code>{{ $message['notification'] }}</code></div>
                </div>
                @if(!empty($message['extra']))
                    <div>
                        <div class="meta">Extra payload</div>
                        <pre style="background: rgba(148,163,184,0.12); padding: 1rem; border-radius: 0.75rem; overflow-x: auto;">{{ json_encode($message['extra'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                @endif
            </div>
        </div>
        <div class="panel">
            <div class="panel-header">
                <strong>Preview</strong>
            </div>
            <div class="phone-shell">
                <div class="phone-screen">
                    <div class="bubble">{!! nl2br(e($message['body'])) !!}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
