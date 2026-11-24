<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Subscription Packages</h5>
                    <p class="text-muted mb-0">View and edit platform pricing. Yearly Individual is a checkout SKU (365 days) — clients toggle Monthly/Yearly on the Individual card rather than seeing a fourth dashboard card.</p>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card tbl-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Tier</th>
                                    <th>Price</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($packages as $package)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-semibold">
                                            {{ $package->name }}
                                            @if ($package->slug === 'individual-yearly')
                                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle ms-1">Yearly checkout</span>
                                            @endif
                                        </td>
                                        <td><code>{{ $package->slug }}</code></td>
                                        <td><code>{{ $package->tier_key }}</code></td>
                                        <td>
                                            @if ($package->price_compare)
                                                <del class="text-muted">{{ $package->price_compare }}</del>
                                            @endif
                                            {{ $package->price_heading }}
                                            @if ($package->price_subheading)
                                                <span class="text-muted">{{ $package->price_subheading }}</span>
                                            @endif
                                            @if ($package->price_amount_tzs)
                                                <div class="small text-muted">Charge {{ number_format($package->price_amount_tzs) }} TZS</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ((int) $package->billing_period_days > 0)
                                                {{ $package->billing_period_days }} days
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $package->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $package->is_active ? 'Active' : 'Disabled' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <x-admin.table-actions :actions="[
                                                [
                                                    'type' => 'link',
                                                    'label' => 'Edit',
                                                    'icon' => 'ti ti-edit',
                                                    'url' => route('admin.subscription-packages.edit', $package),
                                                ],
                                            ]" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            No subscription packages found. Run the database seeder to create the default packages.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
