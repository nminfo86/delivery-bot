<?php require_once __DIR__ . '/../../app_version.php'; ?>
<!--Start Footer-->
<div class="container">
    <!-- <p id="gototop" class="pull-right"><a href="../index.php"><?php echo T('return_home')?></a></p> -->
    <footer>
        <p class="text-center">
            <small><a href="https://www.linkedin.com/company/b-itech/" target="_blank">B i-Tech</a>
                <?= T('bouhezila_text') ?></small>
            <a href="#" onclick="showAppVersion(); return false;" style="font-size: 0.75em; color: #6c757d; text-decoration: none; margin-left: 6px;">V<?= htmlspecialchars($appVersionData['current_version']) ?></a>
            <small id="copyrightCms"></small>
        </p>
    </footer>
</div>
<script>
    var _appVersion = <?= json_encode($appVersionData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<!--End Footer-->