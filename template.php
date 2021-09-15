<?php 

function template_pagination($nom_page){
$pdo = pdo_connect_mysql();

// Get the page via GET request (URL param: page), if non exists default the page to 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
// Number of records to show on each page
$records_per_page = 10;

$params = $search= "";
if ($nom_page == 'client' || $nom_page == 'fournisseur'){
$sortable = ["nom", "ville", "pays"]; //variable
}
elseif ($nom_page == 'gestion_caisse') {
  $sortable = ["date_ajout", "solde_actuel", "solde", "etat", "responsable"];
}
elseif ($nom_page == 'caisse_stock') {
  $sortable = ["date_ajout", "designation", "entrer", "ajouter_par"];
}
elseif ($nom_page == 'caisse_vente') {
  $sortable = ["date_ajout", "designation", "sortie", "vendu_par"];
}
elseif ($nom_page == 'deffectueux') {
  $sortable = ["nom_produit", "quantite", "date_ajout", "prix_totale"];
}

$ligne = 0;
for ($x=1;$x < $page;$x++) {
    $ligne =$ligne+5;
}

//recherche par nom
if (!empty($_GET['q'])){
    $params = "'%" . $_GET['q'] . "%'";
    if ($nom_page == 'client' || $nom_page == 'fournisseur'){
    $search = " AND (NOM LIKE $params OR VILLE LIKE $params)"; //variable
    }
    elseif ($nom_page == 'gestion_caisse') {
      $search = " AND (gestion_caisse.DATE_AJOUT LIKE $params OR gestion_caisse.ETAT LIKE $params OR gestion_caisse.MOTIF LIKE $params OR gestion_caisse.RESPONSABLE LIKE $params)";
    }
    elseif ($nom_page == 'caisse_stock') {
      $search = " AND (caisse_stock.DATE_AJOUT LIKE $params OR caisse_stock.DESIGNATION LIKE $params OR caisse_stock.ENTRER LIKE $params OR caisse_stock.TOTALE_SOMME LIKE $params OR caisse_stock.AJOUTER_PAR LIKE $params)";
    }
    elseif ($nom_page == 'caisse_vente') {
      $search = " AND (caisse_vente.DATE_AJOUT LIKE $params OR caisse_vente.DESIGNATION LIKE $params OR caisse_vente.SORTIE LIKE $params OR caisse_vente.TOTALE_SOMME LIKE $params OR caisse_vente.VENDU_PAR LIKE $params)";
    }
    elseif ($nom_page == 'deffectueux') {
      $search = " AND (deffectueux.DESIGNATION LIKE $params OR deffectueux.DATE_AJOUT LIKE $params)";
    }
}

//organisation

if(!empty($_GET['sort']) && in_array($_GET['sort'], $sortable)){
    $direction = $_GET['dir'];
    if (!in_array($direction, ['asc', 'desc'])) {
        $direction = 'asc';
    }
    $pag = " ORDER BY " . $_GET['sort'] . " $direction";
}

// Get the total number, this is so we can determine whether there should be a next and previous button
if ($nom_page == 'client' || $nom_page == 'fournisseur' || $nom_page == 'gestion_caisse'){
$num = $pdo->query("SELECT COUNT(*) FROM $nom_page WHERE ID=".$_SESSION['id'].$search)->fetchColumn(); //variable
}
elseif ($nom_page == 'caisse_stock') {
  $num = $pdo->query("SELECT COUNT(*) FROM fournisseur, stock, gestion_stock, caisse_stock WHERE (fournisseur.ID=".$_SESSION['id'].") AND (fournisseur.ID_FOURNISSEUR=stock.ID_FOURNISSEUR) AND (stock.ID_STOCK=gestion_stock.ID_STOCK) AND (gestion_stock.ID_GESTION=caisse_stock.ID_GESTION)".$search)->fetchColumn();
}
elseif ($nom_page == 'caisse_vente') {
  $num = $pdo->query("SELECT COUNT(*) FROM client, gestion_stock, vente, caisse_vente WHERE (client.ID=".$_SESSION['id'].") AND (client.ID_CLIENT=vente.ID_CLIENT) AND (gestion_stock.ID_GESTION=vente.ID_GESTION) AND (vente.ID_VENTE=caisse_vente.ID_VENTE)".$search)->fetchColumn();
}
elseif ($nom_page == 'deffectueux') {
  $num = $pdo->query("SELECT COUNT(*) FROM fournisseur, stock, deffectueux WHERE (stock.ID_FOURNISSEUR=fournisseur.ID_FOURNISSEUR) AND (stock.ID_STOCK=deffectueux.ID_STOCK) AND (fournisseur.ID=".$_SESSION['id'].")".$search)->fetchColumn();
}

$currentPage = ($page-1)*$records_per_page;
$nbre_page = ceil($num / $records_per_page);
$before_page = $page - 1;
$after_page = $page + 1;

echo <<<EOT
<!-- //// PAGINATION //// -->

    <div class="pied">
      <ul class="pagination">
EOT;

        ///////////////////////////////// si la pagination est inférieur ou égale à 5 /////////////////////////////////

        if ($nbre_page <= 5 ){                    
          ///////////////////////////////// pagination précédent /////////////////////////////////

          if ($page > 1){
            if(isset($_GET['sort']) && isset($_GET['dir'])){

              echo"<li class='prev'><a href='read_". $nom_page .".php?q=";
              if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";} echo"&page=". ($page-1) . "&sort=" . $_GET['sort'] . "&dir=" . $_GET['dir']. "'"."><i class='fas fa-angle-double-left fa-sm'></i> Précédent</a></li>";
            }
            elseif (isset($_GET['q']) && (!isset($_GET['sort']) && !isset($_GET['dir']))){
              echo"<li class='prev'><a href='read_". $nom_page. ".php?q=". $_GET['q'] ."&page=". ($page-1) ."'"."><i class='fas fa-angle-double-left fa-sm'></i> Précédent</a></li>"; 
            }
            else{
              echo"<li class='prev'><a href='read_". $nom_page .".php?page=". ($page-1) ."'"."><i class='fas fa-angle-double-left fa-sm'></i> Précédent</a></li>";
            }
          }
          elseif($num < 1){

          }
          elseif($page == 1){
          echo"<li class='prev disable'><a href='read_". $nom_page .".php?page=". ($page-1) ."'"."><i class='fas fa-angle-double-left fa-sm'></i> Précédent</a></li>";
          }

          ///////////////////////////////// numéro pagination /////////////////////////////////

          for($i = 1; $i <= $nbre_page; $i++){
            if($i == $page){
              if(isset($_GET['sort']) && isset($_GET['dir'])){
                echo"<li class='num_page active'><a href='read_". $nom_page .".php?q="; 
                if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";}; echo"&page=". $i ."&sort=".$_GET['sort']."&dir=".$_GET['dir']."'>". $i ."</a></li>";
              }
              elseif (isset($_GET['q']) && (!isset($_GET['sort']) && !isset($_GET['dir']))){
                echo"<li class='num_page active'><a href='read_". $nom_page .".php?q=";
                if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";} echo"&page=". $i ."&sort=".$_GET['sort']."&dir=".$_GET['dir']."'>". $i ."</a></li>"; 
              }
              else{
                echo"<li class='num_page active'><a href='read_". $nom_page .".php?page=". $i ."'>". $i ."</a></li>";
              }
            }
            else{
              if(isset($_GET['sort']) && isset($_GET['dir'])){
                echo"<li class='num_page'><a href='read_". $nom_page .".php?q="; 
                if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";} echo"&page=". $i ."&sort=". $_GET['sort'] ."&dir=". $_GET['dir'] ."'>". $i ."</a></li>";
              }
              elseif (isset($_GET['q']) && (!isset($_GET['sort']) && !isset($_GET['dir']))){
                echo"<li class='num_page'><a href='read_". $nom_page .".php?q=";
                if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";} echo"&page=". $i ."'>". $i ."</a></li>"; 
              }
              else{
                echo"<li class='num_page'><a href='read_". $nom_page .".php?page=". $i ."'>". $i ."</a></li>";
              }
            }
          }

          ///////////////////////////////// pagination suivant /////////////////////////////////

          if ($page*$records_per_page < $num){
            if(isset($_GET['sort']) && isset($_GET['dir'])){
              echo"<li class='next'><a href='read_". $nom_page .".php?q="; 
              if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";} echo"&page=". ($page+1) ."&sort=".$_GET['sort']."&dir=".$_GET['dir']."'>Suivant <i class='fas fa-angle-double-right fa-sm'></i></a></li>";
            }
            elseif (isset($_GET['q']) && (!isset($_GET['sort']) && !isset($_GET['dir']))){
              echo"<li class='next'><a href='read_". $nom_page .".php?q=". $_GET['q'] ."&page=". ($page+1) ."'>Suivant <i class='fas fa-angle-double-right fa-sm'></i></a></li>"; 
            }
            else{
              echo"<li class='next'><a href='read_". $nom_page .".php?page=". ($page+1) ."'>Suivant <i class='fas fa-angle-double-right fa-sm'></i></a></li>";
            }
          }
          elseif($num < 1){

          }
          elseif($page*$records_per_page >= $num){
           echo"<li class='next disable'><a href='read_". $nom_page .".php?page=". ($page+1) ."'>Suivant <i class='fas fa-angle-double-right fa-sm'></i></a></li>"; 
          }
        }
    ///////////////////////////////// si la pagination est supérieur à 5 /////////////////////////////////

        elseif ($nbre_page > 5 ){
          ///////////////////////////////// pagination précédent /////////////////////////////////

          if ($page > 1){
            if(isset($_GET['sort']) && isset($_GET['dir'])){
              echo"<li class='prev'><a href='read_". $nom_page .".php?q="; 
              if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";} echo"&page=". ($page-1) ."&sort=". $_GET['sort'] ."&dir=". $_GET['dir'] ."'><i class='fas fa-angle-double-left fa-sm'></i> Précédent</a></li>";
            }
            elseif (isset($_GET['q']) && (!isset($_GET['sort']) && !isset($_GET['dir']))){
              echo"<li class='prev'><a href='read_". $nom_page .".php?q=". $_GET['q'] ."&page=". ($page-1) ."'><i class='fas fa-angle-double-left fa-sm'></i> Précédent</a></li>"; 
            }
            else{
              echo"<li class='prev'><a href='read_". $nom_page .".php?page=". ($page-1) ."'><i class='fas fa-angle-double-left fa-sm'></i> Précédent</a></li>";
            }
          }
          elseif($num < 1){

          }
          elseif($page == 1){
            echo"<li class='prev disable'><a href='read_". $nom_page .".php?page=". ($page-1) ."'><i class='fas fa-angle-double-left fa-sm'></i> Précédent</a></li>";
          }

          ///////////////////////// numero apres pagination précedent /////////////////////////////
          if($page > 2){
            echo"<li class='num_page'><a href='read_". $nom_page .".php?page=1'>1</a></li>";
            if($page > 3){
              echo"<li class='dots disable'><a href=''>...</a></li>";
            }
          }   

          if($page == $nbre_page){
            echo $before_page = $before_page - 2;
          }
          elseif($page == $nbre_page - 1){ 
            echo $before_page = $before_page - 1;
          }
          
          if($page == 1){
            $after_page = $after_page + 2;
          }
          elseif($page == 2){ 
            echo $after_page = $after_page + 1;
          }

          ///////////////////////////////// numéro pagination /////////////////////////////////

          
          for($i = $before_page; $i <= $after_page; $i++){
            if($i > $nbre_page){
              continue;
            }

            if($i == 0){
              echo $i = $i + 1;
            }


            if($i == $page){
              if(isset($_GET['sort']) && isset($_GET['dir'])){
                echo"<li class='num_page active'><a href='read_". $nom_page .".php?q="; 
                if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";} echo"&page=". $i ."&sort=". $_GET['sort'] ."&dir=". $_GET['dir'] ."'>". $i ."</a></li>";
              }
              elseif (isset($_GET['q']) && (!isset($_GET['sort']) && !isset($_GET['dir']))){
                echo"<li class='num_page active'><a href='read_". $nom_page .".php?q="; 
                if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";} echo"&page=". $i ."&sort=". $_GET['sort'] ."&dir=". $_GET['dir'] ."'>". $i ."</a></li>"; 
              }
              else{
                echo"<li class='num_page active'><a href='read_". $nom_page .".php?page=". $i ."'>". $i ."</a></li>";
              }
            }
            else{
              if(isset($_GET['sort']) && isset($_GET['dir'])){
                echo"<li class='num_page'><a href='read_". $nom_page .".php?q=";
                if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";} echo"&page=". $i ."&sort=". $_GET['sort'] ."&dir=". $_GET['dir'] ."'>". $i ."</a></li>";
              }
                
              elseif (isset($_GET['q']) && (!isset($_GET['sort']) && !isset($_GET['dir']))){
                echo"<li class='num_page'><a href='read_". $nom_page .".php?q="; 
                if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";} echo"&page=". $i ."&sort=". $_GET['sort'] ."&dir=". $_GET['dir'] ."'>". $i ."</a></li>";
              }
            
              else{
                echo"<li class='num_page'><a href='read_". $nom_page .".php?page=". $i ."'>". $i ."</a></li>";
              }
            }
          }
          
          ///////////////////////// numero avant fin pagination /////////////////////////////
          if($page < $nbre_page-1){
              if($page < $nbre_page-2){
                echo"<li class='dots disable'><a href=''>...</a></li>";
              }
              echo"<li class='num_page'><a href='read_". $nom_page .".php?page=". $nbre_page ."'>". $nbre_page ."</a></li>";
          }

          ///////////////////////////////// pagination suivant /////////////////////////////////

          if ($page*$records_per_page < $num){
            if(isset($_GET['sort']) && isset($_GET['dir'])){
              echo"<li class='next'><a href='read_". $nom_page .".php?q="; 
              if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";} echo"&page=". ($page+1) ."&sort=". $_GET['sort'] ."&dir=". $_GET['dir'] ."'>Suivant <i class='fas fa-angle-double-right fa-sm'></i></a></li>";
            }
            elseif (isset($_GET['q']) && (!isset($_GET['sort']) && !isset($_GET['dir']))){
              echo"<li class='next'><a href='read_". $nom_page .".php?q=". $_GET['q'] ."&page=". ($page+1) ."'>Suivant <i class='fas fa-angle-double-right fa-sm'></i></a></li>"; 
            }
            else{
              echo"<li class='next'><a href='read_". $nom_page .".php?page=". ($page+1) ."'>Suivant <i class='fas fa-angle-double-right fa-sm'></i></a></li>";
            }
          }
          elseif($num < 1){

          }
          elseif($page*$records_per_page >= $num){
           echo"<li class='next disable'><a href='read_". $nom_page .".php?page=". ($page+1) ."'>Suivant <i class='fas fa-angle-double-right fa-sm'></i></a></li>"; 
          }
        } 
echo <<<EOT
      </ul>
    </div>
<!-- FIN PAGINATION --> 
EOT;
}

 ?>