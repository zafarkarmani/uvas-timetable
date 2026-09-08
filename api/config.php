<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');

// Supabase publishable keys are designed for browser use when RLS is correctly configured.
// Never place a Supabase secret/service-role key in this file.
$config = [
    'supabaseUrl' => 'https://xnopqdfszkrwpgyxnlmq.supabase.co',
    'supabaseAnonKey' => 'sb_publishable_ZB-G_6r4bTMaqiGooLscRw_s0kbqCuY'
];

echo json_encode($config, JSON_UNESCAPED_SLASHES);
