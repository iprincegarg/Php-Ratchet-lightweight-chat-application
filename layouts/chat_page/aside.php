<div class="col-md-5 col-lg-3 aside page_column visible" column="first">
    <div class='head'>
        <?php
        if (Registry::load('current_user')->logged_in) {
            ?>
            <span class='menu toggle_side_navigation'>
                <i class="bi bi-list"></i>
                <span class="total_unread_notifications"></span>
            </span>
            <?php
        }
        ?>
        <span class='logo refresh_page'>
            <?php if (Registry::load('current_user')->color_scheme === 'dark_mode') {
                ?>
                <img src="<?php echo Registry::load('config')->site_url.'assets/files/logos/chat_page_logo_dark_mode.png'.$cache_timestamp; ?>" />
                <?php
            } else {
                ?>
                <img src="<?php echo Registry::load('config')->site_url.'assets/files/logos/chat_page_logo.png'.$cache_timestamp; ?>" />
                <?php
            } ?>
        </span>
        <span class='icons'>
            <?php
            if (role(['permissions' => ['audio_player' => 'listen_music']])) {
                ?>
                <i class="bi bi-music-note-list load_audio_player"></i>
                <?php
            }
            if (!Registry::load('current_user')->logged_in) {
                ?>
                <i class="bi bi-grid load_groups load_aside d-none" load="groups"></i>
                <?php
            }
            ?>
        </span>
    </div>

    <div class="storage_files_upload_status">
        <div class="center">
            <div class="error">
                <span class="message"><?php echo Registry::load('strings')->error ?> : <span></span></span>
            </div>
            <div class="text">
                <span><?php echo Registry::load('strings')->uploading_files ?> : <span class="percentage">0%</span></span>
            </div>
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
            </div>
            <div class="files_attached">
                <form class="files_attached_form" enctype="multipart/form-data">
                    <input type="hidden" name="upload" value="storage" />
                    <input type="hidden" name="frontend" value='true' />
                    <input type="file" multiple="" name="storage_file_attachments[]" class="storage_file_attachments" />
                </form>
            </div>
        </div>
    </div>

    <div class="audio_player_box module hidden">
        <?php include 'layouts/chat_page/audio_player_box.php'; ?>
    </div>

    <div class="site_records module">
        <?php include 'layouts/chat_page/site_records.php'; ?>
    </div>


    <?php
    if (!Registry::load('current_user')->logged_in) {
        ?>
        <div class="info_box">
            <div>
                <div class="text">
                    <?php echo Registry::load('strings')->not_logged_in_message ?>
                </div>
                <span class="button open_link" autosync=true link="<?php echo Registry::load('config')->site_url ?>entry/">
                    <?php echo Registry::load('strings')->login ?>
                </span>
            </div>
        </div>
        <?php
    }
    ?>


    <?php
    $site_advert = DB::connect()->rand("site_advertisements",
        ['site_advertisements.site_advert_max_height', 'site_advertisements.site_advert_content'],
        ["site_advertisements.site_advert_placement" => 'left_content_block', "disabled[!]" => 1, "LIMIT" => 1]
    );
    if (isset($site_advert[0])) {
        $site_advert = $site_advert[0];
        ?>

        <div class="site_advert_block" style="max-height:<?php echo $site_advert['site_advert_max_height']; ?>px">
            <div>
                <?php echo $site_advert['site_advert_content']; ?>
            </div>
        </div>
        <?php
    }
    ?>

    <?php include 'layouts/chat_page/mini_audio_player.php'; ?>

</div>