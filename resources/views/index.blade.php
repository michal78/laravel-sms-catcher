@extends('sms-catcher::layout')

@section('content')
    <div class="panel">
        <div class="panel-header">
            <div>
                <strong>{{ $messages->count() }} message{{ $messages->count() === 1 ? '' : 's' }}</strong>
                <div class="meta">Only available in local/dev environments</div>
            </div>
            <div class="actions">
                <form method="POST" action="{{ route('sms-catcher.clear') }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn" type="submit">Clear inbox</button>
                </form>
            </div>
        </div>
        <div class="message-list">
            @forelse($messages as $message)
                <a href="{{ route('sms-catcher.show', $message['id']) }}" class="message">
                    <div><strong>{{ $message['to'] }}</strong></div>
                    <div>{!! \SmsCatcher\Helpers\UrlProcessor::linkify(\Illuminate\Support\Str::limit($message['body'], 120)) !!}</div>
                    <small>{{ \Carbon\Carbon::parse($message['timestamp'])->diffForHumans() }}</small>
                </a>
            @empty
                <div class="empty">
                    <p>No SMS notifications have been captured yet.</p>
                    <p>Trigger a notification using the <code>sms</code> channel to see it appear here.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
