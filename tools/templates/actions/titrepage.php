<?php


$title = htmlspecialchars($this->services->get(\YesWiki\Templates\Service\Utils::class)->getTitleFromBody($this->page), ENT_COMPAT | ENT_HTML5);
if ($title) {
    echo $title;
} else {
    echo $this->GetPageTag();
}
