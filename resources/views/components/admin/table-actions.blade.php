@props([
    'actions' => [],
    'label' => 'Actions',
    'align' => 'end',
])

@php
    $visibleActions = collect($actions)
        ->filter(fn ($action) => ($action['show'] ?? true) !== false)
        ->values();

    $menuAlignClass = $align === 'start' ? 'dropdown-menu-start' : 'dropdown-menu-end';
@endphp

@if ($visibleActions->isNotEmpty())
    <div {{ $attributes->class(['dropdown d-inline-block']) }}>
        <button
            type="button"
            class="btn btn-sm btn-outline-secondary dropdown-toggle"
            data-bs-toggle="dropdown"
            data-bs-popper-config='{"strategy":"fixed"}'
            aria-expanded="false"
            aria-label="{{ $label }}"
        >
            {{ $label }}
        </button>
        <ul class="dropdown-menu {{ $menuAlignClass }}">
            @foreach ($visibleActions as $action)
                @php
                    $type = $action['type'] ?? 'link';
                    $icon = $action['icon'] ?? null;
                    $itemLabel = $action['label'] ?? 'Action';
                    $danger = (bool) ($action['danger'] ?? false);
                    $disabled = (bool) ($action['disabled'] ?? false);
                    $itemClass = trim('dropdown-item' . ($danger ? ' text-danger' : '') . ' ' . ($action['class'] ?? ''));
                    $attrs = $action['attributes'] ?? [];
                @endphp

                @switch ($type)
                    @case ('divider')
                        <li><hr class="dropdown-divider"></li>
                        @break

                    @case ('link')
                        <li>
                            <a
                                href="{{ $action['url'] ?? '#' }}"
                                class="{{ $itemClass }}{{ $disabled ? ' disabled' : '' }}"
                                @if ($disabled) aria-disabled="true" tabindex="-1" @endif
                                @foreach ($attrs as $attr => $value)
                                    @if (is_bool($value))
                                        @if ($value) {{ $attr }} @endif
                                    @elseif ($value !== null)
                                        {{ $attr }}="{{ $value }}"
                                    @endif
                                @endforeach
                            >
                                @if ($icon)
                                    <i class="{{ $icon }} me-2"></i>
                                @endif
                                {{ $itemLabel }}
                            </a>
                        </li>
                        @break

                    @case ('button')
                        <li>
                            <button
                                type="{{ $action['buttonType'] ?? 'button' }}"
                                class="{{ $itemClass }}"
                                @if ($disabled) disabled @endif
                                @foreach ($attrs as $attr => $value)
                                    @if (is_bool($value))
                                        @if ($value) {{ $attr }} @endif
                                    @elseif ($value !== null)
                                        {{ $attr }}="{{ $value }}"
                                    @endif
                                @endforeach
                            >
                                @if ($icon)
                                    <i class="{{ $icon }} me-2"></i>
                                @endif
                                {{ $itemLabel }}
                            </button>
                        </li>
                        @break

                    @case ('form')
                        <li>
                            <form
                                action="{{ $action['url'] }}"
                                method="POST"
                                @if (! empty($action['confirm']))
                                    onsubmit="return confirm(@json($action['confirm']));"
                                @endif
                                @foreach (($action['formAttributes'] ?? []) as $attr => $value)
                                    @if (is_bool($value))
                                        @if ($value) {{ $attr }} @endif
                                    @elseif ($value !== null)
                                        {{ $attr }}="{{ $value }}"
                                    @endif
                                @endforeach
                            >
                                @csrf
                                @if (($action['method'] ?? 'POST') !== 'POST')
                                    @method($action['method'])
                                @endif
                                @foreach (($action['hidden'] ?? []) as $name => $value)
                                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                                @endforeach
                                <button
                                    type="submit"
                                    class="{{ $itemClass }}"
                                    @if ($disabled) disabled @endif
                                >
                                    @if ($icon)
                                        <i class="{{ $icon }} me-2"></i>
                                    @endif
                                    {{ $itemLabel }}
                                </button>
                            </form>
                        </li>
                        @break

                    @default
                        @php
                            throw new \InvalidArgumentException("Unsupported table action type [{$type}].");
                        @endphp
                @endswitch
            @endforeach
        </ul>
    </div>
@endif
