<?php

declare(strict_types=1);

function copy_defaults(): array
{
    static $all = null;
    if ($all === null) {
        $json = file_get_contents(__DIR__ . '/defaults.json');
        $decoded = json_decode($json ?: '', true);
        $all = is_array($decoded) ? $decoded : [];
    }
    return $all;
}

function site_text(string $key): string
{
    $defaults = copy_defaults();
    $default = isset($defaults[$key]) ? (string) $defaults[$key] : '';
    return setting($key, $default);
}

function content_groups(): array
{
    return [
        'Homepage' => [
            'hero_title' => 'Hero title',
            'hero_subhead' => 'Second line',
            'hero_support' => 'Supporting line',
            'hero_primary' => 'Primary button',
            'hero_secondary' => 'Secondary button',
            'hero_philosophy' => 'Philosophy line',
            'freedom_heading' => 'Freedom heading',
            'freedom_body' => 'Freedom text',
            'how_title' => 'How it works heading',
            'home_seeds_title' => 'Seeds heading',
            'home_seeds_line' => 'Seeds line',
        ],
        'About' => [
            'about_heading' => 'Opening heading',
            'about_intro' => 'Opening',
            'about_founder_heading' => 'Founder heading',
            'about_founder_body' => 'Founder story',
            'about_belief_heading' => 'Belief heading',
            'about_belief_body' => 'Belief',
            'about_hello_heading' => 'Hello heading',
            'about_hello_body' => 'Hello note',
            'about_close_heading' => 'Closing heading',
            'about_close_body' => 'Closing line',
        ],
        'Contact' => [
            'contact_headline' => 'Headline',
            'contact_card_title' => 'Card title',
            'contact_body' => 'Card note',
        ],
        'Seeds page' => [
            'seeds_title' => 'Title',
            'seeds_line' => 'Line under the title',
            'seeds_support' => 'Support line',
            'seeds_accountability' => 'Accountability line',
            'seeds_learning' => 'Learning line',
            'seeds_small' => 'Small line',
        ],
        'Empty rooms' => [
            'out_there_empty' => 'Out There explanation',
            'campfire_waiting' => 'Campfire line',
            'campfire_empty' => 'Campfire explanation',
        ],
        'Footer' => [
            'footer_line' => 'Footer line',
        ],
        'Navigation' => [
            'nav_about' => 'About',
            'nav_seeds' => 'Seeds',
            'nav_out_there' => 'Out There',
            'nav_campfire' => 'Campfire',
            'nav_waypoints' => 'Waypoints',
            'nav_reading' => 'Reading Room',
            'nav_contact' => 'Contact',
            'nav_join' => 'Join',
            'nav_login' => 'Log In',
        ],
    ];
}

function settings_text_keys(): array
{
    return [
        'site_title' => 'Site title',
        'tagline' => 'Tagline',
        'logo_text' => 'Logo words',
        'founder_name' => 'Founder name',
        'founder_email' => 'Founder email',
        'email_reset_subject' => 'Password reset subject',
        'email_reset_body' => 'Password reset note',
    ];
}

function color_keys(): array
{
    return [
        'color_background' => 'Background',
        'color_ink' => 'Ink',
        'color_soft' => 'Soft text',
        'color_sage' => 'Sage',
        'color_clay' => 'Clay',
        'color_mist' => 'Mist',
        'color_card' => 'Cards',
    ];
}

function image_keys(): array
{
    return [
        'hero_image' => 'Homepage walk',
        'freedom_image' => 'Homepage at home',
        'out_there_image' => 'Out There',
        'campfire_image' => 'Campfire',
        'logo_image' => 'Small logo picture',
    ];
}

function long_setting(string $key, string $value): bool
{
    return str_contains($value, "\n") || str_contains($key, 'body') || str_contains($key, 'intro') || strlen($value) > 140;
}
