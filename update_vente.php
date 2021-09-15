
<?php
include 'functions.php';
session_start();
$pdo = pdo_connect_mysql();
$msg = '';


// VERIFICATION SI L'UTILISATEUR EXISTE TOUJOURS DANS LA BASE DE DONNÉE
verif_session();
# Si la session n'est pas définie, rediriger vers la page de connexion, si l'url est prédéfinie avant la connexion rediriger vers l'url prédéfinie après la connexion
session_url();


   // Define variables and initialize with empty values
    $statut = $client= "";
    $statut_err = $client_err= "";

    $icon_type= "<i class='fas fa-exclamation-triangle'></i>";


// Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page
$stmt = $pdo->prepare("SELECT * FROM client WHERE ID=".$_SESSION['id']);
$stmt->execute();
// Fetch the records so we can display them in our template.
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
 

// Check if the contact id exists, for example update.php?id=1 will get the contact with the id of 1
if (isset($_SESSION['id_vente'])) {
    // Get the stock from the stock table
    $sql= "SELECT * FROM vente WHERE ID_VENTE = "."'".$_SESSION['id_vente']."'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $vente = $stmt->fetch(PDO::FETCH_ASSOC);
    $statut = $vente['STATUT'];
    // si le id vente n'existe pas ou si le satut de la vente est PAYEE 
    if ( (!$vente) || ($_SESSION['user'] <> "admin" && $statut == "paye" ) ) {
        header("location: read_vente.php");
        exit();        
    }  
} else {
      header("location: read_vente.php");
    exit();    
}

    if (isset($_POST['submit'])) {

        function data_input($data) {
              $data = trim($data);
              $data = stripslashes($data);
              $data = htmlspecialchars($data);
              return $data;
            }
        

        // Validate CLIENT
        $input_client = data_input($_POST["client"]);
        if(empty($input_client)){
            $client_err = "Veuillez selectionner un client";
        } else {
            $client = $input_client;
            $id_client = $client ;
        }

        // Validate STATUT
        $input_statut = data_input($_POST["statut"]);
        if(empty($input_statut)){
            $statut_err = "Veuillez selectionner un statut";
        }elseif (!preg_match("/^[a-zA-Z-' ]*$/",$input_statut)) {
           $statut_err = "Seulement les lettres et les chiffres sont acceptés";
        } else {
            $statut = $input_statut;
        }        

        // SELECT NOM CLIENT   

        $sql_nom_clt = "SELECT * FROM client WHERE ID_CLIENT ='$client'";
        $query_nom_clt = $pdo->prepare($sql_nom_clt);
        $query_nom_clt->execute();
        $row_nom_clt = $query_nom_clt->fetch(PDO::FETCH_BOTH);
        $nom_client = $row_nom_clt['NOM'];

        // Check input errors before inserting in database
        if(empty($statut_err) && empty($client_err) && ($statut <> $vente['STATUT'] || $nom_client <> $vente['CLIENT']) ) {

                // prepare sql and bind parameters
                $sql="UPDATE vente SET STATUT = :STATUT, ID_CLIENT = :ID_CLIENT, CLIENT = :NOM_CLIENT WHERE ID_VENTE = ".$_SESSION['id_vente'];
                var_dump($sql);
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':STATUT', $statut);
                $stmt->bindParam(':ID_CLIENT', $id_client);
                $stmt->bindParam(':NOM_CLIENT', $nom_client);            
                // Update the record
                $ex_update_vente = $stmt->execute();

            } else {
                $ex_update_vente = false;
            }

        #INSERTION HISTORIQUE MODIFICATION VENTE

        if($ex_update_vente <> false ) {
              
            // Définitions des varialbes pour l'insertion dans l'historique
            $id_resp = $_SESSION['id'];
            $date_historique = date("Y-m-d");
            $heure_historique = date("H:i:s");
            $action = "modifié une vente";
            $type = "modification";
            $ajouter_par = $vente['AJOUTE_PAR'];
            $designation = $vente['NOM_PRODUIT']." ".$vente['REFERENCE']." ".$vente['QUANTITE_UNITE'].$vente['UNITE'];
            $designation =  ucwords($designation);
            $modifier_par = $_SESSION['name'];


            if ($statut <> $vente['STATUT'] && $nom_client == $vente['CLIENT'] ) {
                // Si le STATUT a été modifié
                $avant = "Statut : ".$vente['STATUT'];
                $apres = "Statut : ".$statut;
                $statut = strtoupper($statut);

            } elseif ($nom_client <> $vente['CLIENT'] && $statut == $vente['STATUT'] ) {
                // Si le CLIENT a été modifié
                $avant = "Client : ".$vente['CLIENT'];
                $apres = "Client : ".$nom_client;

            } else {
                // Si le STATUT et le CLIENT a été modifié
                $avant = "Client : ".$vente['CLIENT']." <br> "."Statut : ".$vente['STATUT'];
                $apres = "Client : ".$nom_client." <br> "."Statut : ".$statut;
            }

            
              // prepare sql and bind parameters
              $sql="INSERT INTO historique (ID, DATE_HISTORIQUE, HEURE_HISTORIQUE, ACTION, TYPE, AJOUTER_PAR, DESIGNATION, AVANT, APRES, MODIFIER_PAR)
              VALUES (:ID, :DATE_HISTORIQUE, :HEURE_HISTORIQUE, :ACTION, :TYPE, :AJOUTER_PAR, :DESIGNATION, :AVANT, :APRES, :MODIFIER_PAR)";
              $stmt = $pdo->prepare($sql);
              $stmt->bindParam(':ID', $id_resp);
              $stmt->bindParam(':DATE_HISTORIQUE', $date_historique);
              $stmt->bindParam(':HEURE_HISTORIQUE', $heure_historique);                    
              $stmt->bindParam(':ACTION', $action);
              $stmt->bindParam(':TYPE', $type);
              $stmt->bindParam(':AJOUTER_PAR', $ajouter_par);
              $stmt->bindParam(':DESIGNATION', $designation);
              $stmt->bindParam(':AVANT', $avant);
              $stmt->bindParam(':APRES', $apres);
              $stmt->bindParam(':MODIFIER_PAR', $modifier_par);
              // Update the record
              $ex_historique = $stmt->execute(); 

            # DEFINITION NOTIFICATION SWEET ALERT 

                // Records created successfully. Redirect to landing page
                $_SESSION['alert_icon'] = "success";
                $_SESSION['alert_title'] = "Modification vente terminée";

                if ($statut <> $vente['STATUT'] && $nom_client == $vente['CLIENT'] ) {
                    $statut = strtoupper($statut);
                    // code...
                    $_SESSION['alert_text'] = "Le STATUT de la vente à été modifier comme \'".$statut."\' !";

                } elseif ($nom_client <> $vente['CLIENT'] && $statut == $vente['STATUT'] ) {
                    $nom_client = strtoupper($nom_client);
                    $_SESSION['alert_text'] = "Le CLIENT à été modifier comme \'".$nom_client."\' !";

                } else {

                    $statut = strtoupper($statut);
                    $nom_client = strtoupper($nom_client);
                    $_SESSION['alert_text'] = "Le STATUT et le CLIENT de la vente à été modifier comme \'".$statut."\', \'".$nom_client."\' ! ";
                }
                                    
                header("location: update_vente.php");
                exit();              

        } elseif ( ($statut == $vente['STATUT'] && $nom_client == $vente['CLIENT']) ) {
            // code...
             $msg="Something went wrong. Please try again later.";
                $_SESSION['alert_icon'] = "info";
                $_SESSION['alert_title'] = "Aucune modification";
                $_SESSION['alert_text'] = "Aucune modification enregistrée";            

        } else {
                $msg="Something went wrong. Please try again later.";
                $_SESSION['alert_icon'] = "error";
                $_SESSION['alert_title'] = "Erreur";
                $_SESSION['alert_text'] = "Un problème est survenu. Veuillez réessayer plus tard ou veuillez vous reconnecter";
            }
        
    }


