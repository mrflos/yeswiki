<?php


// on choisit le template utilisé
$template = $this->GetParameter('template');
if (empty($template)) {
    $template = 'moteurrecherche_basic.tpl.html';
}

// on peut ajouter des classes à la classe par défaut .searchform
$searchelements['class'] = ($this->GetParameter('class') ? 'form-search ' . $this->GetParameter('class') : 'form-search');
$searchelements['btnclass'] = ($this->GetParameter('btnclass') ? ' ' . $this->GetParameter('btnclass') : '');
$searchelements['iconclass'] = ($this->GetParameter('iconclass') ? ' ' . $this->GetParameter('iconclass') : '');

// on peut changer l'url de recherche
$searchelements['url'] = ($this->GetParameter('url') ? $this->GetParameter('url') : $this->href('show', 'RechercheTexte'));

// si une recherche a été effectuée, on garde les mots clés
$searchelements['phrase'] = htmlspecialchars((isset($_REQUEST['phrase']) ? $_REQUEST['phrase'] : ''));

echo $this->render("@templates/$template", $searchelements);
