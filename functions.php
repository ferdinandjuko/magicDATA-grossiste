<?php
// DEFNINITION TIMEZONE
date_default_timezone_set("Europe/Moscow");

// FONCTION CONNEXION A LA BASE DE DONNEE
function pdo_connect_mysql() {
  $DATABASE_HOST = 'localhost';
  $DATABASE_USER = 'root';
  $DATABASE_PASS = '';
  $DATABASE_NAME = 'grossiste_ilo';
  try {
  	return new PDO('mysql:host=' . $DATABASE_HOST . ';dbname=' . $DATABASE_NAME . ';charset=utf8', $DATABASE_USER, $DATABASE_PASS);
  } catch (PDOException $exception) {
  	// If there is an error with the connection, stop the script and display the error.
  	exit('Failed to connect to database!');
  }
}

// Si la session n'est pas définie, rediriger vers la page de connexion, si l'url est prédéfinie avant la connexion rediriger vers l'url prédéfinie après la connexion

function session_url() {
  
  if(!isset($_SESSION['username'])) {

  // protocole utilisé : http ou https ?
  if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') $url = "https://"; else $url = "http://";
  // hôte (nom de domaine voire adresse IP)
  $url .= $_SERVER['HTTP_HOST'];
  // emplacement de la ressource (nom de la page affichée). Utiliser $_SERVER['PHP_SELF'] si vous ne voulez pas afficher les paramètres de la requête
  $url .= $_SERVER['REQUEST_URI'];
  // on affiche l'URL de la page courante

  $mot = "login";

  // Teste si la chaîne contient le mot, puis remplacer par index pour rediriger vers l'index par défaut.
  if(strpos($url, $mot) !== false) {

    #Le mot existe!";
    $url = str_replace("login", "index", $url);

  }

  # Definition en session de l'url avant la connexion vers le login
  $_SESSION['url'] = $url;
  header('location: login.php');
  }

}

// VERIFICATION SI L'UTILISATEUR EXISTE TOUJOURS DANS LA BASE DE DONNÉE

function verif_session() {
  
  $pdo = pdo_connect_mysql();
  if (isset($_SESSION['id'])) {

    if (isset($_SESSION['id_modo'])) {
       
        $sql ="SELECT * FROM moderateur WHERE ID_MODO =".$_SESSION['id_modo'];
        $query_modo = $pdo->prepare($sql);
        $query_modo->execute();
        $query_modo->fetch(PDO::FETCH_BOTH);

        $sql ="SELECT * FROM inscription WHERE ID =".$_SESSION['id'];
        $query = $pdo->prepare($sql);
        $query->execute();
        $query->fetch(PDO::FETCH_BOTH);   

        if ($query->rowCount() == 0 || $query_modo->rowCount()== 0 ) {
            # REDIRIGER VERS LOGIN
            header("location: login.php");
            exit();         
          }
     

      } else {

        $sql ="SELECT * FROM inscription WHERE ID =".$_SESSION['id'];
        $query = $pdo->prepare($sql);
        $query->execute();
        $query->fetch(PDO::FETCH_BOTH);

        if ($query->rowCount() == 0 ) {
            // code...
            # REDIRIGER VERS LOGIN
            header("location: login.php");
            exit();         
          }

      }

  }

}

// TEMPLATE HEADER HTML
function template_header($title) {
echo <<<EOT
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>$title</title>
        <link href="bootstrap-4.5.3/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>  
        <link href="css.css" rel="stylesheet" type="text/css">       
        <link rel="stylesheet" href="style/all.min.css">
        <link rel="icon" type="image/PNG" href="favicon.png">
        <script src="scripts/jquery-3.5.1.min.js" type="text/javascript"></script>   
        <script src="scripts/popper.min.js" type="text/javascript"></script>     
        <script src="scripts/bootstrap.min.js"></script>        
        <script src="scripts/sweetalert2/dist/sweetalert2.all.min.js"></script>
        <script src="scripts/sweetalert2/dist/polyfill.js"></script>
        <script src="scripts/all.min.js" type="text/javascript"></script>        
  </head>
<body>
EOT;
}

