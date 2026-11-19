<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPackage extends Model
{
    protected $fillable = [
        'slug',
        'tier_key',
        'name',
        'description',
        'price_heading',
        'price_subheading',
        'price_compare',
        'price_amount_tzs',
        'billing_period_days',
        'requires_payment',
        'badge_label',
        'cta_label',
        'cta_action',
        'features',
        'linked_title',
        'linked_description',
        'linked_features',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'linked_features' => 'array',
            'is_active' => 'boolean',
            'requires_payment' => 'boolean',
            'price_amount_tzs' => 'integer',
            'billing_period_days' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function resolvedCtaAction(bool $isUnlinkedStudent = false): string
    {
        if ($this->slug === 'public-school') {
            return $isUnlinkedStudent ? 'public-school-student' : 'public-school-teacher';
        }

        return $this->cta_action;
    }

    public function linkedFeatureList(): array
    {
        if (! empty($this->linked_features)) {
            return $this->linked_features;
        }

        return $this->features ?? [];
    }
}
