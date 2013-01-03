<script type="text/javascript">
alert(1111);
    <?php if ($configMap): ?>
    twttr.anywhere.config(<?php echo json_encode($configMap) ?>);
    <?php endif; ?>

    twttr.anywhere(function (T) {
        <?php echo $scripts ?>
    });
</script>