// TEMPLATE CONTENT PAGE (NAVBAR ET SLIDEBAR)
function template_content($curentpage) {
echo <<<EOT
 <div id="container" class="container-fluid">

  <div class="row">

  <!-- //////////////// SLIDEBAR ///////////////////////// -->

    <div id="slidebar" class="slidebar">
      <div class="container-logo">
            <a href="index.php"><p class="p-image"><img id="logo" src="ilo/logo.png" alt="LOGO ILO"><p></a>
            <a href="index.php"><p class="logo-p"><span class="logo-p-bleu">ILO </span><span class="logo-p-orange">- GROSSISTE</span></p></a>
      </div>
    <!-- Links -->
    <nav id='slidenav'>
      <ul class='nav flex-column'>      
EOT;

     ///////////// INDEX //////////////

      if ($curentpage=="index") {
      # Si le page actuelle est INDEX        
      echo"<li id='border-line-top-active' class='nav-item border-line'>
            <a class='nav-link' href='index.php'><img id='icon-tableau-bord' src='ilo/tableau-de-bord-blanc.png'> Tableau de bord </a>
          </li>";   
      } else {
      # Si le page actuelle n'est pas INDEX        
        echo "<li id='border-line-top-non-active' class='nav-item border-line'>
            <a class='nav-link' href='index.php'><img id='icon-tableau-bord' src='ilo/tableau-de-bord.png'> Tableau de bord </a>
          </li>  ";
      }

     ///////////// HISTORIQUE //////////////

      if ($curentpage=="historique" && $_SESSION['user']<>"modo") {
      # Si le page actuelle est RAPPORT OU HISTORIQUE  que la session utilisateur est administrateur     
      echo"<li id='menu-active' class='nav-item border-line'>
            <a class='nav-link' href='read_historique.php'><img src='ilo/historique-blanc.png'> Rapports </a>
          </li>";   
      }elseif ($curentpage=="historique" && $_SESSION['user']<>"admin") {
      # Si le page actuelle est RAPPORT OU HISTORIQUE  que la session utilisateur est moderateur
      echo"";   
      }elseif ($curentpage<>"historique" && $_SESSION['user']<>"modo"){
      # Si le page actuelle n'est pas RAPPORT  OU HISTORIQUE     
      echo "<li class='nav-item border-line'>
            <a class='nav-link' href='read_historique.php'><img src='ilo/historique.png'> Rapports </a>
          </li>  ";
      }            

     ///////////// CONTACT //////////////

      if ($curentpage=="client") {
      # Si le page actuelle est CLIENT        
      echo"<li id='menu-active' class='nav-item border-line'>
              <a id='contact-btn' class='nav-link'><img src='ilo/contact-blanc.png'> Clients <i id='contact-arrow' class='fas fa-caret-down'></i></a>
            </li>
            <div id='contact'>
                <a class='nav-link' href='read_client.php'><img src='ilo/contact.png'> Clients</a>
                <a class='nav-link' href='read_fournisseur.php'><img src='ilo/contact.png'> Fournisseurs</i></a>
            </div>";
      }elseif ($curentpage=="fournisseur") {
      # Si le page actuelle est FOURNISSEUR       
      echo"<li id='menu-active' class='nav-item border-line'>
              <a id='contact-btn' class='nav-link'><img src='ilo/contact-blanc.png'> Fournisseur <i id='contact-arrow' class='fas fa-caret-down'></i></a>
            </li>
            <div id='contact'>
                <a class='nav-link' href='read_client.php'><img src='ilo/contact.png'> Clients</a>
                <a class='nav-link' href='read_fournisseur.php'><img src='ilo/contact.png'> Fournisseurs</i></a>
            </div>";
      }else {
      # Si le page actuelle n'est pas CLIENT        
      echo"<li class='nav-item border-line'>
            <a id='contact-btn' class='nav-link'><img src='ilo/contact.png'> Contacts <i id='contact-arrow' class='fas fa-caret-down'></i></a>
          </li>
          <div id='contact'>
            <a class='nav-link' href='read_client.php'><img src='ilo/contact.png'> Clients</a>
            <a class='nav-link' href='read_fournisseur.php'><img src='ilo/contact.png'> Fournisseurs</i></a>
          </div>";
      }

     ///////////// STOCKS //////////////

    if ($curentpage=="stocks") {
      # Si le page actuelle est STOCKS
      echo"<li id='menu-active' class='nav-item border-line'>
            <a id='inventaire-btn' class='nav-link'><img src='ilo/inventaire-blanc.png'> Stocks <i id='inventaire-arrow' class='fas fa-caret-down'></i></a>
          </li>
          <div id='inventaire'>
            <a class='nav-link' href='read_stock.php'><img src='ilo/inventaire.png'> Stocks</a>
            <a class='nav-link' href='read_deffectueux.php'><img src='ilo/inventaire.png'> Stocks déffectueux</a>
          </div>"; 
      }elseif ($curentpage=="deffectueux") {
      # Si le page actuelle est DEFFECTUEUX
      echo"<li id='menu-active' class='nav-item border-line'>
            <a id='inventaire-btn' class='nav-link'><img src='ilo/inventaire-blanc.png'> Déffectueux <i id='inventaire-arrow' class='fas fa-caret-down'></i></a>
          </li>
          <div id='inventaire'>
            <a class='nav-link' href='read_stock.php'><img src='ilo/inventaire.png'> Stocks</a>
            <a class='nav-link' href='read_deffectueux.php'><img src='ilo/inventaire.png'> Stocks déffectueux</a>
          </div>"; 
      }else {
      # Si le page actuelle n'est pas STOCKS
      echo"<li class='nav-item border-line'>
            <a id='inventaire-btn' class='nav-link'><img src='ilo/inventaire.png'> Inventaire <i id='inventaire-arrow' class='fas fa-caret-down'></i></a>
          </li>
          <div id='inventaire'>
            <a class='nav-link' href='read_stock.php'><img src='ilo/inventaire.png'> Stocks</a>
            <a class='nav-link' href='read_deffectueux.php'><img src='ilo/inventaire.png'> Stocks déffectueux</a>
          </div>";         
      }

     ///////////// CAISSE //////////////

      if ($curentpage=="caisse") {
      # Si le page actuelle est CAISSE
      echo"<li id='menu-active' class='nav-item border-line'>
            <a class='nav-link' href='read_caisse.php'><img src='ilo/caisse-blanc.png'> Caisse</a>
          </li>";
      } else {
      # Si le page actuelle n'est pas CAISSE
      echo"<li class='nav-item border-line'>
            <a class='nav-link' href='read_caisse.php'><img src='ilo/caisse.png'> Caisse</a>
          </li>";        
      }

     ///////////// VENTE //////////////

      if ($curentpage=="vente") {
      # Si le page actuelle est VENTE
      echo"<li id='menu-active' class='nav-item border-line'>
            <a class='nav-link' href='read_vente.php'><img src='ilo/vente-blanc.png'> Vente</a>
          </li>";
      } else {
      # Si le page actuelle n'est pas VENTE
      echo"<li class='nav-item border-line'>
            <a class='nav-link' href='read_vente.php'><img src='ilo/vente.png'> Vente</a>
          </li>";        
      } 

     ///////////// FACTURE OU FICHIER //////////////

      if ($curentpage=="fichier") {
      # Si le page actuelle est FACTURE OU FICHIER
      echo"<li id='menu-active' class='nav-item border-line'>
            <a class='nav-link' href='read_facture.php'><img src='ilo/fichiers.png'> Fichier</a>
          </li>";
      } else {
      # Si le page actuelle n'est pas FACTURE OU FICHIER
      echo"<li class='nav-item border-line'>
            <a class='nav-link' href='read_facture.php'><img src='ilo/fichier.png'> Fichier</a>
          </li>";        
      }   

     ///////////// EMPLOYES //////////////

      if ($curentpage=="employes" && $_SESSION['user']<>"modo") {
      # Si le page actuelle est EMPLOYES et que la session utilisateur est administrateur
      echo"<li id='menu-active' class='nav-item border-line'>
            <a class='nav-link' href='read_employe.php'><img src='ilo/profil-blanc.png'> Employés</a>
          </li> ";
      } elseif ($curentpage=="emoloyes" && $_SESSION['user']<>"admin") {
      # Si le page actuelle n'est pas EMPLOYES et que la session utilisateur est moderateur
      echo"";        
      } elseif ($curentpage<>"emoloyes" && $_SESSION['user']<>"modo") {
      # Si le page actuelle n'est pas EMPLOYES
      echo"<li class='nav-item border-line'>
            <a class='nav-link' href='read_employe.php'><img src='ilo/profil.png'> Employés</a>
          </li>";        
      }                         
echo <<<EOT
      </ul>    
    </nav>
    <div id="aide">
        <a class="nav-link" href="read_aide.php"><i class="far fa-question-circle"></i> Aides</a> 
    </div>
    </div>
    <!-- CONTENU -->
    <div id="content" class="content">
    <nav id="navbar" class="navbar navbar-expand-md">
      <!-- Brand -->
        <i id="toggler-icon" class="fas fa-bars"></i>
      <!-- Navbar links -->
      <div class="collapse navbar-collapse" id="photo-user">
        <ul class="navbar-nav">
          <li class="nav-item">
EOT;
      echo "<a class='nav-link compte' href='#'><img src='ilo/icons-100.png'>".ucwords($_SESSION['name'])."</a>";
echo <<<EOT
          </li>
        </ul>
      </div>      
      <div class="collapse navbar-collapse" id="collapsibleNavbar">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a id="configuration" class="nav-link" href="parametre.php"><i id="fa-cog" class="fas fa-cog"></i> Configuration</a>
          </li>
          <li class="nav-item">
            <a class="nav-link power-off" href="logout.php"><i class="fas fa-power-off"></i></a>
          </li>         
        </ul>
      </div>
    </nav> 
EOT;
}


