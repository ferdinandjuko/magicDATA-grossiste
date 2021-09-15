<?php

include 'functions.php';
session_start();
$pdo = pdo_connect_mysql();
$msg = '';

// VERIFICATION SI L'UTILISATEUR EXISTE TOUJOURS DANS LA BASE DE DONNÉE
verif_session();
# Si la session n'est pas définie, rediriger vers la page de connexion, si l'url est prédéfinie avant la connexion rediriger vers l'url prédéfinie après la connexion
session_url();



// SELECT * CLIENT

$stmt = $pdo->prepare("SELECT * FROM client WHERE ID=".$_SESSION['id']);
$stmt->execute();
// Fetch the records so we can display them in our template.
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// SELECT * STOCK

$stmt_stock = $pdo->prepare("SELECT * FROM stock WHERE ID=".$_SESSION['id']);
$stmt_stock ->execute();
// Fetch the records so we can display them in our template.
$stocks = $stmt_stock->fetchAll(PDO::FETCH_ASSOC);


// SELECT * GSTOCK

$stmt_gstock = $pdo->prepare("SELECT * FROM gestion_stock WHERE QUANTITE > 0 AND SESSION_ID=".$_SESSION['id']);
$stmt_gstock ->execute();
// Fetch the records so we can display them in our template.
$gstocks = $stmt_gstock->fetchAll(PDO::FETCH_ASSOC);


   // Define variables and initialize with empty values
    $nom_produit= $prix_unitaire= $quantite= $benefice= $date_vente= $heure_vent= $client=  "";
    $nom_produit_err=$prix_unitaire_err=$quantite_err=$benefice_err=$date_vente_err=$heure_vent_err=$client_err= "";
    $icon_type= "<i class='fas fa-exclamation-triangle'></i>";


