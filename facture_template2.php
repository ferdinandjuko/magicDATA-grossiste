<?php 
include 'functions.php';

# Démarrage de la session
session_start(); 

// VERIFICATION SI L'UTILISATEUR EXISTE TOUJOURS DANS LA BASE DE DONNÉE
verif_session();
# Si la session n'est pas définie, rediriger vers la page de connexion, si l'url est prédéfinie avant la connexion rediriger vers l'url prédéfinie après la connexion
session_url();

// Connect to MySQL database
$pdo = pdo_connect_mysql();

// Check if the contact id exists, for example update.php?id=1 will get the contact with the id of 1
if (isset($_GET['facture_id']) ) {
    # code...
    $_SESSION['id_viewfacture'] = $_GET['facture_id'];
    // Records created successfully. Redirect to landing page;
    header("location: facture_template2.php");
    exit();
} 


    // Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page
    $query = "SELECT * FROM facture WHERE ID_FACTURE=".$_SESSION['id_viewfacture'];

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    // Fetch the records so we can display them in our template.
    $factures = $stmt->fetch(PDO::FETCH_ASSOC);


    // Prepare the SQL statement of CLIENTS
    $query2 = "SELECT * FROM client WHERE ID_CLIENT=".$factures['CLIENT_RESP'];
    $stmt2 = $pdo->prepare($query2);
    $stmt2->execute();
    // Fetch the records so we can display them in our template.
    $client = $stmt2->fetch(PDO::FETCH_ASSOC);

    //if session not define
    /*if (!isset($_SESSION['id_viewfacture'])) {
      header("location: read_facture.php");
    } */ 

require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

ob_start();
?>

<html>
<head>
    <meta charset="utf-8">
    <title>pdf viewer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <link href="http://localhost/ilo_grossiste/bootstrap-4.5.3/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>

    <link rel="stylesheet" href="http://localhost/ilo_grossiste/facture css.css">
    <link rel="icon" type="image/PNG" href="http://localhost/ilo_grossiste/favicon.png">
      <!-- JQUERY FORM VALIDATION -->
    <script src="http://localhost/ilo_grossiste/scripts/jquery-3.5.1.min.js" type="text/javascript"></script><script src="http://localhost/ilo_grossiste/scripts/bootstrap.min.js"></script>        
    <script src="scripts/main.js"></script>  
</head>
<!-- <body> -->
  <!-- <div class="container"> -->
    <!-- MODELE FACTURE -->
        <div class="row d-flex">
          <div class="col-7">
            <!-- <img src="img/img2.png" class="logo-facture float-left"> -->
            <img src="http://localhost/ilo_grossiste/img/img2.png" class="logo-facture float-left">
            
          </div>
          <div class="col-5 position-right">
            <h1 class="document-type display-4">FACTURE</h1>
            <p class="text-right"><strong>N° <?=$factures['ID_FACTURE'] ;?></strong></p>
          </div>
        </div>
        <div class="row d-flex">
            <p class="col-7 position-right">
              <strong>90TECH SAS</strong><br>
              6B Rue Aux-Saussaies-Des-Dames<br>
              57950 MONTIGNY-LES-METZ
            </p>
            <br><br><br>
            <p  class="col-5" >
              <strong><?=$client['NOM'] ;?></strong><br>
              <?=$client['COURRIEL'] ;?><br>
              <?=$client['TEL'] ;?><br>
              <?=$client['ADRESSE'] ;?><br>
              <?=$client['VILLE'] ;?> <?=$client['PAYS'] ;?><br>
            </p>
        </div>
        <br>

        <table class="table table-striped">
          <thead>
            <tr>
              <th class="text-left">Designation</th>
              <th class="text-left">Quantité</th>
              <th class="text-left">PU HT</th>
              <th class="text-left">TVA</th>
              <th class="text-left">Total HT</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="text-left"><?=$factures['DESIGNATION'] ;?></td>
              <td class="text-left"><?=$factures['QUANTITE'] ;?></td>
              <td class="text-left"><?=$factures['PRIX_UNITAIRE'] ;?>Ar</td>
              <td class="text-left">20%</td>
              <td class="text-left"><?=$factures['PRIX_TOTALE'] ;?>Ar</td>
            </tr>
          </tbody>
        </table>
        <div class="row">
          <div class="col-8">
          </div>
          <div class="col-4" style="right: 0;">
            <table class="table table-sm text-right">
              <tr class="text-left">
                <td><strong>Total HT</strong></td>
                <td><?=$factures['PRIX_TOTALE'] ;?>Ar</td>
              </tr>
              <tr class="text-left">
                <td><strong>TVA 20%</strong></td>
                <td>740,00€</td>
              </tr>
              <tr class="text-left">
                <td><strong>Total TTC</strong></td>
                <td>4 440,00€</td>
              </tr>
            </table>
          </div>
        </div>
        <br>
        <br>
        <br>
        <p class="conditions text-left">
          En votre aimable règlement
          <br>
          Et avec nos remerciements.
          <br><br>
          Conditions de paiement : paiement à réception de facture, à 15 jours.
          <br>
          Aucun escompte consenti pour règlement anticipé.
          <br>
          Règlement par virement bancaire.
          <br><br>
          En cas de retard de paiement, indemnité forfaitaire pour frais de recouvrement : 178 360,86 Ar (art. L.4413 et L.4416 code du commerce).
        </p>

        <p class="bottom-page text-right">
          90TECH SAS - N° SIRET 80897753200015 RCS METZ<br>
          6B, Rue aux Saussaies des Dames - 57950 MONTIGNY-LES-METZ 03 55 80 42 62 - www.90tech.fr<br>
          Code APE 6201Z - N° TVA Intracom. FR 77 808977532<br>
          IBAN FR76 1470 7034 0031 4211 7882 825 - SWIFT CCBPFRPPMTZ
        </p>

  <!-- </div>  -->

<!-- </body> -->
</html>


<?php
$txt =ob_get_clean();


$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$dompdf->loadHtml($txt);
$dompdf->setPaper('A4');
$dompdf->render();
$dompdf->stream("facture",array("Attachment" => 1));



?>


