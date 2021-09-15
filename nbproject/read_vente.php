<?php
include 'functions.php';
session_start();
if(!isset($_SESSION['username'])) {
header('location: login.php');
}
// Connect to MySQL database
$pdo = pdo_connect_mysql();
// Get the page via GET request (URL param: page), if non exists default the page to 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
// Number of records to show on each page
$records_per_page = 10;

// Prepare the SQL statement and get records from our VENTE table, LIMIT will determine the page
$query = "SELECT * FROM vente, client WHERE (client.ID_CLIENT=vente.ID_CLIENT) AND (client.ID=".$_SESSION['id'].")";
$params = $search= "";
$sortable = ["nom_produit", "prix_unitaire", "quantite", "prix_totale", "benefice", "date_vente", "client"];
$pag = "";
$ligne = 0;
for ($x=1;$x < $page;$x++) {
    $ligne =$ligne+5;
}

//recherche par nom
if (!empty($_GET['q'])){
    $params = "'%" . $_GET['q'] . "%'";
    $search = " AND (vente.NOM_PRODUIT LIKE $params OR vente.REFERENCE LIKE $params OR vente.PRIX_UNITAIRE LIKE $params OR vente.QUANTITE LIKE $params OR vente.PRIX_TOTALE LIKE $params OR vente.BENEFICE LIKE $params OR vente.DATE_VENTE LIKE $params OR vente.CLIENT LIKE $params OR vente.STATUT LIKE $params OR vente.AJOUTE_PAR LIKE $params)";
}

//organisation
if(!empty($_GET['sort']) && in_array($_GET['sort'], $sortable)){
    $direction = $_GET['dir'] ?? 'asc';
    if (!in_array($direction, ['asc', 'desc'])) {
        $direction = 'asc';
    }
    $pag = " ORDER BY " . $_GET['sort'] . " $direction";
}

