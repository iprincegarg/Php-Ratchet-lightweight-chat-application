<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">
    <link href="<?php echo Registry::load('config')->site_url ?>assets/thirdparty/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo Registry::load('config')->site_url ?>assets/css/common/messages_table.css">
    <link rel="stylesheet" href="<?php echo Registry::load('config')->site_url ?>assets/css/chat_page/emojis.css">

    <title><?php echo $conversation; ?></title>
</head>
<body>


    <div class="content">

        <div class="container">
            <h2 class="mb-5"><?php echo $conversation; ?></h2>


            <div class="table-responsive">

                <table class="table table-striped custom-table">
                    <thead>
                        <tr>

                            <th scope="col"><?php echo Registry::load('strings')->message_id ?></th>
                            <th scope="col"><?php echo Registry::load('strings')->posted_by ?></th>
                            <th scope="col"><?php echo Registry::load('strings')->content ?></th>
                            <th scope="col"><?php echo Registry::load('strings')->attachments ?></th>
                            <th scope="col"><?php echo Registry::load('strings')->timestamp ?></th>
                            <th scope="col"><?php echo Registry::load('strings')->reply_to ?></th>
                        </tr>
                    </thead>
                    <tbody>


                        <?php
                        foreach ($messages as $message) {
                            $date = array();
                            $date['date'] = $message['created_on'];
                            $date['auto_format'] = true;
                            $date['include_time'] = true;
                            $date['timezone'] = Registry::load('current_user')->time_zone;
                            $created_on = get_date($date);
                            if (empty($message['parent_message_id'])) {
                                $message['parent_message_id'] = Registry::load('strings')->none;
                            }
                            ?>
                            <tr scope="row">



                                <td><?php output($message['message_id']); ?></td>
                                <td><a href="#"><?php output($message['display_name']); ?></a></td>

                                <?php if (!empty($message['filtered_message'])) {
                                    ?>
                                    <td><?php echo $message['filtered_message']; ?></td>
                                    <?php
                                } else if ($message['attachment_type'] === 'gif') {
                                    ?>
                                    <td><?php echo Registry::load('strings')->gif ?></td>
                                    <?php
                                } else if ($message['attachment_type'] === 'sticker') {
                                    ?>
                                    <td><?php echo Registry::load('strings')->sticker ?></td>
                                    <?php
                                } else if ($message['attachment_type'] === 'audio_message') {
                                    ?>
                                    <td><?php echo Registry::load('strings')->audio_message ?></td>
                                    <?php
                                } else {
                                    ?>
                                    <td><?php echo Registry::load('strings')->attachments ?></td>
                                    <?php
                                } ?>


                                <?php if ($message['attachment_type'] === 'gif') {
                                    $attachment = json_decode($message['attachments']);
                                    $attachment = $attachment->gif_url;
                                    ?>
                                    <td class="attachment"><?php output($attachment) ?></td>
                                    <?php
                                } else if ($message['attachment_type'] === 'sticker') {
                                    $attachment = json_decode($message['attachments']);
                                    $attachment = $attachment->sticker;
                                    ?>
                                    <td class="attachment"><?php output($attachment) ?></td>
                                    <?php
                                } else if ($message['attachment_type'] === 'audio_message') {
                                    ?>
                                    <td class="attachment"><?php echo Registry::load('strings')->audio_message ?></td>
                                    <?php
                                } else if (!empty($message['attachment_type']) && $message['attachment_type'] !== 'url_meta') {
                                    $attachments = json_decode($message['attachments']);
                                    ?>
                                    <td class="attachment"><?php foreach ($attachments as $attachment) {

                                        if (!isset($attachment->name)) {
                                            $attachment = new stdClass();
                                            $attachment->name = '[Attachment]';
                                        }

                                        echo '['.$attachment->name.'] ';
                                    } ?></td>
                                    <?php
                                } else {
                                    ?>
                                    <td><?php echo Registry::load('strings')->none ?></td>
                                    <?php
                                } ?>


                                <td><?php output($created_on['date'].' - '.$created_on['time']); ?></td>
                                <td><?php output($message['parent_message_id']); ?></td>

                            </tr>
                            <?php
                        } ?>


                    </tbody>
                </table>
            </div>


        </div>

    </div>

    <script src="<?php echo Registry::load('config')->site_url ?>assets/thirdparty/bootstrap/bootstrap.bundle.min.js"></script>

</body>
</html>