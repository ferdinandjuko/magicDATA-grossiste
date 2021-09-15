<?php
include 'functions.php';
session_start();

// VERIFICATION SI L'UTILISATEUR EXISTE TOUJOURS DANS LA BASE DE DONNÉE
verif_session();
# Si la session n'est pas définie, rediriger vers la page de connexion, si l'url est prédéfinie avant la connexion rediriger vers l'url prédéfinie après la connexion
session_url();

// Connect to MySQL database
$pdo = pdo_connect_mysql();
// Get the page via GET request (URL param: page), if non exists default the page to 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
// Number of records to show on each page
$records_per_page = 10;

// Prepare the SQL statement and get records from our VENTE table, LIMIT will determine the page
$query = "SELECT * FROM caisse_stock WHERE SESSION_ID = ".$_SESSION['id'];
$params = $search= "";
$sortable = ["date_ajout", "designation", "entrer", "ajouter_par"];
$pag = "";
$ligne = 0;
for ($x=1;$x < $page;$x++) {
    $ligne =$ligne+$records_per_page;
}

//recherche par nom
if (!empty($_GET['q'])){
    $params = "'%" . $_GET['q'] . "%'";
    $search = " AND (caisse_stock.DATE_AJOUT LIKE $params OR caisse_stock.DESIGNATION LIKE $params OR caisse_stock.ENTRER LIKE $params OR caisse_stock.TOTALE_SOMME LIKE $params OR caisse_stock.AJOUTER_PAR LIKE $params)";
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
$caisse_stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of VENTE
$num_caisse_stock = $pdo->query("SELECT COUNT(*) FROM caisse_stock WHERE SESSION_ID =".$_SESSION['id']."".$search)->fetchColumn();

$numero_page = "";
$currentPage = ($page-1)*$records_per_page;
$nbre_page = ceil($num_caisse_stock / $records_per_page);
$before_page = $page - 1;
$after_page = $page + 1;
$vide = "<h3 class='tableau-vide-h3'>Aucune caisse de stock ajouté pour le moment</h3>";
?>

<?=template_header('Caisse stock')?>
<?=template_content('caisse')?>

        <!-- CONTENU -->
        <div id="container-page" class="container">
            <div id="search-cadre" class="row">
                <div class="col-md-6">
                    <h3><i id="fa-user-circle" class="fas fa-layer-group"></i> CAISSE STOCKS</h3>
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
                <h5><i class='fas fa-home'></i> / Caisse / Caisse Stocks</h5>
            </div>
                <!-- Fin navigation --> 
        <div id="cadre-contenu">
          <div class="cadre-title">
                  <h2><i class="fas fa-play"></i> CAISSES STOCKS</h2>
                  <div class="cadre-autre-title">
                      <h2><a class="autre-btn" href="read_caisse.php"><i class="fas fa-stream"></i> Voir Caisse</a></h2>
                      <h2><a class="autre-btn" href="read_caisse_vente.php"><i class="fas fa-layer-group"></i> Voir Caisse vente</a></h2>                     
                  </div> 
          </div>                  
        	   <table id="table" class="table">
                    <thead class="thead-facture">
                    <tr>
                        <td>#</td>
                        <td><?= tri('date_ajout', 'Date d\'ajout', $_GET) ?></td>
                        <td>Heure</td>
                        <td><?= tri('designation', 'Designation', $_GET) ?></td>
                        <td><?= tri('entrer', 'Entrer', $_GET) ?></td>
                        <td><?= tri('ajouter_par', 'Ajouter par', $_GET) ?></td>              
                    </tr>
                </thead>
                <tbody class="tbody">
                    <?php foreach ($caisse_stocks as $caisse_stock): ?>
                    <tr>
                        <td align='right'><?= ++$ligne?></td>
                        <td><?=$caisse_stock['DATE_AJOUT']?></td>
                        <td><?=$caisse_stock['HEURE']?></td>
                        <td><?=$caisse_stock['DESIGNATION']?></td> 
                        <td><?=$caisse_stock['ENTRER']?></td>
                        <td><?=$caisse_stock['AJOUTER_PAR']?></td>                                        
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
                <?php if ($num_caisse_stock < 1 || $page > $nbre_page): ?>
                  <?=$vide?>
                <?php endif; ?>
                <br>
                <!-- //// PAGINATION //// -->
                   <?= template_pagination('caisse_stock') ?>
                <!-- FIN PAGINATION -->
        	
        </div>


<?=template_footer()?>
