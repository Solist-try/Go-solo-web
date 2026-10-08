<?php

declare(strict_types=1);

require __DIR__ . '/site.php';
require __DIR__ . '/desk.php';
require __DIR__ . '/rooms.php';
require __DIR__ . '/talk.php';

dispatch();

function dispatch(): void
{
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    $path = request_path();
    foreach (route_table() as [$routeMethod, $pattern, $handler]) {
        if ($routeMethod !== $method) {
            continue;
        }
        $regex = '#^' . preg_replace('#\{([A-Za-z_]+)\}#', '(?P<$1>[^/]+)', $pattern) . '$#';
        if (!preg_match($regex, $path, $matches)) {
            continue;
        }
        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }
        $handler($params);
        return;
    }
    not_found();
}

function route_table(): array
{
    $post = static function (string $pattern, callable $handler): array {
        return ['POST', $pattern, static function (array $params) use ($handler): void {
            csrf_check();
            $handler($params);
        }];
    };

    return [
        ['GET', '/', 'page_home'],
        ['GET', '/about', 'page_about'],
        ['GET', '/contact', 'page_contact'],
        $post('/contact', 'page_contact_post'),
        ['GET', '/privacy', 'page_privacy'],
        ['GET', '/terms', 'page_terms'],
        ['GET', '/seeds', 'page_seeds'],
        $post('/seeds/skill-swap', 'page_skill_post'),
        ['GET', '/seeds/{slug}', 'page_seed'],
        $post('/seeds/{slug}/begin', 'page_seed_begin'),
        $post('/seeds/{slug}/open', 'page_seed_open'),
        ['GET', '/out-there', 'page_stories'],
        ['GET', '/out-there/new', 'page_story_form'],
        $post('/out-there', 'page_story_save'),
        ['GET', '/out-there/{id}', 'page_story'],
        $post('/out-there/{id}/reply', 'room_story_reply'),
        $post('/out-there/{id}/report', 'page_story_report'),
        ['GET', '/campfire', 'page_campfire'],
        ['GET', '/campfire/new', 'page_campfire_form'],
        $post('/campfire', 'page_campfire_save'),
        ['GET', '/campfire/{id}', 'page_campfire_show'],
        $post('/campfire/{id}/comment', 'page_campfire_comment'),
        $post('/campfire/{id}/report', 'page_campfire_report'),
        $post('/comments/{id}/report', 'page_comment_report'),
        $post('/comments/{id}/delete', 'room_comment_delete'),
        ['GET', '/waypoints', 'page_waypoints'],
        ['GET', '/waypoints/{slug}/discussions/{id}', 'room_discussion'],
        $post('/waypoints/{slug}/discussions/{id}/reply', 'room_discussion_reply'),
        $post('/waypoints/{slug}/discussions/{id}/delete', 'room_discussion_delete'),
        $post('/waypoints/{slug}/discussions', 'room_discussion_save'),
        ['GET', '/waypoints/{slug}', 'page_waypoint'],
        $post('/waypoints/{slug}/join', 'page_waypoint_join'),
        $post('/waypoints/{slug}/leave', 'page_waypoint_leave'),
        ['GET', '/reading', 'page_reading'],
        ['GET', '/reading/{slug}', 'page_reading_show'],
        ['GET', '/join', 'page_join_form'],
        $post('/join', 'page_join'),
        ['GET', '/login', 'page_login_form'],
        $post('/login', 'page_login'),
        $post('/logout', 'page_logout'),
        ['GET', '/forgot', 'page_forgot_form'],
        $post('/forgot', 'page_forgot'),
        ['GET', '/reset', 'page_reset_form'],
        $post('/reset', 'page_reset'),
        ['GET', '/profile', 'page_profile'],
        ['GET', '/profile/edit', 'page_profile_edit'],
        $post('/profile', 'page_profile_save'),
        ['GET', '/members/{id}', 'page_member'],
        $post('/members/{id}/report', 'page_profile_report'),
        $post('/growing', 'page_growing'),
        $post('/planted/remove', 'page_planted_remove'),
        $post('/notes', 'page_notes'),
        $post('/conversations', 'page_talk_start'),
        ['GET', '/conversations/{id}', 'page_talk'],
        $post('/conversations/{id}', 'page_talk_send'),
        $post('/conversations/{id}/report', 'page_talk_report'),
        ['GET', '/account', 'page_account'],
        $post('/account', 'page_account_save'),
        $post('/account/delete', 'page_account_delete'),
        ['GET', '/notices', 'page_notices'],
        ['GET', '/steward', 'desk_overview'],
        ['GET', '/steward/members', 'desk_members'],
        ['GET', '/steward/members/{id}', 'desk_member'],
        $post('/steward/members/{id}', 'desk_member_save'),
        $post('/steward/members/{id}/delete', 'desk_member_delete'),
        ['GET', '/steward/seeds', 'desk_seeds'],
        $post('/steward/seeds/save', 'desk_seed_save'),
        ['GET', '/steward/same', 'desk_same'],
        $post('/steward/same/suggest', 'desk_same_suggest'),
        $post('/steward/same/{id}', 'desk_same_status'),
        ['GET', '/steward/skills', 'desk_skills'],
        $post('/steward/skills/connect', 'desk_skill_connect'),
        $post('/steward/skills/archive', 'desk_skill_archive'),
        ['GET', '/steward/out-there', 'desk_stories'],
        $post('/steward/stories/{id}', 'desk_story_action'),
        ['GET', '/steward/campfire', 'desk_campfire'],
        $post('/steward/campfire/{id}', 'desk_campfire_action'),
        $post('/steward/comments/{id}', 'desk_comment_action'),
        ['GET', '/steward/waypoints', 'desk_waypoints'],
        ['GET', '/steward/waypoints/new', 'desk_waypoint_form'],
        ['GET', '/steward/waypoints/{id}', 'desk_waypoint_form'],
        $post('/steward/waypoints/save', 'desk_waypoint_save'),
        $post('/steward/waypoints/readings', 'desk_waypoint_readings'),
        $post('/steward/waypoint-posts/{id}', 'desk_waypoint_post_action'),
        ['GET', '/steward/reading', 'desk_reading'],
        ['GET', '/steward/reading/new', 'desk_reading_form'],
        ['GET', '/steward/reading/{id}', 'desk_reading_form'],
        $post('/steward/reading/save', 'desk_reading_save'),
        $post('/steward/reading/delete', 'desk_reading_delete'),
        $post('/steward/reading/category', 'desk_reading_category'),
        $post('/steward/reading/category-delete', 'desk_reading_category_delete'),
        ['GET', '/steward/messages', 'desk_messages'],
        ['GET', '/steward/messages/{id}', 'desk_message'],
        $post('/steward/messages/{id}', 'desk_message_save'),
        ['GET', '/steward/conversations', 'desk_conversations'],
        ['GET', '/steward/conversations/{id}', 'desk_conversation'],
        $post('/steward/conversations/{id}', 'desk_conversation_action'),
        ['GET', '/steward/reports', 'desk_reports'],
        $post('/steward/reports/{id}', 'desk_report_action'),
        ['GET', '/steward/content', 'desk_content'],
        $post('/steward/content', 'desk_content_save'),
        ['GET', '/steward/settings', 'desk_settings'],
        $post('/steward/settings', 'desk_settings_save'),
    ];
}
