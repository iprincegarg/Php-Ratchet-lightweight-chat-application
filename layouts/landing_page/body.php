<?php
if (isset(Registry::load('settings')->hero_section_animation) && Registry::load('settings')->hero_section_animation === 'enable') {
    Registry::load('appearance')->body_class .= ' animated_hero_image';
}
?>
<body class="landing_page<?php echo ' '.Registry::load('appearance')->body_class ?>">

    <?php include 'assets/headers_footers/landing_page/body.php'; ?>
    <main>

        <?php
        include('layouts/landing_page/navigation.php');
        ?>

        <?php
        if (isset(Registry::load('config')->load_page) && !empty(Registry::load('config')->load_page)) {
            include('layouts/landing_page/custom_page.php');
        } else {
            include('layouts/landing_page/hero.php');
            include('layouts/landing_page/groups.php');
            include('layouts/landing_page/faq.php');
        }
        ?>

        <?php
        include('layouts/landing_page/bottom.php');
        ?>



    </main>

    <?php include 'assets/headers_footers/landing_page/footer.php'; ?>
</body>