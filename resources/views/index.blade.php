@extends('sms-catcher::layout')

@push('head')
<script>
let autoUpdateInterval = null;
let isAutoUpdateEnabled = false;

function toggleAutoUpdate() {
    if (isAutoUpdateEnabled) {
        stopAutoUpdate();
    } else {
        startAutoUpdate();
    }
}

function startAutoUpdate() {
    isAutoUpdateEnabled = true;
    localStorage.setItem('sms-catcher-auto-update', 'true');
    
    // Update UI
    const toggleText = document.getElementById('toggle-text');
    const toggleStatus = document.getElementById('toggle-status');
    toggleText.textContent = 'Auto-update ON';
    toggleStatus.style.display = 'inline';
    
    // Start interval
    autoUpdateInterval = setInterval(fetchMessages, 5000);
}

function stopAutoUpdate() {
    isAutoUpdateEnabled = false;
    localStorage.setItem('sms-catcher-auto-update', 'false');
    
    // Update UI
    const toggleText = document.getElementById('toggle-text');
    const toggleStatus = document.getElementById('toggle-status');
    toggleText.textContent = 'Enable auto-update';
    toggleStatus.style.display = 'none';
    
    // Clear interval
    if (autoUpdateInterval) {
        clearInterval(autoUpdateInterval);
        autoUpdateInterval = null;
    }
}

function fetchMessages() {
    const apiUrl = '{{ route("sms-catcher.api") }}';
    
    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            updateMessageList(data.messages);
        })
        .catch(error => {
            console.error('Error fetching messages:', error);
        });
}

function updateMessageList(messages) {
    const messageList = document.querySelector('.message-list');
    if (!messageList) return;
    
    // Store current scroll position
    const scrollTop = messageList.scrollTop;
    
    if (messages.length === 0) {
        messageList.innerHTML = `
            <div class="empty">
                <p>No SMS notifications have been captured yet.</p>
                <p>Trigger a notification using the <code>sms</code> channel to see it appear here.</p>
            </div>
        `;
    } else {
        let html = '';
        messages.forEach(message => {
            const limitedBody = message.body.replace(/[\n\r]/g, ' ').substring(0, 40) + (message.body.length > 40 ? '...' : '');
            const timeAgo = formatTimeAgo(new Date(message.timestamp));
            
            html += `
                <a href="{{ route('sms-catcher.show', '') }}/${message.id}" class="message">
                    <div><strong>${escapeHtml(message.to)}</strong></div>
                    <div>${escapeHtml(limitedBody)}</div>
                    <small>${timeAgo}</small>
                </a>
            `;
        });
        messageList.innerHTML = html;
    }
    
    // Update message count
    const countElement = document.querySelector('.panel-header strong');
    if (countElement) {
        const count = messages.length;
        countElement.textContent = `${count} message${count === 1 ? '' : 's'}`;
    }
    
    // Restore scroll position
    messageList.scrollTop = scrollTop;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTimeAgo(date) {
    const now = new Date();
    const diffMs = now - date;
    const diffSecs = Math.floor(diffMs / 1000);
    const diffMins = Math.floor(diffSecs / 60);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);
    
    if (diffDays > 0) {
        return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    } else if (diffHours > 0) {
        return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    } else if (diffMins > 0) {
        return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    } else {
        return 'Just now';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check localStorage for saved state
    const savedState = localStorage.getItem('sms-catcher-auto-update');
    if (savedState === 'true') {
        startAutoUpdate();
    }
});

// Clean up interval on page unload
window.addEventListener('beforeunload', function() {
    if (autoUpdateInterval) {
        clearInterval(autoUpdateInterval);
    }
});
</script>
@endpush

@section('content')
    <div class="panel">
        <div class="panel-header">
            <div>
                <strong>{{ $messages->count() }} message{{ $messages->count() === 1 ? '' : 's' }}</strong>
                <div class="meta">Only available in local/dev environments</div>
            </div>
            <div class="actions">
                <button class="btn" id="auto-update-toggle" onclick="toggleAutoUpdate()">
                    <span id="toggle-text">Enable auto-update</span>
                    <span id="toggle-status" style="display: none; color: #38bdf8; margin-left: 4px;">●</span>
                </button>
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
                    <div>{!! \SmsCatcher\Helpers\UrlProcessor::linkify(\Illuminate\Support\Str::limit(str_replace(["\n", "\r\n", "\r"], ' ', $message['body']), 40)) !!}</div>
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
