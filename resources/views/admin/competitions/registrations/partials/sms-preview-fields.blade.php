@php
    $prefix = $prefix ?? 'sms';
    $canSendSms = (bool) ($canSendSms ?? false);
    $phoneDisplay = $phoneDisplay ?? null;
    $message = $message ?? '';
    $senderDefault = $senderDefault ?? 'ADILISHA';
    $senderOptions = $senderOptions ?? ['ADILISHA', 'ADILISHAXR', 'STEMLABS'];
    $recipientCount = (int) ($recipientCount ?? ($canSendSms ? 1 : 0));
    $checkboxId = $prefix.'_send_sms';
    $messageId = $prefix.'_sms_message';
    $senderId = $prefix.'_sms_sender_id';
    $sendChecked = old('send_sms', $canSendSms ? '1' : '0') == '1' && $canSendSms;
    $creditSentence = \App\Services\Sms\SmsCreditCalculator::previewSentence((string) $message, $recipientCount, $sendChecked);
    $creditQuote = \App\Services\Sms\SmsCreditCalculator::quote((string) $message, 1);
@endphp

<div class="border rounded p-3 bg-light" data-sms-credit-root data-recipients="{{ $recipientCount }}">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <h6 class="mb-0">SMS notification</h6>
        <span>
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
            @checked($sendChecked)
            @disabled(! $canSendSms)
        >
        <label class="form-check-label" for="{{ $checkboxId }}">
            Send SMS with this message
        </label>
    </div>

    @if(! $canSendSms)
        <p class="text-muted mb-2">Add a valid Tanzania teacher phone on the registration to enable SMS. No SMS credits will be deducted.</p>
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

    <div class="mb-2">
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
    </div>

    <div class="alert alert-warning mb-0" role="status">
        <div class="fw-semibold" data-sms-credit-text>{{ $creditSentence }}</div>
        <div class="mt-1" data-sms-credit-meta>
            {{ $creditQuote['encoding'] === 'ucs2' ? 'Unicode' : 'GSM-7' }}
            · {{ $creditQuote['units'] }} units
            @if($recipientCount > 1)
                · {{ $recipientCount }} recipients
            @endif
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                const rules = @json(\App\Services\Sms\SmsCreditCalculator::clientRules());

                function measure(message) {
                    const chars = Array.from(message);
                    const basic = new Set(Array.from(rules.gsmBasic));
                    const extended = new Set(Array.from(rules.gsmExtended));
                    let gsmUnits = 0;

                    for (const ch of chars) {
                        if (basic.has(ch)) {
                            gsmUnits += 1;
                            continue;
                        }
                        if (extended.has(ch)) {
                            gsmUnits += 2;
                            continue;
                        }

                        let ucsUnits = 0;
                        for (const unicodeChar of chars) {
                            const codePoint = unicodeChar.codePointAt(0);
                            ucsUnits += codePoint > 0xffff ? 2 : 1;
                        }

                        return { encoding: 'ucs2', units: ucsUnits };
                    }

                    return { encoding: 'gsm7', units: gsmUnits };
                }

                function segmentCount(encoding, units) {
                    if (units <= 0) {
                        return 0;
                    }

                    const single = encoding === 'gsm7' ? rules.gsmSingle : rules.unicodeSingle;
                    const concat = encoding === 'gsm7' ? rules.gsmConcat : rules.unicodeConcat;

                    if (units <= single) {
                        return 1;
                    }

                    return Math.ceil(units / concat);
                }

                function describe(message, recipients, willSend) {
                    const measured = measure(message);
                    const segments = segmentCount(measured.encoding, measured.units);
                    const credits = willSend ? segments * Math.max(0, recipients) : 0;

                    return {
                        text: 'This message is ' + segments + ' SMS. Credits to be deducted: ' + credits,
                        meta: (measured.encoding === 'gsm7' ? 'GSM-7' : 'Unicode') + ' · ' + measured.units + ' units'
                            + (recipients > 1 ? ' · ' + recipients + ' recipients' : ''),
                    };
                }

                function bindRoot(root) {
                    const textarea = root.querySelector('textarea[name="sms_message"]');
                    const checkbox = root.querySelector('input[type="checkbox"][name="send_sms"]');
                    const text = root.querySelector('[data-sms-credit-text]');
                    const meta = root.querySelector('[data-sms-credit-meta]');
                    if (!textarea || !text) {
                        return;
                    }

                    const recipients = parseInt(root.dataset.recipients || '0', 10) || 0;

                    function update() {
                        const willSend = checkbox ? checkbox.checked && !checkbox.disabled : recipients > 0;
                        const result = describe(textarea.value || '', recipients, willSend);
                        text.textContent = result.text;
                        if (meta) {
                            meta.textContent = result.meta;
                        }
                    }

                    textarea.addEventListener('input', update);
                    if (checkbox) {
                        checkbox.addEventListener('change', update);
                    }
                    update();
                }

                function boot() {
                    document.querySelectorAll('[data-sms-credit-root]').forEach(bindRoot);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', boot);
                } else {
                    boot();
                }
            })();
        </script>
    @endpush
@endonce
