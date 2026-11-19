<?php

use App\Services\Sms\SmsCreditCalculator;

uses(Tests\TestCase::class);

test('gsm basic alphabet is the 128 character set and extended chars are double', function () {
    expect(mb_strlen(SmsCreditCalculator::gsmBasicCharacters()))->toBe(128)
        ->and(count(array_unique(mb_str_split(SmsCreditCalculator::gsmBasicCharacters()))))->toBe(128)
        ->and(mb_strlen(SmsCreditCalculator::gsmExtendedCharacters()))->toBe(10);
});

test('sms credits follow gsm-7 160 and 153 and unicode 70 and 67', function () {
    expect(SmsCreditCalculator::quote(str_repeat('a', 160))['segments'])->toBe(1)
        ->and(SmsCreditCalculator::quote(str_repeat('a', 161))['segments'])->toBe(2)
        ->and(SmsCreditCalculator::quote(str_repeat('a', 306))['segments'])->toBe(2)
        ->and(SmsCreditCalculator::quote(str_repeat('a', 307))['segments'])->toBe(3)
        ->and(SmsCreditCalculator::quote('')['segments'])->toBe(0);

    $extended = str_repeat('a', 159).'^';
    expect(SmsCreditCalculator::quote($extended)['encoding'])->toBe('gsm7')
        ->and(SmsCreditCalculator::quote($extended)['units'])->toBe(161)
        ->and(SmsCreditCalculator::quote($extended)['segments'])->toBe(2);

    expect(SmsCreditCalculator::quote(str_repeat('你', 70))['encoding'])->toBe('ucs2')
        ->and(SmsCreditCalculator::quote(str_repeat('你', 70))['segments'])->toBe(1)
        ->and(SmsCreditCalculator::quote(str_repeat('你', 71))['segments'])->toBe(2)
        ->and(SmsCreditCalculator::quote(str_repeat('你', 134))['segments'])->toBe(2)
        ->and(SmsCreditCalculator::quote(str_repeat('你', 135))['segments'])->toBe(3);
});

test('credits multiply by recipients and stay zero when send is off', function () {
    $message = str_repeat('a', 161);

    expect(SmsCreditCalculator::quote($message, 3)['credits'])->toBe(6)
        ->and(SmsCreditCalculator::previewSentence($message, 3, true))
        ->toBe('This message is 2 SMS. Credits to be deducted: 6')
        ->and(SmsCreditCalculator::previewSentence($message, 3, false))
        ->toBe('This message is 2 SMS. Credits to be deducted: 0');
});