try {

    // Processing form data when form is submitted
    if(isset($_POST["submit"])) {

        function data_input($data) {
              $data = trim($data);
              $data = stripslashes($data);
              $data = htmlspecialchars($data);
              return $data;
            }
        
        // Validate Nom produit (ID GESTION)
        $input_nom_produit = data_input($_POST["nom_produit"]);

        if ($input_nom_produit == "vide"){
            $nom_produit_err = "Veuillez selectionner un nom de produit";
        }else{
            $nom_produit = $input_nom_produit;
        } 


        // Validate PRIX UNITAIRE
        $input_prix_unitaire = data_input($_POST["prix_unitaire"]);

        if(empty($input_prix_unitaire) || $input_prix_unitaire==0.0){
            $prix_unitaire= 0;
            $prix_unitaire_err = "Prix unitaire vide";
        }elseif (!preg_match("/^[0-9,. ]*$/",$input_prix_unitaire)) {
           $prix_unitaire_err = "Seulement les chiffres sont acceptés pu";
        } else{
            $prix_unitaire = $input_prix_unitaire;
            $prix_unitaire = str_replace("%,%", ".", $prix_unitaire);
        } 
        // Validate QUANTITE
        $input_quantite = data_input($_POST["quantite"]);
        if(empty($input_quantite) || $input_quantite==0.0){
            $quantite = 0;
            $quantite_err="Quantité vide";
        } elseif (!filter_var($input_quantite, FILTER_VALIDATE_INT) === true) {
           $quantite = "Seulement les chiffres sont acceptés";
        } else{
            $quantite = $input_quantite;
        }


        // Validate PRIX TOTAL
        $input_prix_total = floatval($prix_unitaire)*($quantite);
        if(empty($input_prix_total)){
            $prix_total_err = "Format quantité invalide";
        } elseif (!preg_match("/^[0-9,.' ]*$/",$input_prix_total)) {
           $prix_total_err = "Seulement les chiffres sont acceptés pt";
        } else{
            $prix_total = $input_prix_total;
        }                 

        // Validate CLIENT
        $input_client = data_input($_POST["client"]);
        if($input_client == "vide"){
            $client_err = "Veuillez selectionner un client";
        } else {
            $client = $input_client;
        }

        // Validate Statut
        $input_statut = data_input($_POST["statut"]);

        if ($input_statut == "vide"){
            $statut_err = "Veuillez selectionner un statut";
        }else{
            $statut = $input_statut;
        }         

        // Validate DATE_VENTE
        $input_date_vente = date("Y-m-d");

        if(!empty($input_date_vente)){
            $date_vente = $input_date_vente;
        }

        // Validate HEURE_VENTE
        $input_heure_vente = date("H:i:s");

        if(!empty($input_heure_vente)){
            $heure_vente = $input_heure_vente;
        }
        if (empty($nom_produit_err)) {
            // SELECT NOM CLIENT       
            $sql_nom_clt="SELECT * FROM client WHERE ID_CLIENT ='$client'";
            $query_nom_clt = $pdo->prepare($sql_nom_clt);
            $query_nom_clt->execute();
            $row_nom_clt = $query_nom_clt->fetch(PDO::FETCH_BOTH);
            $nom_client = $row_nom_clt['NOM'];
            $id_client =  $client;

            // SELECT NOM PRODUIT GESTION STOCK     
            $sql_nom_pdt="SELECT * FROM gestion_stock WHERE ID_GESTION ='$nom_produit'";
            $query_nom_pdt = $pdo->prepare($sql_nom_pdt);
            $query_nom_pdt->execute();
            $row_nom_pdt = $query_nom_pdt->fetch(PDO::FETCH_BOTH);   

            // SELECT PU GESTION STOCK       
            $sql_gestion_stock="SELECT * FROM gestion_stock WHERE ID_GESTION ='$nom_produit'";
            $query_gestion_stock = $pdo->prepare($sql_gestion_stock);
            $query_gestion_stock->execute();
            $row_gestion_stock = $query_gestion_stock->fetch(PDO::FETCH_BOTH);   
            $id_gestion_stock = $row_gestion_stock['ID_GESTION'];   
            $pu_pdt = $row_gestion_stock['PRIX_UNITAIRE']; 

            // DETECTER SI LE QUANTITÉ DE STOCK EST SUPERIEUR A LA QUANTITE DE PRODUIT EN VENTE
            # Definition variable stock
            $qte_gestion_stock = $row_gestion_stock['QUANTITE'];
            if ($quantite > $qte_gestion_stock) {
                $quantite_err="Le nombre de stock disponible est  <span class='bold'>".$qte_gestion_stock."</span>";
            }

            // Validate BENEFICE
            $input_benefice = ($prix_unitaire)-($pu_pdt);
            if(empty($input_benefice)) {
                $benefice_err = "Format quantité invalide";
            } elseif (!preg_match("/^[0-9,.' ]*$/",$input_benefice)) {
               $benefice_err = "Seulement les chiffres sont acceptés";
            } else{
                $benefice = ($input_benefice)*($quantite);
            }                    
        }


        // Check input errors before inserting in database
        if(empty($nom_produit_err) && empty($nom_ref_err) && empty($nom_unite_err) && empty($nom_qte_unite_err) &&  empty($prix_unitaire_err) && empty($prix_total_err) && empty($quantite_err) && empty($benefice_err) && empty($date_vente_err) && empty($heure_vent_err) && empty($client_err) ) {

                // INSERTION FACTURE

                $id_users=$_SESSION['id'];
                $nom_client=$row_nom_clt['NOM'];

                $nom_pdt=$row_nom_pdt['NOM_PRODUIT'];
                $produit_ref=$row_nom_pdt['REFERENCE'];
                $produit_qte_unite=$row_nom_pdt['QUANTITE_UNITE'];
                $produit_unite=$row_nom_pdt['UNITE'];
                $designation=ucfirst($nom_pdt)." ".ucfirst($produit_ref)." ".$produit_qte_unite." ".strtoupper($produit_unite);

                // prepare sql and bind parameters
                $sql_facture = "INSERT INTO facture (ID_CLIENT, DATE_ECHEANCE, HEURE_ECHEANCE, DESIGNATION, PRIX_UNITAIRE, QUANTITE, STATUT, CLIENT)
                VALUES (:ID_CLIENT, :DATE_ECHEANCE, :HEURE_ECHEANCE, :DESIGNATION, :PRIX_UNITAIRE, :QUANTITE, :STATUT, :CLIENT)";

                $stmt_facture = $pdo->prepare($sql_facture);  

                $stmt_facture->bindParam(':ID_CLIENT', $id_client);
                $stmt_facture->bindParam(':DATE_ECHEANCE', $date_vente);
                $stmt_facture->bindParam(':HEURE_ECHEANCE', $heure_vente);             
                $stmt_facture->bindParam(':DESIGNATION', $designation);
                $stmt_facture->bindParam(':PRIX_UNITAIRE', $prix_unitaire);
                $stmt_facture->bindParam(':QUANTITE', $quantite);
                $stmt_facture->bindParam(':STATUT', $statut);
                $stmt_facture->bindParam(':CLIENT', $nom_client);

                // insert a row
                $ex_facture= $stmt_facture->execute();
                // recupère le ID du facture
                $last_id_facture = $pdo->lastInsertId();

                
            if ($ex_facture <> false) {

                // INSERTION VENTE 
                $ajoute_par = $_SESSION['name'];

                $sql_vente ="INSERT INTO vente (ID_CLIENT, ID_FACTURE, ID_GESTION, NOM_PRODUIT, REFERENCE, QUANTITE_UNITE, UNITE, PRIX_UNITAIRE, QUANTITE, BENEFICE, DATE_VENTE, HEURE_VENTE, CLIENT, STATUT, AJOUTE_PAR)
                VALUES ( :ID_CLIENT, :ID_FACTURE, :ID_GESTION, :NOM_PRODUIT, :REFERENCE, :QUANTITE_UNITE, :UNITE, :PRIX_UNITAIRE, :QUANTITE, :BENEFICE, :DATE_VENTE, :HEURE_VENTE, :CLIENT, :STATUT, :AJOUTE_PAR)";

                $stmt_vente = $pdo->prepare($sql_vente);  
                
                $stmt_vente->bindParam(':ID_CLIENT', $id_client);
                $stmt_vente->bindParam(':ID_FACTURE', $last_id_facture);
                $stmt_vente->bindParam(':ID_GESTION', $id_gestion_stock);
                $stmt_vente->bindParam(':NOM_PRODUIT', $nom_pdt);
                $stmt_vente->bindParam(':REFERENCE', $produit_ref);
                $stmt_vente->bindParam(':QUANTITE_UNITE', $produit_qte_unite);
                $stmt_vente->bindParam(':UNITE', $produit_unite);
                $stmt_vente->bindParam(':PRIX_UNITAIRE', $prix_unitaire);
                $stmt_vente->bindParam(':QUANTITE', $quantite);
                $stmt_vente->bindParam(':BENEFICE', $benefice);
                $stmt_vente->bindParam(':DATE_VENTE', $date_vente);
                $stmt_vente->bindParam(':HEURE_VENTE', $heure_vente);
                $stmt_vente->bindParam(':CLIENT', $nom_client);
                $stmt_vente->bindParam(':STATUT', $statut);
                $stmt_vente->bindParam(':AJOUTE_PAR', $ajoute_par);

                // insert a row
                $ex_vente= $stmt_vente->execute();
                // recupère le ID du facture
                $last_id_vente = $pdo->lastInsertId(); 


            } else {
                exit('ex_facture false');
            }
             
            if ($ex_vente <> false) {

                 # MIS A JOUR GESTION CAISSE

                    $sql_id="SELECT MAX(ID_GESTION_CAISSE) AS max_id FROM gestion_caisse WHERE SESSION_ID=".$_SESSION['id'];
                    $query_id = $pdo->prepare($sql_id);
                    $query_id->execute();
                    $row_id = $query_id->fetch(PDO::FETCH_BOTH);
                    $last_id = $row_id['max_id'];

                    $sql_select="SELECT * FROM gestion_caisse WHERE ID_GESTION_CAISSE="."'".$last_id."'";
                    $query_select = $pdo->prepare($sql_select);
                    $query_select->execute();
                    $row_gestion_stock = $query_select->fetch(PDO::FETCH_BOTH);

                    $solde_actuel_base = $row_gestion_stock['SOLDE_ACTUEL'];
                    
                    $solde_actuel_total =  doubleval($solde_actuel_base) +  doubleval($prix_total);
                    $etat="VENTE (+ Crédit)";
                    $ajoute_par= $_SESSION['name'];
                    $id_resp= $_SESSION['id'];
                    $date_ajout = date("Y-m-d"); 
                    $responsable = $_SESSION['name']; 
                    // MOTIF
                    $motif= $designation." (".$quantite." x ".$prix_unitaire." Ar)";

                    $sql="INSERT INTO gestion_caisse (SESSION_ID, DATE_AJOUT, SOLDE_ACTUEL, SOLDE, ETAT, MOTIF, RESPONSABLE, ID_CLIENT_RESP, CLIENT_RESP)
                    VALUES (:SESSION_ID, :DATE_AJOUT, :SOLDE_ACTUEL, :SOLDE, :ETAT, :MOTIF, :RESPONSABLE, :ID_CLIENT_RESP, :CLIENT_RESP)";
                    $stmt = $pdo->prepare($sql);

                    $stmt->bindParam(':SESSION_ID', $id_resp);
                    $stmt->bindParam(':DATE_AJOUT', $date_ajout);
                    $stmt->bindParam(':SOLDE_ACTUEL', $solde_actuel_total);
                    $stmt->bindParam(':SOLDE', $prix_total);
                    $stmt->bindParam(':ETAT', $etat);
                    $stmt->bindParam(':MOTIF', $motif);
                    $stmt->bindParam(':RESPONSABLE', $responsable);
                    $stmt->bindParam(':ID_CLIENT_RESP', $id_client);
                    $stmt->bindParam(':CLIENT_RESP', $client);

                    // insert a row gestion stock
                    $ex_gestion_caisse= $stmt->execute();
                    $last_id_gestion_caisse = $pdo->lastInsertId();   
               
            } else {
                exit('ex_vente false');
            }

            if ($ex_gestion_caisse <> false) {

                    # INSERSTION CAISSE VENTE

                    // definition des variables
                     $heure = date("H:i:s");
                     $date = date("Y-m-d");
                     $session_id = $_SESSION['id'];
  
                    $sql="INSERT INTO caisse_vente (ID_GESTION_CAISSE, ID_VENTE, DATE_AJOUT, HEURE, DESIGNATION, SORTIE, VENDU_PAR, SESSION_ID)
                    VALUES ( :ID_GESTION_CAISSE, :ID_VENTE, :DATE_AJOUT, :HEURE, :DESIGNATION, :SORTIE, :VENDU_PAR, :SESSION_ID)";
                    
                    $stmt = $pdo->prepare($sql);

                    $stmt->bindParam(':ID_GESTION_CAISSE', $last_id_gestion_caisse);
                    $stmt->bindParam(':ID_VENTE', $last_id_vente);
                    $stmt->bindParam(':DATE_AJOUT', $date);
                    $stmt->bindParam(':HEURE', $heure);
                    $stmt->bindParam(':DESIGNATION', $designation);
                    $stmt->bindParam(':SORTIE', $prix_total);  
                    $stmt->bindParam(':VENDU_PAR', $responsable);
                    $stmt->bindParam(':SESSION_ID', $session_id);

                    // insert a row gestion stock
                    $ex_caisse_vente= $stmt->execute();
                    // recupère le ID du facture
                    $last_id_caisse_vente = $pdo->lastInsertId();
                                 
                } else {
                    exit('ex_gestion_caisse <> false');
                }

            if ($ex_caisse_vente <> false ) {

                // GESTION STOCK : MIS a JOUR

                $query_update="SELECT * FROM gestion_stock WHERE ID_GESTION='".$id_gestion_stock."'";

                $stmt_update = $pdo->prepare($query_update);
                $stmt_update->execute();
                $row_gestion = $stmt_update->fetch(PDO::FETCH_BOTH);

                $quantite_update =($row_gestion['QUANTITE']) - ($quantite);

                $sql = "UPDATE gestion_stock SET QUANTITE='$quantite_update' WHERE ID_GESTION='".$id_gestion_stock."'";

                // Prepare statement
                $update_gestion = $pdo->prepare($sql);

                // execute the query
                $ex_gestion_stock= $update_gestion->execute();
                
            } else {
                    exit('ex_caisse_vente <> false');
                }                                             

                // Attempt to execute the prepared statement
                if($ex_facture && $ex_vente && $ex_gestion_stock && $ex_gestion_caisse && $ex_caisse_vente){
                    // Records created successfully. Redirect to landing page

                    # ALERT SI AJOUT VENTE AVEC SUCCES 
                      $_SESSION['alert_icon'] = "success";
                      $_SESSION['alert_title'] = "Addition vente terminée";
                      $_SESSION['alert_text'] = "Le vente a été ajouté avec succès!"; 

                    # REDIRIGER VERS LE VENTE SI CREATION VENTE AVEC SUCCES

                    header("location: read_vente.php");
                    exit();
                } else{

                    // sql to delete a record
                    $sql_facture = "DELETE FROM facture WHERE ID_FACTURE=".$last_id_facture;
                    $sql_vente = "DELETE FROM vente WHERE ID_VENTE =".$last_id_vente;
                    $sql_gestion_caisse = "DELETE FROM gestion_caisse WHERE ID_GESTION_CAISSE=".$last_id_gestion_caisse;
                    $sql_caisse_vente = "DELETE FROM caisse_vente WHERE ID_CAISSE_VENTE =".$last_id_caisse_vente;
   

                    // use exec() because no results are returned
                    $pdo->exec($sql_caisse_vente);
                    $pdo->exec($sql_gestion_caisse);
                    $pdo->exec($sql_vente);  
                    $pdo->exec($sql_facture); 

                    # ALERT SI CREATION VENTE ECHOUE
                      $_SESSION['alert_icon'] = "warning";
                      $_SESSION['alert_title'] = "L'Addition a échoué";
                      $_SESSION['alert_text'] = "Une erreur s'est produite veuillez réessayer plus tard! Le vente n'a pas été ajouté.";  
                    # REDIRIGER VERS LE VENTE SI CREATION VENTE ECHOUE
                    header("location: read_vente.php");
                    exit();
                }
            }

            // Close statement
            $conn = null;
        }
    
    }