$pag .= " LIMIT :current_page, :record_per_page";
$query = $query.$search.$pag;
# Si le query est déjà definie
if (isset($_SESSION['query'])) {
  # code...
  $query=$_SESSION['query'];
  unset($_SESSION['query']);
}
// Prepare the SQL statement and get records from our VENTE table, LIMIT will determine the page
$stmt = $pdo->prepare($query);
$stmt->bindValue(':current_page', ($page-1)*$records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':record_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
// Fetch the records so we can display them in our template.
$ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of VENTE
$num_vente = $pdo->query("SELECT COUNT(*) FROM vente, client WHERE (client.ID_CLIENT=vente.ID_CLIENT) AND (client.ID=".$_SESSION['id'].")".$search)->fetchColumn();

$numero_page = "";
$currentPage = ($page-1)*$records_per_page;
$nbre_page = ceil($num_vente / $records_per_page);
$vide = "<h3>Tableau vide</h3>";
?>

<?=template_header('Read')?>

<div id="container" class="container-fluid">

    <div class="row">

    <!-- SLIDEBAR -->
      <div id="slidebar" class="slidebar">
        <div class="container-logo">
            <p class="p-image"><img id="logo" src="ilo/logo.png" alt="LOGO ILO"><p>
            <p class="logo-p"><span class="logo-p-bleu">ILO </span><span class="logo-p-orange">- GROSSISTE</span></p>
        </div>
          <!-- Links -->
        <nav id="slidenav">
            <ul class="nav flex-column">
                <li id="border-line-top-non-active" class="nav-item border-line">
                  <a class="nav-link" href="index.php"><img id="icon-tableau-bord" src="ilo/tableau-de-bord.png"> Tableau de bord </a>
                </li>
                <li class="nav-item border-line">
                  <a id="contact-btn" class="nav-link"><img src="ilo/contact.png"> Contacts <i id="contact-arrow" class="fas fa-caret-down"></i></a>
                </li>
                <div id="contact">
                    <a class="nav-link" href="read_client.php"><img src="ilo/contact.png"> Clients</a>
                    <a class="nav-link" href="read_fournisseur.php"><img src="ilo/contact.png"> Fournisseurs</i></a>
                </div>
                <li class="nav-item border-line">
                  <a id="inventaire-btn" class="nav-link"><img src="ilo/inventaire.png"> Inventaire <i id="inventaire-arrow" class="fas fa-caret-down"></i></a>
                </li>
                <div id="inventaire">
                    <a class="nav-link" href="read_stock.php"><img src="ilo/inventaire.png"> Stocks</a>
                    <a class="nav-link" href="read_deffectueux.php"><img src="ilo/inventaire.png"> Stocks déffectueux</a>
                </div>
                <li  class="nav-item border-line">
                  <a class="nav-link" href="read_caisse.php"><img src="ilo/caisse.png"> Caisse</a>
                </li>
                <li id="menu-active" class="nav-item border-line">
                  <a class="nav-link" href="read_vente.php"><img src="ilo/vente-blanc.png"> Vente</a>
                </li>
                <li class="nav-item border-line">
                  <a class="nav-link" href="read_facture.php"><img src="ilo/fichier.png"> Fichier</a>
                </li>
                <li class="nav-item border-line">
                  <a class="nav-link" href="read_employe.php"><img src="ilo/profil.png"> Employés</a>
                </li>               
             </ul>       
        </nav>
        <div id="aide">
                <a class="nav-link" href="#"><i class="far fa-question-circle"></i> Aides</a>   
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
                <a class="nav-link compte" href="#"><img src="ilo/icons-100.png"> <?=$_SESSION['name']?></a>
              </li>
            </ul>
          </div>          
          <div class="collapse navbar-collapse" id="collapsibleNavbar">
            <ul class="navbar-nav">
              <li class="nav-item">
                <a id="configuration" class="nav-link" href="#"><i class="fas fa-cog"></i> Configuration</a>
              </li>
              <li class="nav-item">
                <a class="nav-link power-off" href="logout.php"><i class="fas fa-power-off"></i></a>
              </li>           
            </ul>
          </div>
        </nav>      
        <!-- CONTENU -->
        <div id="container-page" class="container">
            <div id="search-cadre" class="row">
                <div class="col-md-6">
                    <h3><i id="fa-user-circle" class="fas fa-user-circle"></i> GESTION VENTE</h3>
                </div>
                <div class="col-md-6">
                  <form action="" method="get">
                    <div id="input-group" class="input-group">
                      <input id="search" type="text" class="form-control" name="q" placeholder="Rechercher" value="<?= htmlentities($_GET['q'] ?? null) ?>"><button class="fa-search-btn"><i class="fas fa-search"></i></button>
                    </div>
                  </form>                   
                </div>           
            </div>
    <!-- FIN FONCTION -->    


                <!-- navigation --> 
            <div id="navigation" class="row">
                <h5><i class='fas fa-home'></i> / Vente</h5>
            </div>
                <!-- Fin navigation --> 
        <div id="cadre-contenu">
        	<h2>Read VENTE</h2>
        	<a href="add_vente.php" class="create-contact">Create VENTE</a>
        	<table class="table">
                <thead class="thead-dark">
                    <tr>
                        <td>#</td>
                        <td><?= tri('nom_produit', 'Designation', $_GET) ?></td>
                        <td><?= tri('prix_unitaire', 'Prix Unitaire', $_GET) ?></td>
                        <td><?= tri('quantite', 'Quantité', $_GET) ?></td>
                        <td><?= tri('prix_totale', 'Prix Totale', $_GET) ?></td>
                        <td><?= tri('benefice', 'Bénéfice', $_GET) ?></td>
                        <td><?= tri('date_vente', 'Date Vente', $_GET) ?></td>
                        <td>Heure Vente</td>
                        <td><?= tri('client', 'Client', $_GET) ?></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventes as $vente): ?>
                    <tr>
                        <td align='right'><?= ++$ligne?></td>
                        <td><?=$vente['NOM_PRODUIT']." ".$vente['REFERENCE']." ".$vente['QUANTITE_UNITE']." ".$vente['UNITE']?></td>
                        <td><?=$vente['PRIX_UNITAIRE']?></td>
                        <td><?=$vente['QUANTITE']?></td>
                        <td><?=$vente['PRIX_TOTALE']?></td>
                        <td><?=$vente['BENEFICE']?></td>
                        <td><?=$vente['DATE_VENTE']?></td>
                        <td><?=$vente['HEURE_VENTE']?></td>
                        <td><?=$vente['CLIENT']?></td>
                        <td class="actions">
                            <a href="update_vente.php?id=<?=$client['ID_VENTE']?>" class="edit"><i class="fas fa-pen fa-xs"></i></a>
                            <a href="delete_vente.php?id=<?=$client['ID_VENTE']?>" class="trash"><i class="fas fa-trash fa-xs"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <!-- PAGINATION -->
                <?php if ($page > $nbre_page): ?>
                  <?=$vide?>
                <?php endif; ?>
                <div class="pied">
                  <ul class="pagination">
                    <?php if ($page > 1): ?>
                    <li class="prev"><a href="read_vente.php?page=<?=$page-1?>"><i class="fas fa-angle-double-left fa-sm"></i> Précédent</a></li>
                    <?php else : ?>
                    <li class="prev disable"><a href="read_vente.php?page=<?=$page-1?>"><i class="fas fa-angle-double-left fa-sm"></i> Précédent</a></li>
                    <?php endif; ?>
                    <?php for($i = 1; $i <= $nbre_page; $i++) : ?>
                      <?php if($i == $page) : ?>
                        <li class="num_page active"><a href="read_vente.php?page=<?= $i; ?>"><?= $i; ?></a></li>
                      <?php else : ?>
                        <li class="num_page"><a href="read_vente.php?page=<?= $i; ?>"><?= $i; ?></a></li>
                      <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($page*$records_per_page < $num_vente): ?>
                    <li class="next"><a href="read_vente.php?page=<?=$page+1?>">Suivant <i class="fas fa-angle-double-right fa-sm"></i></a></li>
                    <?php else : ?>
                    <li class="next disable"><a href="read_vente.php?page=<?=$page+1?>">Suivant <i class="fas fa-angle-double-right fa-sm"></i></a></li>
                    <?php endif; ?>
                  </ul>
                </div>
            <!-- FIN PAGINATION -->
        	 
        </div>


<?=template_footer()?>
