<?php


$oldpage = $this->GetPageTag();
if (!empty($this->page['tag'])) {
    $this->CachePage($this->page);
}
$this->tag = trim($this->GetParameter('page'));
$includedPage = $this->GetCachedPage($this->tag);
$this->setPage(!empty($includedPage) ? $includedPage : $this->LoadPage($this->tag));
