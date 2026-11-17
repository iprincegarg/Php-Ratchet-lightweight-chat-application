<div class="contents">
    <div>
        <div class="image">
            <img src="<?php echo Registry::load('config')->site_url.'assets/files/defaults/welcome.png'.$cache_timestamp; ?>" />
        </div>
        <div class="text">
            <span class="title"><?php echo Registry::load('strings')->welcome_screen_heading ?></span>
            <span class="sub_title"><?php echo Registry::load('strings')->welcome_screen_message ?></span>
            <span class="footer"><?php echo Registry::load('strings')->welcome_screen_footer_text ?></span>
        </div>
        <?php
        $site_advert = DB::connect()->rand("site_advertisements",
            ['site_advertisements.site_advert_max_height', 'site_advertisements.site_advert_content'],
            ["site_advertisements.site_advert_placement" => 'welcome_screen', "disabled[!]" => 1, "LIMIT" => 1]
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
    </div>
</div>