// TEMPLATE PAGINATION
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
elseif ($nom_page == 'gestion_stock') {
  $sortable = ["nom_produit", "designation"];
}
elseif ($nom_page == 'stock') {
  $sortable = ["date_ajout", "nom_produit", "reference","prix_unitaire","prix_totale" ,"date_achat", "fournisseur", "ajoute_par"];
}
elseif ($nom_page == 'vente') {
  $sortable = ["nom_produit", "prix_unitaire", "quantite", "prix_totale", "benefice", "date_vente", "client"];
}
elseif ($nom_page == 'employe') {
  $sortable = ["nom_complet"];
}
elseif ('historique') {
  $sortable = ["date & heure"];
}
elseif ($nom_page == 'facture') {
  $sortable = ["date_echeance", "client", "designation"];
}

$ligne = 0;
for ($x=1;$x < $page;$x++) {
    $ligne =$ligne+$records_per_page;
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
    elseif ($nom_page == 'gestion_stock') {
      $search = " AND (gestion_stock.NOM_PRODUIT LIKE $params OR gestion_stock.REFERENCE LIKE $params)";
    }
    elseif ($nom_page == 'stock') {
      $search = " AND (stock.NOM_PRODUIT LIKE $params OR stock.DATE_AJOUT LIKE $params OR stock.REFERENCE LIKE $params OR stock.DATE_ACHAT LIKE $params OR stock.FOURNISSEUR LIKE $params OR stock.AJOUTE_PAR LIKE $params)";
    }
    elseif ($nom_page == 'vente') {
      $search = " AND (vente.NOM_PRODUIT LIKE $params OR vente.REFERENCE LIKE $params OR vente.PRIX_UNITAIRE LIKE $params OR vente.QUANTITE LIKE $params OR vente.PRIX_TOTALE LIKE $params OR vente.BENEFICE LIKE $params OR vente.DATE_VENTE LIKE $params OR vente.CLIENT LIKE $params OR vente.STATUT LIKE $params OR vente.AJOUTE_PAR LIKE $params)";
    }
    elseif ($nom_page == 'historique') {
      $search = " AND (DATE_HISTORIQUE LIKE $params OR ACTION LIKE $params OR TYPE LIKE $params OR AJOUTER_PAR LIKE $params OR AVANT LIKE $params OR APRES LIKE $params OR MODIFIER_PAR LIKE $params OR SUPPRIMER_PAR LIKE $params)";
    }
    elseif ($nom_page == 'employe') {
      $search = " AND (moderateur.NOM_COMPLET LIKE $params)";
    }
    elseif ($nom_page == 'facture') {
      $search = " AND (DATE_ECHEANCE LIKE $params OR facture.CLIENT LIKE $params OR facture.DESIGNATION LIKE $params OR facture.HEURE_ECHEANCE LIKE $params OR facture.QUANTITE LIKE $params OR facture.PRIX_UNITAIRE LIKE $params OR facture.STATUT LIKE $params)";
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
if ($nom_page == 'client' || $nom_page == 'fournisseur' || $nom_page == 'employe' || $nom_page == 'historique'){
$num = $pdo->query("SELECT COUNT(*) FROM $nom_page WHERE ID =".$_SESSION['id']."".$search)->fetchColumn(); //variable
}
elseif ($nom_page == 'gestion_caisse') {
  $num = $pdo->query("SELECT COUNT(*) FROM gestion_caisse WHERE SESSION_ID =".$_SESSION['id']."".$search)->fetchColumn();
}
elseif ($nom_page == 'caisse_stock') {
  $num = $pdo->query("SELECT COUNT(*) FROM caisse_stock WHERE SESSION_ID =".$_SESSION['id']."".$search)->fetchColumn();
}
elseif ($nom_page == 'caisse_vente') {
  $num = $pdo->query("SELECT COUNT(*) FROM client, gestion_stock, vente, caisse_vente WHERE (client.ID=".$_SESSION['id'].") AND (client.ID_CLIENT=vente.ID_CLIENT) AND (gestion_stock.ID_GESTION=vente.ID_GESTION) AND (vente.ID_VENTE=caisse_vente.ID_VENTE)".$search)->fetchColumn();
}
elseif ($nom_page == 'deffectueux') {
  $num = $pdo->query("SELECT COUNT(*) FROM gestion_stock, deffectueux WHERE (deffectueux.ID_GESTION=gestion_stock.ID_GESTION) AND (gestion_stock.SESSION_ID=".$_SESSION['id'].")".$search)->fetchColumn();
}
elseif ($nom_page == 'gestion_stock') {
  $num = $pdo->query("SELECT COUNT(*) FROM fournisseur, stock, gestion_stock WHERE (stock.ID_GESTION=gestion_stock.ID_GESTION) AND (fournisseur.ID_FOURNISSEUR=stock.ID_FOURNISSEUR) AND (stock.NOM_PRODUIT=gestion_stock.NOM_PRODUIT) AND gestion_stock.QUANTITE<>0 AND (fournisseur.ID=".$_SESSION['id'].")".$search)->fetchColumn();
}
elseif ($nom_page == 'stock') {
  $num = $pdo->query("SELECT COUNT(*) FROM stock, fournisseur WHERE (stock.ID_FOURNISSEUR=fournisseur.ID_FOURNISSEUR) AND fournisseur.ID=".$_SESSION['id'].$search)->fetchColumn();
}
elseif ($nom_page == 'vente') {
  $num = $pdo->query("SELECT COUNT(*) FROM vente, client WHERE (client.ID_CLIENT=vente.ID_CLIENT) AND (client.ID=".$_SESSION['id'].")".$search)->fetchColumn();
}
elseif ($nom_page == 'facture') {
  $num = $pdo->query("SELECT COUNT(*) FROM client, facture, vente WHERE (client.ID_CLIENT=vente.ID_CLIENT) AND (facture.ID_FACTURE=vente.ID_FACTURE) AND (client.ID=".$_SESSION['id'].") AND (vente.ID_FACTURE=facture.ID_FACTURE)")->fetchColumn();
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

            if(isset($_GET['sort']) && isset($_GET['dir'])){
                echo"<li class='num_page'><a href='read_". $nom_page .".php?q="; 
                if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";} echo"&page=1&sort=". $_GET['sort'] ."&dir=". $_GET['dir'] ."'>1</a></li>";
              }
              elseif (isset($_GET['q']) && (!isset($_GET['sort']) && !isset($_GET['dir']))){
                echo"<li class='num_page'><a href='read_". $nom_page .".php?q=";
                if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";} echo"&page=1'>1</a></li>"; 
              }
              else{
                echo"<li class='num_page'><a href='read_". $nom_page .".php?page=1'>1</a></li>";
              }

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
                if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";} echo"&page=". $i ."'>". $i ."</a></li>";
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

              if(isset($_GET['sort']) && isset($_GET['dir'])){
                echo"<li class='num_page'><a href='read_". $nom_page .".php?q="; 
                if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";} echo"&page=". $nbre_page ."&sort=". $_GET['sort'] ."&dir=". $_GET['dir'] ."'>". $nbre_page ."</a></li>";
              }
              elseif (isset($_GET['q']) && (!isset($_GET['sort']) && !isset($_GET['dir']))){
                echo"<li class='num_page'><a href='read_". $nom_page .".php?q=";
                if (isset($_GET['q'])){echo $_GET['q'];}else{echo "";} echo"&page=". $nbre_page ."'>". $nbre_page ."</a></li>"; 
              }
              else{
                echo"<li class='num_page'><a href='read_". $nom_page .".php?page=". $nbre_page ."'>". $nbre_page ."</a></li>";
              }
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



/////////// SWEET ALERT NOTIFICATION //////////
function sweet_alert_notification() {

if (isset($_SESSION['alert_text']) && $_SESSION['alert_text']!='')
    echo"
    <script>
      Swal.fire({
          icon: '".$_SESSION['alert_icon']."',
          title: '".$_SESSION['alert_title']."',
          text: '".$_SESSION['alert_text']."',
          timer: 9000,
        })
    </script>";

    # Effacement de la session de notification
    unset($_SESSION['alert_icon']);
    unset($_SESSION['alert_title']);
    unset($_SESSION['alert_text']);
 
}



// TEMPLATE FOOTER HTML AVEC SCRIPT DROPDOWN SLIDEBAR 
function template_footer() {
echo <<<EOT
      </div>
    </div>
  </div>
</div>
<script src="scripts/main.js"></script>
</body>
</html>
EOT;
}

/// AUTRES FONCTION TABLEAU ///

function price(float $number, string $sigle = "Ar"){
    return number_format($number, 0, '', ' ') . ' ' . $sigle;
}

function withParams(array $data, array $params): string {
    return http_build_query(array_merge($data, $params));
}

function tri(string $sortKey, string $label, array $data): string {
    $sort = $data['sort'] ?? null;
    $direction = $data['dir'] ?? null;
    $ascendant = "<i class='fas fa-sort-amount-down-alt'></i>";
    $descendant = "<i class='fas fa-sort-amount-up-alt'></i>";
    $icon = "";
    if($sort === $sortKey){
        $icon = $direction === 'asc' ? $ascendant : $descendant;
    }
    $encre="#cadre-contenu";
    $url = withParams($data, [
        'sort' => $sortKey,
        'dir' => $direction === 'asc' && $sort === $sortKey ?  'desc' : 'asc'
    ]);
    return <<<HTML
        <a id="link_tri" href="?$url$encre">$label $icon</a>
HTML;

}
?>
