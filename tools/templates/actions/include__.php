<?php


// si la page inclue n'existe pas, on propose de la créer
if (!$incPage = $this->LoadPage($incPageName)) {
    $plugin_output_new = $this->LinkTo($incPageName);

    return;
}

// si le lien correspond à l'url, on rajoute une classe "actif"
if (!empty($actif) && $actif == '1') {
    $page_active = $this->tag;
    if (isset($oldpage) && $oldpage != '') {
        // si utilisation de l'extension attach
        $page_active = $oldpage;
    }
    // d'abord les liens avec des attributs class
    $plugin_output_new = preg_replace(
        '~<a href="' . preg_quote($this->config['base_url'] . $page_active, '~') . '" class="(.*)"~Ui',
        '<a class="active-link $1" href="' . $this->config['base_url'] . $page_active . '"',
        $plugin_output_new
    );

    // ensuite les liens restants (ceux avec une classe avant ne sont pas pris en compte)
    $plugin_output_new = $this->services->get(\YesWiki\Templates\Service\Utils::class)->strIreplacement(
        '<a href="' . $this->config['base_url'] . $page_active . '"',
        '<a class="active-link" href="' . $this->config['base_url'] . $page_active . '"',
        $plugin_output_new
    );
}

// rajoute le javascript pour le double clic si la configuration l'autorise, si le parametre est activé et les droits en écriture existent
if (
    !empty($this->config['allow_doubleclic']) && in_array($this->config['allow_doubleclic'], ['1', 'yes', true])
    && !empty($dblclic) && $dblclic == '1' && $this->HasAccess('write', $incPageName)
) {
    $actiondblclic = ' ondblclick="document.location=\'' . $this->Href('edit', $incPageName) . '\';"';
} else {
    $actiondblclic = '';
}
$plugin_output_new = str_replace('<div class="include ', '<div' . $actiondblclic . ' class="', $plugin_output_new);

// on enleve le préfixe include_ des classes pour que le parametre passé
// et le nom de classe CSS soient bien identiques
$plugin_output_new = str_replace('include_', '', $plugin_output_new);

// on ajoute pour le menu du haut la classe nav de bootstrap
if (($incPageName == 'PageMenuHaut' || strstr($class, 'topnavpage')) && !strstr($class, 'horizontal-dropdown-menu')) {
    $plugin_output_new = preg_replace('/\<ul\>/Ui', '<ul class="nav navbar-nav">', $plugin_output_new, 1);

    //TODO: a faire pour toutes les pages ou juste le menu???
    if (YW_CHARSET != 'ISO-8859-1' && YW_CHARSET != 'ISO-8859-15') {
        // tip to replace mb_convert_encoding($plugin_output_new, 'HTML-ENTITIES', 'UTF-8')
        // from https://stackoverflow.com/questions/37215388/what-is-a-replacement-for-mb-convert-encodingstring-utf-8-html-entities
        $plugin_output_new = preg_replace_callback('/[\x{80}-\x{10FFFF}]/u', function ($m) {
            $char = current($m);
            $utf = iconv('UTF-8', 'UCS-4', $char);

            return sprintf('&#x%s;', ltrim(strtoupper(bin2hex($utf)), '0'));
        }, $plugin_output_new);
    }

    $dom = new DOMDocument();
    @$dom->loadHTML($plugin_output_new);
    $xpath = new DOMXpath($dom);

    $dropdowns = $xpath->query('*/div/ul/li/ul');
    if (!is_null($dropdowns)) {
        foreach ($dropdowns as $element) {
            $element->setAttribute('class', 'dropdown-menu');
            $element->parentNode->setAttribute('class', 'dropdown');
        }
    }
    $dropdownslist = $xpath->query('*/div/ul//li/ul/..');
    if (!is_null($dropdownslist)) {
        foreach ($dropdownslist as $element) {
            $nodes = $element->childNodes;
            foreach ($nodes as $node) {
                // we search for #text child or a link, if we accessed the dropdown menu, we break
                if ($node->nodeName == 'ul') {
                    break;
                }

                // we add trigger for dropdown
                if ($node->nodeName == 'a') {
                    $class = $node->getAttribute('class');
                    $node->setAttribute('class', $class . ' dropdown-toggle');
                    $node->setAttribute('data-toggle', 'dropdown');
                    $caret = $dom->createElement('b');
                    $caret->setAttribute('class', 'caret');
                    $node->appendChild($caret);
                } elseif ($node->nodeName == '#text' && !trim($node->nodeValue) == '') {
                    // check if <a exists or must be created
                    $a = $dom->createElement('a');
                    $a->setAttribute('class', 'dropdown-toggle');
                    $a->setAttribute('data-toggle', 'dropdown');
                    $a->setAttribute('href', '#');
                    $a->nodeValue = trim($node->nodeValue);
                    $node->nodeValue = '';
                    $caret = $dom->createElement('b');
                    $caret->setAttribute('class', 'caret');
                    $a->appendChild($caret);
                    $node->parentNode->insertBefore($a, $node);
                }
            }
        }
    }

    $activelinks = $xpath->query("//a[contains(@class, 'active-link')]");
    if (!is_null($activelinks)) {
        foreach ($activelinks as $activelink) {
            $class = $activelink->parentNode->getAttribute('class');
            $activelink->parentNode->setAttribute('class', $class . ' active');
        }
    }
    $plugin_output_new = preg_replace(
        '/^<!DOCTYPE.+?>/',
        '',
        str_replace(
            ['<html>', '</html>', '<body>', '</body>'],
            '',
            $dom->saveHTML()
        )
    ) . "\n";
} elseif (strstr($class, 'menu-unstyled')) {
    // add style to remove bullets on all ul
    $plugin_output_new = preg_replace('/\<ul\>/Ui', '<ul class="list-unstyled">', $plugin_output_new);

    // remove list-unstyled class for level 2 ul
    $plugin_output_new = preg_replace('/\<\/a>\s+<ul class="list-unstyled">/Ui', "</a>\n<ul>", $plugin_output_new);
}

// on rajoute une div clear pour mettre le flow css en dessous des éléments flottants
$plugin_output_new = (!empty($clear) && $clear == '1') ?
    $plugin_output_new . '<div class="clearfix"></div>' . "\n" :
    $plugin_output_new;

$plugin_output_new = $this->services->get(\YesWiki\Templates\Service\Utils::class)->postFormat($plugin_output_new);