?>

<?=template_header('Modification vente')?>
<?=template_content('vente')?>

        <!-- CONTENU -->
        <div id="container-page" class="container">
    <!-- FIN FONCTION -->  
            <!-- navigation --> 
            <div id="navigation" class="row">
                <h5><a href="index.php"><i class='fas fa-home'></i></a> / Vente / Modification Vente</h5>
                <div class="navigation-retour-active">
                  <a href="read_vente.php"><i class="fas fa-chevron-circle-left"></i></a>
                </div>
                <div class="navigation-retour-non-active">
                  <a href=""><i class="fas fa-chevron-circle-right"></i></a>
                </div>                 
            </div>
                <!-- Fin navigation --> 
            <div id="cadre-contenu">
                <div class="cadre-title">
                    <h2><i class="fas fa-plus-circle"></i> MODIFIER UNE VENTE</h2>
                </div>
                <div class="container mt-4 mb-5">
                    <form name="myForm" id="form-add3" method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                  <label for="nom_produit">NOM DE PRODUIT</label>
                                  <input id="disabled" class="align-center disabled" type="text" value="<?=ucwords($vente['NOM_PRODUIT'])?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="prix_unitaire">PRIX UNITAIRE</label>
                                    <input class="align-center disabled" type="text" value="<?=$vente['PRIX_UNITAIRE']." "."Ar"?>" readonly>
                                </div> 
                                <div class="form-group">
                                    <label for="quantite">QUANTITE</label>
                                    <input class="align-center disabled" type="text" value="<?=$vente['QUANTITE']?>" readonly>                                   
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="statut">STATUT</label>
                                    <?php if ($vente['STATUT'] == "paye") :?>
                                        <select class="custom-select"  id="statut" onchange="getSelectValue();" oninput="controlInputSTATUT();" name="statut">
                                            <option class="pre_selection" value="paye">PAYE</option>
                                            <option value="non-paye">NON-PAYE</option>
                                        </select>
                                    <?php else : ?>
                                        <select class="custom-select"  id="statut" onchange="getSelectValue();" oninput="controlInputSTATUT();" name="statut">
                                            <option class="pre_selection" value="non-paye">NON-PAYE</option>
                                            <option value="paye">PAYE</option>                                            
                                        </select>                                        
                                    <?php endif; ?>
                                    <?php if(!empty($statut_err)) : ?>
                                    <p id="erreur-statut" class="erreur-p"><?= $icon_type ." ". $statut_err?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                  <label for="client">CLIENT</label>                                
                                  <select class="custom-select mr-sm-2"  id="input_client" onchange="getSelectValue();" oninput="controlInputCLIENT();" name="client">
                                  <!-- Si la session faire une vente est définie -->  
                                    <?php if (isset($vente['CLIENT'])  ) :?>
                                        <option class="pre_selection" value="<?=$vente['ID_CLIENT']?>"><?=ucwords($vente['CLIENT'])?></option>
                                        <?php foreach ($clients as $client): ?> 
                                            <option value="<?=$client['ID_CLIENT']?>"><?=ucwords($client['NOM'])?></option>
                                        <?php endforeach; ?>   
                                    <?php else : ?>
                                        <option value="vide">--- CHOISIR UN CLIENT --- *</option>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?=$client['ID_CLIENT']?>"><?=ucwords($client['NOM'])?></option>
                                        <?php endforeach; ?>   
                                    <?php endif; ?>                                   
                                  </select>
                                    <?php if(!empty($client_err)) : ?>
                                    <p id="erreur-client" class="erreur-p"><?= $icon_type ." ". $client_err?></p>
                                    <?php endif; ?>                                  
                                </div>
                                <div id="add-btn" class="form-group">
                                  <input class="btn btn-primary" type="submit" name="submit" value="+ AJOUTER">  
                                </div>
                            </div>                                 
                        </div>   
                    </form>          
                </div>
                
            </div>
        </div>
<?=sweet_alert_notification()?>
<?=template_footer()?>