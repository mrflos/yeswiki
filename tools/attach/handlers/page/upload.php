<?php

// Execute l'upload des fichiers lier par l'action {{attach}}
// Necessite le fichier actions/attach.php pour fonctionner
// voir actions/attach.php ppour la documentation

ob_start();
?>
<div class="page">
    <?php

    if (!class_exists('attach')) {
        include 'tools/attach/libs/attach.lib.php';
    }
    $att = new attach($this);
    $att->doUpload();
    unset($att);
    ?>
</div>
<?php
$output = ob_get_contents();
ob_end_clean();
echo $this->Header() . $output . $this->Footer(); ?>