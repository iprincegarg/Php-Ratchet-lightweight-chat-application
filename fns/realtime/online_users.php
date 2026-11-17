<?php

$data["recent_online_user_id"] = filter_var($data["recent_online_user_id"], FILTER_SANITIZE_NUMBER_INT);

if (empty($data["recent_online_user_id"])) {
    $data["recent_online_user_id"] = 0;
}

if (empty($data["recent_online_user_online_status"])) {
    $data["recent_online_user_online_status"] = 0;
}

update_online_statuses();

$columns = $join = $where = null;
$hide_current_user = false;


$columns = ['site_users.user_id', 'site_users.online_status'];

$where["site_users.online_status[!]"] = 0;

if ($hide_current_user) {
    $where["site_users.user_id[!]"] = Registry::load('current_user')->id;
}

$where["ORDER"] = ["site_users.last_login_session" => "DESC"];
$where["LIMIT"] = 1;

$recent_online_user_id = DB::connect()->select('site_users', $columns, $where);

if (isset($recent_online_user_id[0])) {

    $recent_online_user_online_status = $recent_online_user_id[0]['online_status'];
    $recent_online_user_id = $recent_online_user_id[0]['user_id'];

    if ((int)$recent_online_user_id !== (int)$data["recent_online_user_id"] || (int)$recent_online_user_online_status !== (int)$data["recent_online_user_online_status"]) {
        $result['recent_online_user_id'] = $recent_online_user_id;
        $result['recent_online_user_online_status'] = $recent_online_user_online_status;
        $escape = true;
    }
} else {
    if ((int)$data["recent_online_user_id"] !== 0) {
        $result['recent_online_user_id'] = 0;
        $result['recent_online_user_online_status'] = 0;
        $escape = true;
    }
}