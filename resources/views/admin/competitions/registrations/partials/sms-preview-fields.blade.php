@php
    $prefix = $prefix ?? 'sms';
    $canSendSms = (bool) ($canSendSms ?? false);
    $phoneDisplay = $phoneDisplay ?? null;
    $message = $message ?? '';
    $senderDefault = $senderDefault ?? 'ADILISHA';
    $senderOptions = $senderOptions ?? ['ADILISHA', 'ADILISHAXR', 'STEMLABS'];
    $checkboxId = $prefix.'_send_sms';
    $messageId = $prefix.'_sms_message';
    $senderId = $prefix.'_sms_sender_id';
@endphp

<div class="border rounded p-3 bg-light">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <h6 class="mb-0">SMS notification</h6>
        <span class="small text-muted">
            To:
            @if($phoneDisplay)
                <strong>{{ $phoneDisplay }}</strong>
            @else
                <strong class="text-danger">No valid teacher phone</strong>
            @endif
        </span>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" name="send_sms" value="0">
        <input
            class="form-check-input"
            type="checkbox"
            name="send_sms"
            id="{{ $checkboxId }}"
            value="1"
            @checked(old('send_sms', $canSendSms ? '1' : '0') == '1' && $canSendSms)
            @disabled(! $canSendSms)
        >
        <label class="form-check-label" for="{{ $checkboxId }}">
            Send SMS with this message
        </label>
    </div>

    @if(! $canSendSms)
        <p class="small text-muted mb-2">Add a valid Tanzania teacher phone on the registration to enable SMS.</p>
    @endif

    <div class="mb-3">
        <label class="form-label" for="{{ $senderId }}">Sender ID</label>
        <select class="form-select" name="sms_sender_id" id="{{ $senderId }}" @disabled(! $canSendSms)>
            @foreach ($senderOptions as $sender)
                <option value="{{ $sender }}" @selected(old('sms_sender_id', $senderDefault) === $sender)>{{ $sender }}</option>
            @endforeach
        </select>
        @unless($canSendSms)
            <input type="hidden" name="sms_sender_id" value="{{ $senderDefault }}">
        @endunless
    </div>

    <div class="mb-0">
        <label class="form-label" for="{{ $messageId }}">Message (editable)</label>
        <textarea
            class="form-control"
            name="sms_message"
            id="{{ $messageId }}"
            rows="4"
            maxlength="480"
            @disabled(! $canSendSms)
        >{{ $message }}</textarea>
        @unless($canSendSms)
            <input type="hidden" name="sms_message" value="{{ $message }}">
        @endunless
        <div class="form-text">Keep it short. Long messages use more SMS credits.</div>
    </div>
</div>
