@extends('sms-catcher::layout')

@push('head')
<style>
    /* Device Selector Styles */
    .device-selector select {
        background: rgba(148, 163, 184, 0.15);
        border: 1px solid var(--border);
        color: var(--text);
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
        cursor: pointer;
        font-size: 0.85rem;
        min-width: 140px;
    }

    .device-selector select:hover {
        background: rgba(56, 189, 248, 0.15);
    }

    /* Base phone shell - will be overridden by device-specific classes */
    .phone-shell {
        transition: all 0.3s ease;
        margin: 2rem auto;
    }

    /* iPhone 14 - 6.1" display, 390×844 logical resolution */
    .phone-shell[data-device="iphone-14"] {
        width: 320px;
        height: 640px;
        border: 14px solid #1f2937;
        border-radius: 45px;
        padding: 2rem 1.25rem;
        background: linear-gradient(145deg, #111827, #374151);
        box-shadow: 0 25px 50px rgba(15, 23, 42, 0.5);
    }

    .phone-shell[data-device="iphone-14"] .phone-screen {
        background: #000;
        border-radius: 32px;
        padding: 2rem 1.25rem;
        min-height: 580px;
        position: relative;
    }

    .phone-shell[data-device="iphone-14"] .phone-screen::before {
        content: '';
        position: absolute;
        top: 0.75rem;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 28px;
        background: #000;
        border-radius: 14px;
        z-index: 10;
    }

    /* iPhone SE - 4.7" display, 375×667 logical resolution */
    .phone-shell[data-device="iphone-se"] {
        width: 280px;
        height: 520px;
        border: 12px solid #1f2937;
        border-radius: 32px;
        padding: 1.5rem 1rem;
        background: linear-gradient(145deg, #111827, #374151);
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.4);
    }

    .phone-shell[data-device="iphone-se"] .phone-screen {
        background: #000;
        border-radius: 22px;
        padding: 1.5rem 1rem;
        min-height: 470px;
    }

    /* Samsung Galaxy S24 - 6.2" display, 1080×2340 resolution */
    .phone-shell[data-device="samsung-s24"] {
        width: 340px;
        height: 680px;
        border: 10px solid #2563eb;
        border-radius: 38px;
        padding: 2.25rem 1.5rem;
        background: linear-gradient(145deg, #1e40af, #3b82f6);
        box-shadow: 0 30px 60px rgba(37, 99, 235, 0.3);
    }

    .phone-shell[data-device="samsung-s24"] .phone-screen {
        background: #0f172a;
        border-radius: 28px;
        padding: 2rem 1.25rem;
        min-height: 620px;
        position: relative;
    }

    .phone-shell[data-device="samsung-s24"] .phone-screen::before {
        content: '';
        position: absolute;
        top: 0.5rem;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 6px;
        background: #334155;
        border-radius: 3px;
        z-index: 10;
    }

    /* Google Pixel 8 - 6.2" display, 1080×2400 resolution */
    .phone-shell[data-device="pixel-8"] {
        width: 330px;
        height: 670px;
        border: 11px solid #16a34a;
        border-radius: 35px;
        padding: 2rem 1.25rem;
        background: linear-gradient(145deg, #15803d, #22c55e);
        box-shadow: 0 28px 55px rgba(22, 163, 74, 0.3);
    }

    .phone-shell[data-device="pixel-8"] .phone-screen {
        background: #0c0a09;
        border-radius: 25px;
        padding: 1.75rem 1rem;
        min-height: 610px;
        position: relative;
    }

    .phone-shell[data-device="pixel-8"] .phone-screen::before {
        content: '';
        position: absolute;
        top: 0.75rem;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 24px;
        background: #0c0a09;
        border-radius: 12px;
        z-index: 10;
    }

    /* Generic device (original style) */
    .phone-shell[data-device="generic"] {
        width: 280px;
        border: 12px solid #111827;
        border-radius: 36px;
        padding: 1.5rem 1rem;
        background: linear-gradient(145deg, #0f172a, #1e293b);
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.4);
    }

    .phone-shell[data-device="generic"] .phone-screen {
        background: #0f172a;
        border-radius: 24px;
        padding: 1.5rem 1rem;
        min-height: 360px;
    }

    /* Responsive adjustments */
    @media (max-width: 960px) {
        .phone-shell[data-device="iphone-14"] {
            width: 280px;
            height: 560px;
            padding: 1.5rem 1rem;
        }

        .phone-shell[data-device="iphone-14"] .phone-screen {
            min-height: 500px;
            padding: 1.5rem 1rem;
        }

        .phone-shell[data-device="samsung-s24"] {
            width: 300px;
            height: 600px;
            padding: 1.75rem 1.25rem;
        }

        .phone-shell[data-device="samsung-s24"] .phone-screen {
            min-height: 540px;
            padding: 1.5rem 1rem;
        }

        .phone-shell[data-device="pixel-8"] {
            width: 290px;
            height: 590px;
            padding: 1.5rem 1rem;
        }

        .phone-shell[data-device="pixel-8"] .phone-screen {
            min-height: 530px;
            padding: 1.5rem 1rem;
        }

        .phone-shell[data-device="iphone-se"] {
            width: 250px;
            height: 480px;
            padding: 1.25rem 0.75rem;
        }

        .phone-shell[data-device="iphone-se"] .phone-screen {
            min-height: 430px;
            padding: 1.25rem 0.75rem;
        }
    }
</style>

<script>
// Valid device options
const validDevices = ['iphone-14', 'iphone-se', 'samsung-s24', 'pixel-8', 'generic'];
const defaultDevice = 'generic';

// Device switching functionality
function changeDevice(deviceType) {
    if (!validDevices.includes(deviceType)) {
        deviceType = defaultDevice;
    }
    
    const phoneShell = document.getElementById('phoneShell');
    if (phoneShell) {
        phoneShell.setAttribute('data-device', deviceType);
        
        // Save preference to localStorage
        localStorage.setItem('sms-catcher-device-preference', deviceType);
    }
}

// Initialize device selection on page load
document.addEventListener('DOMContentLoaded', function() {
    const selector = document.getElementById('deviceSelector');
    const phoneShell = document.getElementById('phoneShell');
    
    if (!selector || !phoneShell) return;
    
    // Add event listener to selector
    selector.addEventListener('change', function() {
        changeDevice(this.value);
    });
    
    // Load saved device preference or use default
    let selectedDevice = localStorage.getItem('sms-catcher-device-preference') || defaultDevice;
    
    // Validate saved device is still valid
    if (!validDevices.includes(selectedDevice)) {
        selectedDevice = defaultDevice;
        localStorage.setItem('sms-catcher-device-preference', selectedDevice);
    }
    
    // Set both selector and phone shell to the validated device
    selector.value = selectedDevice;
    phoneShell.setAttribute('data-device', selectedDevice);
});
</script>
@endpush

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
                        <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                    <button class="btn btn-primary" href="{{ route('sms-catcher.index') }}">Back</button>
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
                <div>
                    <strong>Preview</strong>
                    <div class="meta">Select device to preview message appearance</div>
                </div>
                <div class="device-selector">
                    <select id="deviceSelector" class="btn">
                        <option value="iphone-14">iPhone 14</option>
                        <option value="iphone-se">iPhone SE</option>
                        <option value="samsung-s24">Samsung Galaxy S24</option>
                        <option value="pixel-8">Google Pixel 8</option>
                        <option value="generic">Generic</option>
                    </select>
                </div>
            </div>
            <div class="phone-shell" id="phoneShell" data-device="generic">
                <div class="phone-screen">
                    <div class="bubble">{!! nl2br(\SmsCatcher\Helpers\UrlProcessor::linkify($message['body'])) !!}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
