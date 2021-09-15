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
    header("location: view_facture.php");
    exit();
} 


    // Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page
    $query = "SELECT * FROM facture WHERE ID_FACTURE=".$_SESSION['id_viewfacture'];

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    // Fetch the records so we can display them in our template.
    $facture = $stmt->fetch(PDO::FETCH_ASSOC);


    // Prepare the SQL statement of CLIENTS
    $query2 = "SELECT * FROM client WHERE ID_CLIENT=".$facture['ID_CLIENT'];
    $stmt2 = $pdo->prepare($query2);
    $stmt2->execute();
    // Fetch the records so we can display them in our template.
    $client = $stmt2->fetch(PDO::FETCH_ASSOC);

    //if session not define
    if (!isset($_SESSION['id_viewfacture'])) {
      header("location: read_facture.php");
    }

      
?>

<?=template_header('Detail facture')?>

<?=template_content('fichier')?>
        
        <!-- CONTENU -->
        <div id="container-page" class="container">
    <!-- FIN FONCTION -->  
            <!-- navigation --> 
            <div id="navigation" class="row">
                <h5><a href="index.php"><i class='fas fa-home'></i></a> / Inventaire / Historiques Stocks / Détails facture</h5>
                <div class="navigation-retour-active">
                  <a href="read_facture.php"><i class="fas fa-chevron-circle-left"></i></a>
                </div>
                <div class="navigation-retour-non-active">
                  <a href=""><i class="fas fa-chevron-circle-right"></i></a>
                </div>                 
            </div>
                <!-- Fin navigation --> 
            <div id="cadre-contenu">
                <div class="cadre-title">
                    <h2><i class="fas fa-plus-circle"></i> DÉTAILS DE LA FACTURE</h2>
                    <div class="cadre-autre-title">
                      <h2><a class="download-pdf" onclick="printfacture()" href=""><img src="ilo/pdf.png"> Imprimer</a></h2>
                      <h2><a class="download-pdf" onclick="" href="facture_template2.php?facture_id=<?=$facture['ID_FACTURE']?>"><img src="ilo/pdf.png"> Télécharger</a></h2>
                      
                  </div>
                </div>
                <div class="container">
                    <form name="myForm" id="form-add4" action="" method="post">
                        <div class="row">
                            <div class="col-md-3" id="limit_detail">
                                <div class="form-group"> 
                                       <label class="mx-auto" for="date_echeance">DATE ECHEANCE</label>
                                       <p class="mx-auto"><?=$facture['DATE_ECHEANCE'] ;?></p>
                                </div>
                                <hr>
                                <div class="mt-n2 form-group"> 
                                       <label class="mx-auto" for="heure_echeance">HEURE ECHEANCE</label>
                                       <p class="mx-auto"><?=$facture['HEURE_ECHEANCE'] ;?></p> 
                                </div>
                                <hr>
                                <div class="mt-n2 form-group"> 
                                       <label class="mx-auto" for="client">CLIENT</label> 
                                       <p class="mx-auto"><?=$facture['CLIENT'] ;?></p>
                                </div>
                                <hr>
                                <div class="mt-n2 form-group"> 
                                       <label class="mx-auto" for="designation">DESIGNATION</label>
                                       <p class="mx-auto"><?=$facture['DESIGNATION'] ;?></p> 
                                </div>
                                <hr> 
                                <div class="mt-n2 form-group"> 
                                       <label class="mx-auto" for="quantite">QUANTITE</label> 
                                       <p class="mx-auto"><?=$facture['QUANTITE'] ;?></p>
                                </div> 
                                <hr>                             
                                <div class="mt-n2 form-group"> 
                                       <label class="mx-auto" for="prix_unitaire">PRIX UNITAIRE</label>
                                       <p class="mx-auto"><?=$facture['PRIX_UNITAIRE'] ;?></p> 
                                </div>
                                <hr>
                                <div class="mt-n2 form-group"> 
                                       <label class="mx-auto" for="prix totale">PRIX TOTALE</label>
                                       <p class="mx-auto"><?=$facture['PRIX_UNITAIRE']*$facture['QUANTITE'] ;?></p> 
                                </div>
                                <hr>
                                <div class="mt-n2 form-group"> 
                                       <label class="mx-auto" for="statut">STATUT</label> 
                                       <p class="mx-auto"><?=$facture['STATUT'] ;?></p>
                                </div> 
                                <hr>                              
                            </div>
                            <!-- <a class="download-pdf" href=""><img src="ilo/pdf.png"> Télécharger</a> -->
                            <div class="col-md-9 pdf-downloader">
                                <!-- MODELE FACTURE -->
                                  <div class="invoice">
                                    <div class="row">
                                      <div class="col-7">
                                        <img src="img/img2.png" class="logo-facture float-left">
                                      </div>
                                      <div class="col-5">
                                        <h1 class="document-type display-4">FACTURE</h1>
                                        <p class="text-right"><strong>N° <?=$facture['ID_FACTURE'] ;?></strong></p>
                                      </div>
                                    </div>
                                    <div class="row">
                                      <div class="col-7 text-left">
                                        <p>
                                          <strong>90TECH SAS</strong><br>
                                          6B Rue Aux-Saussaies-Des-Dames<br>
                                          57950 MONTIGNY-LES-METZ
                                        </p>
                                      </div>
                                      <div class="col-5 text-left">
                                        <br><br><br>
                                        <p>
                                          <strong><?=$client['NOM'] ;?></strong><br>
                                          <?=$client['COURRIEL'] ;?><br>
                                          <?=$client['TEL'] ;?><br>
                                          <?=$client['ADRESSE'] ;?><br>
                                          <?=$client['VILLE'] ;?> <?=$client['PAYS'] ;?><br>
                                        </p>
                                      </div>
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
                                          <td class="text-left"><?=$facture['DESIGNATION'] ;?></td>
                                          <td class="text-left"><?=$facture['QUANTITE'] ;?></td>
                                          <td class="text-left"><?=$facture['PRIX_UNITAIRE'] ;?>Ar</td>
                                          <td class="text-left">20%</td>
                                          <td class="text-left"><?=$facture['PRIX_UNITAIRE']*$facture['QUANTITE'];?>Ar</td>
                                        </tr>
                                      </tbody>
                                    </table>
                                    <div class="row">
                                      <div class="col-8">
                                      </div>
                                      <div class="col-4">
                                        <table class="table table-sm text-right">
                                          <tr class="text-left">
                                            <td><strong>Total HT</strong></td>
                                            <td><?=$facture['PRIX_UNITAIRE']*$facture['QUANTITE'];?>Ar</td>
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
                                  </div>

                            </div> 
                        </div>   
                    </form>          
                </div>
                
            </div>
        </div>

<script>
  $(document).ready(printfacture() {
    var URL = "http://localhost/ilo_grossiste/facture_template3.php?facture_id=<?=$facture['ID_FACTURE']?>";
    var W = window.open(URL);
    W.window.print();
  });
</script>

<?=template_footer()?>
