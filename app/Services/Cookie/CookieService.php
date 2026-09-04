<?php

namespace App\Services\Cookie;

use App\Support\Consent;
use App\Support\Settings;

class CookieService
{
    /** @return array<string, mixed>|null */
    public function banner(): ?array
    {
        if (! Settings::bool('cookie.enabled')) {
            return null;
        }

        $values = Settings::merged('cookie');
        $policyUrl = route('cerez-politikasi');
        $policyLabel = (string) ($values['policy_label'] ?: 'çerez politikası');
        $description = e((string) $values['description']);
        $description = str_replace(
            '{policy}',
            '<a href="'.e($policyUrl).'">'.e($policyLabel).'</a>',
            $description,
        );

        return [
            'title' => $values['title'],
            'description' => $description,
            'accept_label' => $values['accept_label'],
            'reject_label' => $values['reject_label'],
            'customize_label' => $values['customize_label'],
            'save_label' => $values['save_label'],
            'necessary_title' => $values['necessary_title'],
            'necessary_description' => $values['necessary_description'],
            'functional_title' => $values['functional_title'],
            'functional_description' => $values['functional_description'],
            'analytics_title' => $values['analytics_title'],
            'analytics_description' => $values['analytics_description'],
            'marketing_title' => $values['marketing_title'],
            'marketing_description' => $values['marketing_description'],
            'consent' => Consent::snapshot(),
            'config' => [
                'cookie' => Consent::COOKIE,
                'version' => (int) ($values['version'] ?: 1),
                'days' => (int) ($values['lifetime_days'] ?: 180),
            ],
        ];
    }
}
