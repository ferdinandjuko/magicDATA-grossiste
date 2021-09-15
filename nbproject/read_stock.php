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
$records_per_page = 5;

// Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page
$query = "SELECT * FROM stock, fournisseur WHERE (stock.ID_FOURNISSEUR=fournisseur.ID_FOURNISSEUR) AND fournisseur.ID=".$_SESSION['id'];
$params = $search= "";
$sortable = ["date_ajout", "nom_produit", "reference","prix_unitaire","prix_totale" ,"date_achat", "fournisseur", "ajoute_par"];
$pag = "";
$ligne = 0;
for ($x=1;$x < $page;$x++) {
    $ligne =$ligne+5;
}

//recherche par nom
if (!empty($_GET['q'])){
    $params = "'%" . $_GET['q'] . "%'";
    $search = " AND (stock.NOM_PRODUIT LIKE $params OR stock.DATE_AJOUT LIKE $params OR stock.REFERENCE LIKE $params OR stock.DATE_ACHAT LIKE $params OR stock.FOURNISSEUR LIKE $params OR stock.AJOUTE_PAR LIKE $params)";
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

$stmt = $pdo->prepare($query);
$stmt->bindValue(':current_page', ($page-1)*$records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':record_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
// Fetch the records so we can display them in our template.
$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Get the total number of stock
$num_stock = $pdo->query("SELECT COUNT(*) FROM stock, fournisseur WHERE (stock.ID_FOURNISSEUR=fournisseur.ID_FOURNISSEUR) AND fournisseur.ID=".$_SESSION['id'].$search)->fetchColumn();

$numero_page = "";
$currentPage = ($page-1)*$records_per_page;
$nbre_page = ceil($num_stock / $records_per_page);
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
                <li id="menu-active" class="nav-item border-line">
                  <a id="inventaire-btn" class="nav-link"><img src="ilo/inventaire-blanc.png"> Stocks <i id="inventaire-arrow" class="fas fa-caret-down"></i></a>
                </li>
                <div id="inventaire">
                    <a class="nav-link" href="read_stock.php"><img src="ilo/inventaire.png"> Stocks</a>
                    <a class="nav-link" href="read_deffectueux.php"><img src="ilo/inventaire.png"> Stocks déffectueux</a>
                </div>
                <li  class="nav-item border-line">
                  <a class="nav-link" href="read_caisse.php"><img src="ilo/caisse.png"> Caisse</a>
                </li>
                <li class="nav-item border-line">
                  <a class="nav-link" href="read_vente.php"><img src="ilo/vente.png"> Vente</a>
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
                    <h3><i id="fa-user-circle" class="fas fa-layer-group"></i> GESTION STOCKS</h3>
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
                <h5><i class='fas fa-home'></i> / Stocks</h5>
            </div>
                <!-- Fin navigation --> 
        <div id="cadre-contenu">
            <div>
                <div class="cadre-title">
                    <h2><i class="fas fa-play"></i> Historique Stocks</h2>
                    <div class="cadre-autre-title">
                        <h2><a class="autre-btn" href="read_deffectueux.php"><i class="fas fa-stream"></i> Voir Stock defectueux</a></h2>
                        <h2><a class="autre-btn" href="read_gestion_stock.php"><i class="fas fa-layer-group"></i> Voir Gestion stock</a></h2>                     
                    </div>                   
                </div>
                <div id="cadre-add-new">
                    <a class="add-new" href="add_stock.php" class="create-contact"><i class="fas fa-cart-plus"></i> Ajouter nouveau stock</a>
                </div>

                <table id="table" class="table">
                    <thead>
                        <tr>
                            <td>#</td>
                            <td><?= tri('date_ajout', 'date d\'ajout', $_GET) ?></td>
                            <td><?= tri('reference', 'Designation', $_GET) ?></td>
                            <td><?= tri('prix_unitaire', 'Prix unitaire', $_GET) ?></td>
                            <td>Quantité</td>
                            <td><?= tri('prix_totale', 'Prix totale', $_GET) ?></td>
                            <td><?= tri('date_achat', 'Date d\'achat', $_GET) ?></td>
                            <td><?= tri('fournisseur', 'Fournisseur', $_GET) ?></td>
                            <td>Description</td>
                            <td><?= tri('ajoute_par', 'Ajouté par', $_GET) ?></td>                
                            <td>URL Photo du produit</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stocks as $stock): ?>
                        <tr>
                            <td align='right'><?= ++$ligne?></td>
                            <td><?=$stock['DATE_AJOUT']?></td>
                            <td><?=ucfirst($stock['NOM_PRODUIT'])." ".ucfirst($stock['REFERENCE'])." ".$stock['QUANTITE_UNITE']." ".strtoupper($stock['UNITE'])?></td>
                            <td><?=price($stock['PRIX_UNITAIRE'])?></td>
                            <td><?=$stock['QUANTITE']?></td>
                            <td><?=price($stock['PRIX_TOTALE'])?></td>
                            <td><?=$stock['DATE_ACHAT']?></td>
                            <td><?=$stock['FOURNISSEUR']?></td>
                            <td><?=$stock['DESCRIPTION']?></td>
                            <td><?=$stock['AJOUTE_PAR']?></td>
                            <td><?=$stock['PHOTO']?></td>
                            <td class="actions">
                                <a href="update_stock.php?id=<?=$stock['ID_STOCK']?>" class="edit"><i class="fas fa-pen
                                fa-xs"></i></a>
                                <a href="delete_stock.php?id=<?=$stock['ID_STOCK']?>" class="trash"><i class="fas fa-trash
                                fa-xs"></i></a>
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
                    <li class="prev"><a href="read_stock.php?page=<?=$page-1?>"><i class="fas fa-angle-double-left fa-sm"></i> Précédent</a></li>
                    <?php else : ?>
                    <li class="prev disable"><a href="read_stock.php?page=<?=$page-1?>"><i class="fas fa-angle-double-left fa-sm"></i> Précédent</a></li>
                    <?php endif; ?>
                    <?php for($i = 1; $i <= $nbre_page; $i++) : ?>
                      <?php if($i == $page) : ?>
                        <li class="num_page active"><a href="read_stock.php?page=<?= $i; ?>"><?= $i; ?></a></li>
                      <?php else : ?>
                        <li class="num_page"><a href="read_stock.php?page=<?= $i; ?>"><?= $i; ?></a></li>
                      <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($page*$records_per_page < $num_stock): ?>
                    <li class="next"><a href="read_stock.php?page=<?=$page+1?>">Suivant <i class="fas fa-angle-double-right fa-sm"></i></a></li>
                    <?php else : ?>
                    <li class="next disable"><a href="read_stock.php?page=<?=$page+1?>">Suivant <i class="fas fa-angle-double-right fa-sm"></i></a></li>
                    <?php endif; ?>
                  </ul>
                </div>
            <!-- FIN PAGINATION -->
                  
            </div>
        </div>

<?=template_footer()?>
