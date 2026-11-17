<?php

$permalink = get_url(['remove' => 'entry/']);
$alert_message = $alert_type = null;
$slug = htmlspecialchars(preg_replace('/\\?.*|\//', '', strtok($permalink, '/')));

if ($slug === 'access_token') {
    include 'layouts/entry_page/verify_access_token.php';
} else if ($slug === 'verify_email_address') {
    include 'layouts/entry_page/verify_email_address.php';
} else if ($slug === 'social_login') {
    include 'layouts/entry_page/social_login.php';
}

if (isset($_GET['login_session_id']) && isset($_GET['access_code']) && isset($_GET['session_time_stamp'])) {
    include 'layouts/entry_page/login_session.php';
}

if (Registry::load('current_user')->logged_in) {
    if (isset($_GET['redirect'])) {
        redirect($_GET['redirect']);
    } else {
        redirect('');
    }
} else {
    if (isset(Registry::load('settings')->custom_login_url) && !empty(trim(Registry::load('settings')->custom_login_url))) {
        redirect(Registry::load('settings')->custom_login_url);
    }
}
?>