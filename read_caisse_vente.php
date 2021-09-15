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
$query = "SELECT caisse_vente.ID_CAISSE_VENTE, caisse_vente.ID_GESTION_CAISSE, caisse_vente.ID_VENTE, caisse_vente.DATE_AJOUT, caisse_vente.HEURE, caisse_vente.DESIGNATION, caisse_vente.SORTIE, caisse_vente.VENDU_PAR FROM client, gestion_stock, vente, caisse_vente WHERE (client.ID=".$_SESSION['id'].") AND (client.ID_CLIENT=vente.ID_CLIENT) AND (gestion_stock.ID_GESTION=vente.ID_GESTION) AND (vente.ID_VENTE=caisse_vente.ID_VENTE)";
$params = $search= "";
$sortable = ["date_ajout", "designation", "sortie", "vendu_par"];
$pag = "";
$ligne = 0;
for ($x=1;$x < $page;$x++) {
    $ligne =$ligne+$records_per_page;
}

//recherche par nom
if (!empty($_GET['q'])){
    $params = "'%" . $_GET['q'] . "%'";
    $search = " AND (caisse_vente.DATE_AJOUT LIKE $params OR caisse_vente.DESIGNATION LIKE $params OR caisse_vente.SORTIE LIKE $params OR caisse_vente.TOTALE_SOMME LIKE $params OR caisse_vente.VENDU_PAR LIKE $params)";
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
$caisse_ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of VENTE
$num_caisse_vente = $pdo->query("SELECT COUNT(*) FROM client, gestion_stock, vente, caisse_vente WHERE (client.ID=".$_SESSION['id'].") AND (client.ID_CLIENT=vente.ID_CLIENT) AND (gestion_stock.ID_GESTION=vente.ID_GESTION) AND (vente.ID_VENTE=caisse_vente.ID_VENTE)".$search)->fetchColumn();

$numero_page = "";
$currentPage = ($page-1)*$records_per_page;
$nbre_page = ceil($num_caisse_vente / $records_per_page);
$before_page = $page - 1;
$after_page = $page + 1;
$vide = "<h3 class='tableau-vide-h3'>Aucune caisse de vente ajouté pour le moment</h3>";
?>

<?=template_header('Caisse vente')?>
<?=template_content('caisse')?>

        <!-- CONTENU -->
        <div id="container-page" class="container">
            <div id="search-cadre" class="row">
                <div class="col-md-6">
                    <h3><i id="fa-user-circle" class="fas fa-user-circle"></i> CAISSE VENTE</h3>
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
                <h5><i class='fas fa-home'></i> / Caisses / Caisses ventes</h5>
            </div>
                <!-- Fin navigation --> 
        <div id="cadre-contenu">
                <div class="cadre-title">
                  <h2><i class="fas fa-play"></i> CAISSES VENTES</h2>
                  <div class="cadre-autre-title">
                      <h2><a class="autre-btn" href="read_caisse.php"><i class="fas fa-layer-group"></i> Voir Gestion caisse</a></h2> 
                      <h2><a class="autre-btn" href="read_caisse_stock.php"><i class="fas fa-stream"></i> Voir Caisse stock</a></h2>
                  </div>
                </div>
        	<table id="table" class="table">
                    <thead class="thead-facture">
                    <tr>
                        <td>#</td>
                        <td><?= tri('date_ajout', 'Date d\'ajout', $_GET) ?></td>
                        <td>Heure</td>
                        <td><?= tri('designation', 'Designation', $_GET) ?></td>
                        <td><?= tri('sortie', 'Sortie', $_GET) ?></td>
                        <td><?= tri('vendu_par', 'Vendu par', $_GET) ?></td>              
                    </tr>
                </thead>
                <tbody class="tbody">
                    <?php foreach ($caisse_ventes as $caisse_vente): ?>
                    <tr>
                        <td align='right'><?= ++$ligne?></td>
                        <td><?=$caisse_vente['DATE_AJOUT']?></td>
                        <td><?=$caisse_vente['HEURE']?></td>
                        <td><?=$caisse_vente['DESIGNATION']?></td> 
                        <td><?=$caisse_vente['SORTIE']?></td>
                        <td><?=$caisse_vente['VENDU_PAR']?></td>                                        
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($num_caisse_vente < 1 || $page > $nbre_page): ?>
              <?=$vide?>
            <?php endif; ?>
            <br>          
            <!-- //// PAGINATION //// -->
                   <?= template_pagination('caisse_vente') ?>
            <!-- FIN PAGINATION -->  
            <br>
        	 
        </div>


<?=template_footer()?>