catch(PDOException $e)
    {
    echo $sql . "<br>" . $e->getMessage();
    }    

?>

<?=template_header('Création vente')?>

<?=template_content('vente')?>
        
        <!-- CONTENU -->
        <div id="container-page" class="container">
    <!-- FIN FONCTION -->  
            <!-- navigation --> 
            <div id="navigation" class="row">
                <h5><a href="index.php"><i class='fas fa-home'></i></a> / Vente / Création nouveau Vente</h5>
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
                    <h2><i class="fas fa-plus-circle"></i> AJOUTER UNE VENTE</h2>
                </div>
                <div class="container mt-4 mb-5">
                    <form name="myForm" id="form-add3" action="add_vente.php" method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                  <label for="nom_produit">NOM DE PRODUIT</label>                                
                                  <select class="custom-select mr-sm-2"  id="input_nom_produit" onchange="getSelectValue();" oninput="controlInputNOM_PRODUIT();" name="nom_produit">
                                    <option value="vide">---CHOISIR UN NOM DE PRODUIT--- *</option>
                                        <?php foreach ($gstocks as $gstock): ?>                 
                                            <option value="<?=$gstock['ID_GESTION']?>"><?=ucfirst($gstock['NOM_PRODUIT'])." (".ucfirst($gstock['REFERENCE'])." ".$gstock['QUANTITE_UNITE'].strtoupper($gstock['UNITE']).") | ".$gstock['PRIX_UNITAIRE']." Ar | Qté en stock : ".$gstock['QUANTITE']?></option>
                                        <?php endforeach; ?>
                                  </select>
                                    <?php if(!empty($nom_produit_err)) : ?>
                                    <p id="erreur-nom_produit" class="erreur-p"><?= $icon_type ." ". $nom_produit_err?></p>
                                    <?php endif; ?>                                  
                                </div>
                                <div class="form-group">
                                    <label for="prix_unitaire">PRIX UNITAIRE</label>
                                    <input class="form-control" type="text" name="prix_unitaire" placeholder="prix unitaire" value="0.0" oninput="controlInputPRIX_UNITAIRE();" id="prix_unitaire">
                                    <?php if(!empty($prix_unitaire_err)) : ?>
                                    <p id="erreur-prix_unitaire" class="erreur-p"><?= $icon_type ." ". $prix_unitaire_err?></p>
                                    <?php endif; ?>                                    
                                </div> 
                                <div class="form-group">
                                    <label for="quantite">QUANTITE</label>
                                    <input class="form-control" type="text" name="quantite" placeholder="quantite" value="0.0" oninput="controlInputQUANTITE();" id="quantite">
                                    <?php if(!empty($quantite_err)) : ?>
                                    <p id="erreur-quantite" class="erreur-p"><?= $icon_type ." ". $quantite_err?></p>
                                    <?php endif; ?>                                    
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="statut">STATUT</label>
                                    <select class="custom-select"  id="statut" onchange="getSelectValue();" oninput="controlInputSTATUT();" name="statut">
                                        <option value="vide">--STATUT--*</option>
                                        <option value="paye">PAYE</option>
                                        <option value="non-paye">NON-PAYE</option>
                                    </select>
                                    <?php if(!empty($statut_err)) : ?>
                                    <p id="erreur-statut" class="erreur-p"><?= $icon_type ." ". $statut_err?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                  <label for="client">CLIENT</label>                                
                                  <select class="custom-select mr-sm-2"  id="input_client" onchange="getSelectValue();" oninput="controlInputCLIENT();" name="client">
                                  <!-- Si la session faire une vente est définie -->  
                                    <?php if (isset($_SESSION ['client_faire_vente_nom']) && isset($_SESSION ['client_faire_vente_id'])  ) :?>
                                        <option class="pre_selection" value="<?=$_SESSION ['client_faire_vente_id']?>"><?=ucwords($_SESSION ['client_faire_vente_nom'])?></option>
                                        <?php foreach ($clients as $client): ?> 
                                            <option value="<?=$client['ID_CLIENT']?>"><?=$client['NOM']?></option>
                                        <?php endforeach; ?>   
                                    <?php else : ?>
                                        <option value="vide">---CHOISIR UN CLIENT--- *</option>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?=$client['ID_CLIENT']?>"><?=$client['NOM']?></option>
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

<!-- Deconnexion session -->
    <?php 
      unset($_SESSION ['client_faire_vente_nom']);
      unset($_SESSION ['client_faire_vente_id']);
     ?>

<!-- // SWEET ALERT NOTIFICATION // -->
<?=sweet_alert_notification()?>

<?=template_footer()?>
