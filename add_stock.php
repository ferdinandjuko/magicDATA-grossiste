<?php
 
include 'functions.php';
session_start();
$pdo = pdo_connect_mysql();
$msg = '';

// VERIFICATION SI L'UTILISATEUR EXISTE TOUJOURS DANS LA BASE DE DONNÉE
verif_session();
# Si la session n'est pas définie, rediriger vers la page de connexion, si l'url est prédéfinie avant la connexion rediriger vers l'url prédéfinie après la connexion
session_url();


// Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page
$stmt = $pdo->prepare("SELECT * FROM fournisseur WHERE ID=".$_SESSION['id']);
$stmt->execute();
// Fetch the records so we can display them in our template.
$fournisseurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
 

   // Define variables and initialize with empty values
    $nom_produit = $ref = $unite = $quantite_unite = $prix_unitaire = $prix_totale = $quantite = $date_achat = $fournisseur = $description = $photo = $etat= $nombre_caisse_solde = "";
    $nom_produit_err = $ref_err = $unite_err = $quantite_unite_err = $prix_unitaire_err = $prix_totale_err = $quantite_err = $date_achat_err = $fournisseur_err = $description_err = $photo_err = "";
    $icon_type= "<i class='fas fa-exclamation-triangle'></i>";
    $upload_dir = 'uploads/stock/';

    // DETECTER SI L'UTILISATEUR A DEJA UNE SOLDE DE DEPART
    $sql_solde = "SELECT * FROM gestion_caisse WHERE SESSION_ID=".$_SESSION['id']; 
    $query_solde = $pdo->prepare($sql_solde);
    $query_solde->execute();
    $nombre_caisse_solde = $query_solde->rowCount();

try {

    // Processing form data when form is submitted
    if(isset($_POST["submit"])) {

        function data_input($data) {
              $data = trim($data);
              $data = stripslashes($data);
              $data = htmlspecialchars($data);
              return $data;
            }
        
        // Validate Nom produit
        $input_nom_produit = data_input($_POST["nom_produit"]);

        if(empty($input_nom_produit)){
           $nom_produit_err = "Veuillez inserer le nom de votre produit";
        } elseif(!preg_match("/^[0-9-a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ' ]*$/",$input_nom_produit)){
           $nom_produit_err = "Seulement les lettres et les espaces sont acceptés";
        } else{
           $nom_produit = $input_nom_produit;
        }

        // Validate reference
        $input_ref = data_input($_POST["ref"]);

        if(empty($input_ref)){
            $ref_err = "Veuillez inserer votre reference";
        } elseif (!preg_match("/^[0-9-a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ' ]*$/",$input_ref)) {
            $ref_err = "Seulement les lettres et les espaces sont acceptés";
        } else{
            $ref = $input_ref;
        }

        // Validate unite
        $input_unite = data_input($_POST["unite"]);

        if(empty($input_unite) || $input_unite=="vide"){
            $unite_err = "Veuillez inserer votre unite de mesure";
        } else{
            $unite = $input_unite;
        }

        // Validate quantite unite
        $input_quantite_unite = data_input($_POST["quantite_unite"]);
        if(empty($input_quantite_unite) || $input_quantite_unite==0.0){
            $quantite_unite = 0;
            $quantite_unite_err="Quantité unité ou unité vide";
        } elseif (!preg_match("/^[0-9,. ]*$/",$input_quantite_unite)) {
           $quantite_unite_err = "Seulement les chiffres sont acceptés";
        } else{
            $quantite_unite = $input_quantite_unite;
            $quantite_unite = str_replace('.', ',', $quantite_unite);
        }

        // Validate PRIX UNITAIRE
        $input_prix_unitaire = data_input($_POST["prix_unitaire"]);

        if(empty($input_prix_unitaire) || $input_prix_unitaire==0.0) {
            $prix_unitaire= 0;
            $prix_unitaire_err = "Prix unitaire vide";
        }elseif (!preg_match("/^[0-9,. ]*$/",$input_prix_unitaire)) {
           $prix_unitaire_err = "Seulement les chiffres sont acceptés pu";
        } else{
            $prix_unitaire = $input_prix_unitaire;
            $prix_unitaire = str_replace("%,%", ".", $prix_unitaire);
            $prix_unitaire = floatval($prix_unitaire);
        }

        // Validate QUANTITE
        $input_quantite = data_input($_POST["quantite"]);
        if(empty($input_quantite) || $input_quantite==0.0){
            $input_quantite = 0;
            $quantite_err = "Quantité vide";
        } elseif (!filter_var($input_quantite, FILTER_VALIDATE_INT) === true) {
           $quantite_err = "Seulement les chiffres sont acceptés";
        } else{
            $quantite = $input_quantite;
        }

        // Validate PRIX TOTAL
        if (!empty($prix_unitaire) && !empty($quantite)) {

            $input_prix_total = floatval($prix_unitaire)*($quantite);
            if (!preg_match("/^[0-9,.' ]*$/",$input_prix_total)) {
           $prix_total_err = "Seulement les chiffres sont acceptés pt";
             } else{
            $prix_total = $input_prix_total;
            }  
        }

        // Validate Date d'achat
        $input_date_achat = data_input($_POST["date_achat"]);

        if(!empty($input_date_achat)){
            $date_achat = $input_date_achat;
        } elseif (empty($input_date_achat)) {
            $date_achat_err = "Date vide";
        }
        // Validate Date d'AJOUT
        $input_date_ajout = date("Y-m-d");

        if(!empty($input_date_ajout)){
            $date_ajout = $input_date_ajout;
        }        
        // Validate FOURNISSEUR

        $input_fournisseur = data_input($_POST["fournisseur"]);

        if ($input_fournisseur == "vide"){
            $fournisseur_err = "Veuillez selectionner un fournisseur";
        }else{
            $fournisseur = $input_fournisseur;
        }       
        // Validate description
        $input_description = data_input($_POST["description"]);

        if(empty($input_description)){
            $description_err = "Inserer votre votre description";
        } elseif (!preg_match("/^[0-9-a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ' ]*$/",$input_description)) {
           $description_err = "Seulement les lettres et les chiffres sont acceptés";  // 1= erreur

        }else {
            $description = $input_description;
        }       
//////////// PHOTO //////////////

        // Validate Photo 
        $designation = $nom_produit."_".$ref."_".$quantite_unite."_".$unite;

         if (empty($nom_produit_err) && empty($ref_err) && empty($quantite_unite_err) && empty($unite_err) ) {

                $imgName = $_FILES['image']['name'];
                $imgTmp = $_FILES['image']['tmp_name'];
                $imgSize = $_FILES['image']['size'];

                if($imgName) {

                  $imgExt = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));

                  $allowExt  = array('jpeg', 'jpg', 'png', 'gif');

                  $photo = time().'_'.rand(1000,9999).'_'.$designation.'.'.$imgExt;


                  if(in_array($imgExt, $allowExt)){

                    if($imgSize < 5000000){
                        move_uploaded_file($imgTmp ,$upload_dir.$photo); 
                    } else {
                     $photo_err = 'La taille de votre photo est trop grande. Taille maximum 5mo';
                    }
                  } else {
                    $photo_err = 'Insérer une photo valide !';    
                  }
                } else {
                    $photo = "default_stock.png";
            }

        } 

//////////////////////////////////// 


   # SELECTION POUR AVOIR LE NOM DU FOURNISSEUR
        
    $sql_nom="SELECT * FROM fournisseur WHERE ID_FOURNISSEUR='$fournisseur'";
    $query_nom = $pdo->prepare($sql_nom);
    $query_nom->execute();
    $row_fournisseur = $query_nom->fetch(PDO::FETCH_BOTH);

    $nom_fournisseur=$row_fournisseur['NOM'];
    $id_fournisseur = $fournisseur;

// 1° /////////////////////// INSERTION GESTION STOCK /////////////////////////////////

        if(empty($nom_produit_err) && empty($prix_unitaire_err) && empty($quantite_err) && empty($date_achat_err) && empty($fournisseur_err) && empty($description_err) && $nombre_caisse_solde<>0) {

                $sql_update_stock="SELECT * FROM gestion_stock WHERE (SESSION_ID=".$_SESSION['id'].") AND ID_FOURNISSEUR =".$id_fournisseur." AND gestion_stock.NOM_PRODUIT='$nom_produit' AND gestion_stock.REFERENCE='$ref' AND gestion_stock.UNITE='$unite' AND gestion_stock.QUANTITE_UNITE='$quantite_unite' AND gestion_stock.PRIX_UNITAIRE='$prix_unitaire'";

                $query_update_stock= $pdo->prepare($sql_update_stock);
                $query_update_stock->execute();
                $row_update_stock = $query_update_stock->fetch(PDO::FETCH_BOTH);
                $id_gestion = $row_update_stock['ID_GESTION'];

                if($query_update_stock->rowCount()==0) {

                    // INSERTION GESTION STOCK SI LE MEME STOCK N'EXISTE PAS ENCORE
                    $session_id = $_SESSION['id'];

                    $sql_gestion_stock="INSERT INTO gestion_stock (ID_FOURNISSEUR, SESSION_ID, NOM_PRODUIT, REFERENCE, UNITE, QUANTITE_UNITE, PRIX_UNITAIRE, QUANTITE, FOURNISSEUR)
                    VALUES (:ID_FOURNISSEUR, :SESSION_ID, :NOM_PRODUIT, :REFERENCE, :UNITE, :QUANTITE_UNITE, :PRIX_UNITAIRE,:QUANTITE, :FOURNISSEUR)";
                    
                    $stmt_gestion_stock = $pdo->prepare($sql_gestion_stock);

                    $stmt_gestion_stock->bindParam(':ID_FOURNISSEUR', $id_fournisseur);
                    $stmt_gestion_stock->bindParam(':SESSION_ID', $session_id);
                    $stmt_gestion_stock->bindParam(':NOM_PRODUIT', $nom_produit);
                    $stmt_gestion_stock->bindParam(':REFERENCE', $ref);
                    $stmt_gestion_stock->bindParam(':UNITE', $unite);
                    $stmt_gestion_stock->bindParam(':QUANTITE_UNITE', $quantite_unite);
                    $stmt_gestion_stock->bindParam(':PRIX_UNITAIRE', $prix_unitaire);
                    $stmt_gestion_stock->bindParam(':QUANTITE', $quantite);
                    $stmt_gestion_stock->bindParam(':FOURNISSEUR', $nom_fournisseur);

                    // insert a row gestion stock
                    $ex_gestion_stock= $stmt_gestion_stock->execute(); 
                    // recuperer l'id gestion stock
                    $last_id_gestion = $pdo->lastInsertId();
   

                } elseif($query_update_stock->rowCount()==1) {

                    // INSERTION GESTION STOCK SI LE MEME STOCK EXISTE DEJA

                    $quantite_total = $quantite + $row_update_stock['QUANTITE'];

                    $sql = "UPDATE gestion_stock SET QUANTITE='$quantite_total' WHERE ID_GESTION='".$row_update_stock['ID_GESTION']."'";

                    // Prepare statement
                    $update1 = $pdo->prepare($sql);

                    // execute the query
                    $ex_gestion_stock = $update1->execute();
                    $last_id_gestion = $id_gestion;

                    } else {
                        $msg="Something went wrong";
                        echo $msg;
                    }

                if ($ex_gestion_stock == false ) {
                    # code...
                    exit("GESTION STOCK");
                }
// 2° ////////////////////////// INSERTION STOCK //////////////////////////////////

            if ($ex_gestion_stock <> false) {

                $ajoute_par = $_SESSION['name'];

                // prepare sql and bind parameters
                $sql_stock="INSERT INTO stock (ID_GESTION, ID_FOURNISSEUR, DATE_AJOUT, NOM_PRODUIT, REFERENCE, UNITE, QUANTITE_UNITE, PRIX_UNITAIRE, QUANTITE, DATE_ACHAT, FOURNISSEUR, DESCRIPTION, AJOUTE_PAR, IMAGE)
                VALUES (:ID_GESTION, :ID_FOURNISSEUR, :DATE_AJOUT, :NOM_PRODUIT, :REFERENCE, :UNITE, :QUANTITE_UNITE, :PRIX_UNITAIRE, :QUANTITE, :DATE_ACHAT, :FOURNISSEUR, :DESCRIPTION, :AJOUTE_PAR, :IMAGE)";
                
                $stmt_stock = $pdo->prepare($sql_stock);

                $stmt_stock->bindParam(':ID_GESTION', $last_id_gestion);                
                $stmt_stock->bindParam(':ID_FOURNISSEUR', $id_fournisseur);
                $stmt_stock->bindParam(':DATE_AJOUT', $date_ajout);
                $stmt_stock->bindParam(':NOM_PRODUIT', $nom_produit);
                $stmt_stock->bindParam(':REFERENCE', $ref);
                $stmt_stock->bindParam(':UNITE', $unite);
                $stmt_stock->bindParam(':QUANTITE_UNITE', $quantite_unite);
                $stmt_stock->bindParam(':PRIX_UNITAIRE', $prix_unitaire);
                $stmt_stock->bindParam(':QUANTITE', $quantite);
                $stmt_stock->bindParam(':DATE_ACHAT', $date_achat);
                $stmt_stock->bindParam(':FOURNISSEUR', $nom_fournisseur);
                $stmt_stock->bindParam(':DESCRIPTION', $description);
                $stmt_stock->bindParam(':AJOUTE_PAR', $ajoute_par);
                $stmt_stock->bindParam(':IMAGE', $photo);

                // insert a row
                $ex_stock= $stmt_stock->execute();
                //recuperer l'id
                $last_id_stock = $pdo->lastInsertId();


                var_dump($sql_stock);
                if ($ex_stock == false ) {
                    # code...
                    exit("STOCK");
                }                
        }


// 3° /////////////////////// INSERTION GESTION CAISSE /////////////////////////////////


                if ( ($ex_stock <> false) && ($ex_gestion_stock <> false) ) {


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
                    
                    $solde_actuel_total =  doubleval($solde_actuel_base) -  doubleval($prix_total);
                    $etat="STOCK (- Débit)";
                    $ajoute_par= $_SESSION['name'];
                    $id_resp= $_SESSION['id'];
                    $date_ajout = date("Y-m-d"); 
                    $responsable = $_SESSION['name']; 
                    // MOTIF
                    $motif= $nom_produit." ".$ref." ".$quantite_unite." ".$unite." (".$quantite." x ".$prix_unitaire." Ar)";

                    $sql="INSERT INTO gestion_caisse (SESSION_ID, DATE_AJOUT, SOLDE_ACTUEL, SOLDE, ETAT, MOTIF, RESPONSABLE, ID_FOURNISSEUR_RESP, FOURNISSEUR_RESP)
                    VALUES (:SESSION_ID, :DATE_AJOUT, :SOLDE_ACTUEL, :SOLDE, :ETAT, :MOTIF, :RESPONSABLE, :ID_FOURNISSEUR_RESP, :FOURNISSEUR_RESP)";
                    $stmt = $pdo->prepare($sql);

                    $stmt->bindParam(':SESSION_ID', $id_resp);
                    $stmt->bindParam(':DATE_AJOUT', $date_ajout);
                    $stmt->bindParam(':SOLDE_ACTUEL', $solde_actuel_total);
                    $stmt->bindParam(':SOLDE', $prix_total);
                    $stmt->bindParam(':ETAT', $etat);
                    $stmt->bindParam(':MOTIF', $motif);
                    $stmt->bindParam(':RESPONSABLE', $responsable);
                    $stmt->bindParam(':ID_FOURNISSEUR_RESP', $id_fournisseur);
                    $stmt->bindParam(':FOURNISSEUR_RESP', $nom_fournisseur);

                    // insert a row gestion stock
                    $ex_gestion_caisse= $stmt->execute();
                    $last_id_caisse = $pdo->lastInsertId(); 


                    if ($ex_gestion_caisse == false ) {
                        # code...
                        exit("GESTION CAISSE");
                    }

                }


// 4° /////////////////////// INSERTION CAISSE STOCK /////////////////////////////////                

                if ( ($ex_stock  <> false) && ($ex_gestion_stock <> false) && ($ex_gestion_caisse <> false)) {
                    
                    // definition des variables
                     $heure = date("H:i:s");
                     $date = date("Y-m-d");
                     $designation =$nom_produit." ".$ref." ".$quantite_unite." ".$unite;
                     $session_id = $_SESSION['id'];
                
                    $sql="INSERT INTO caisse_stock (ID_GESTION, ID_GESTION_CAISSE, ID_FOURNISSEUR, ID_STOCK, SESSION_ID, DATE_AJOUT, HEURE, DESIGNATION, ENTRER, AJOUTER_PAR, FOURNISSEUR)
                    VALUES (:ID_GESTION, :ID_GESTION_CAISSE,:ID_FOURNISSEUR, :ID_STOCK , :SESSION_ID, :DATE_AJOUT, :HEURE, :DESIGNATION, :ENTRER, :AJOUTER_PAR, :FOURNISSEUR)";

                    var_dump($sql);
                    
                    $stmt = $pdo->prepare($sql);

                    $stmt->bindParam(':ID_GESTION', $last_id_gestion);
                    $stmt->bindParam(':ID_GESTION_CAISSE', $last_id_caisse);
                    $stmt->bindParam(':ID_FOURNISSEUR', $id_fournisseur);                    
                    $stmt->bindParam(':ID_STOCK', $last_id_stock);  
                    $stmt->bindParam(':SESSION_ID', $session_id);                        
                    $stmt->bindParam(':DATE_AJOUT', $date);
                    $stmt->bindParam(':HEURE', $heure);
                    $stmt->bindParam(':DESIGNATION', $designation);
                    $stmt->bindParam(':ENTRER', $prix_total);  
                    $stmt->bindParam(':AJOUTER_PAR', $ajoute_par);
                    $stmt->bindParam(':FOURNISSEUR', $nom_fournisseur);
                

                    // insert a row gestion stock
                    $ex_caisse_stock= $stmt->execute();
                    $last_id_caisse_stock = $pdo->lastInsertId(); 


                    if ($ex_caisse_stock == false ) {
                        # code...

                        $sql_stock = "DELETE FROM stock WHERE ID_STOCK=".$last_id_stock;
                        $sql_gestion_stock = "DELETE FROM gestion_stock WHERE ID_GESTION=".$last_id_gestion;
                        $sql_caisse_stock = "DELETE FROM caisse_stock WHERE ID_CAISSE_STOCK=".$last_id_caisse_stock;
                        $sql_gestion_caisse = "DELETE FROM gestion_caisse WHERE ID_GESTION_CAISSE=".$last_id_caisse;
       

                        // use exec() because no results are returned
                        $pdo->exec($sql_caisse_stock);
                        $pdo->exec($sql_gestion_caisse);   
                        $pdo->exec($sql_gestion_stock);
                        $pdo->exec($sql_stock);   

                        exit("CAISSE STOCK");
                    }                    

                }

                // Attempt to execute the prepared statement
                if( ($ex_stock <> false) && ($ex_gestion_stock <> false) && ($ex_gestion_caisse <> false) && ($ex_caisse_stock <> false) ) {


                    # ALERT SI CREATION STOCK AVEC SUCCES 
                      $_SESSION['alert_icon'] = "success";
                      $_SESSION['alert_title'] = "Addition stock terminée";
                      $_SESSION['alert_text'] = "Le stock a été ajouté avec succès!";  
                    # REDIRIGER VERS LE STOCK SI CREATION STOCK AVEC SUCCES

                    header("location: read_stock.php");
                    exit();

             
                } else{

        // ANNULATION DE TOUTES LES INSERTIONS SI un probleme est survenu LORS DE L'insertion //       

                    $sql_stock = "DELETE FROM stock WHERE ID_STOCK=".$last_id_stock;
                    $sql_gestion_stock = "DELETE FROM gestion_stock WHERE ID_GESTION=".$last_id_gestion;
                    $sql_caisse_stock = "DELETE FROM caisse_stock WHERE ID_CAISSE_STOCK=".$last_id_caisse_stock;
                    $sql_gestion_caisse = "DELETE FROM gestion_caisse WHERE ID_GESTION_CAISSE=".$last_id_caisse;
   

                    // use exec() because no results are returned
                    $pdo->exec($sql_caisse_stock);
                    $pdo->exec($sql_gestion_caisse);   
                    $pdo->exec($sql_gestion_stock);
                    $pdo->exec($sql_stock);   


                    # ALERT SI CREATION STOCK ECHOUE
                      $_SESSION['alert_icon'] = "warning";
                      $_SESSION['alert_title'] = "L'Addition a échoué";
                      $_SESSION['alert_text'] = "Une erreur s'est produite veuillez réessayer plus tard! Le stock n'a pas été ajouté.";  
                    # REDIRIGER VERS LE STOCK SI CREATION STOCK ECHOUE
                    header("location: read_stock.php");
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

<?=template_header('Creation stock')?>

<?=template_content('stocks')?>
        
        <!-- CONTENU -->
        <div id="container-page" class="container">
    <!-- FIN FONCTION -->  
            <!-- navigation --> 
            <div id="navigation" class="row">
                <h5><a href="index.php"><i class='fas fa-home'></i></a> / Inventaire / Historique Stocks / Création nouveau stock</h5>
                <div class="navigation-retour-active">
                  <a href="read_stock.php"><i class="fas fa-chevron-circle-left"></i></a>
                </div>
                <div class="navigation-retour-non-active">
                  <a href=""><i class="fas fa-chevron-circle-right"></i></a>
                </div>                 
            </div>
                <!-- Fin navigation --> 
            <div id="cadre-contenu">
                <div class="cadre-title">
                    <h2><i class="fas fa-plus-circle"></i> AJOUTER UN STOCK</h2>
                </div>
                
                <?php if($nombre_caisse_solde == 0) : ?>
                <div>
                    <p class="error"><i class="fas fa-info-circle"> </i> AJOUTER D'ABORD UNE SOLDE DE DÉPART DANS VOTRE CAISSE</p>
                    <a class="btn btn-outline-secondary ml-2 mr-2 font-weight-bold" href="add_solde.php"><i class="fas fa-plus"> </i> AJOUTER UNE SOLDE</a>
                </div>
                <?php endif; ?>                    
                <div class="container">
                    <form name="myForm" id="form-add2" enctype="multipart/form-data" action="add_stock.php" method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group"> 
                                    <label for="nom_produit">NOM DE PRODUIT</label>
                                    <input class="form-control" type="text" name="nom_produit" placeholder="NOM DE PRODUIT" value="" oninput="controlInputNOM_PRODUIT();" id="nom_produit">
                                    <?php if(!empty($nom_produit_err)) : ?>
                                    <p id="erreur-nom_produit" class="erreur-p"><?= $icon_type ." ". $nom_produit_err?></p>
                                    <?php endif; ?>                                       
                                </div>
                                <div class="form-group">
                                    <label for="reference">REFERENCE</label>
                                    <input class="form-control" type="text" name="ref" placeholder="REFERENCE" value="" oninput="controlInputREFERENCE();" id="ref">
                                    <?php if(!empty($ref_err)) : ?>
                                    <p id="erreur-reference" class="erreur-p"><?= $icon_type ." ". $ref_err?></p>
                                    <?php endif; ?>                                       
                                </div>
                                <div class="form-group" id="uniteTest">
                                    <label for="quantite_unite">QUANTITE UNITE</label>
                                    <div class="d-flex">
                                        <input class="form-control col-md-7" type="text" name="quantite_unite" placeholder="quantite" value="0.0" oninput="controlInputQUANTITE_UNITE();" id="quantite_unite">     
                                        <!-- <label for="unite">UNITE</label> -->                           
                                        <select class="custom-select col-md-3"  id="unite" onchange="getSelectValue();" oninput="controlInputUNITE();" name="unite">
                                            <option value="vide">--UNITE--*</option>
                                            <option value="kg">kg (kilogramme)</option>
                                            <option value="g">g (gramme)</option>
                                            <option value="t">t (tonne)</option>
                                            <option value="l">l (litre)</option>
                                            <option value="cl">cl (centilitre)</option>
                                            <option value="ml">ml (mililitre)</option>
                                            <option value="pcs">pcs (pièces)</option>
                                            <option value="crt">carton</option>
                                            <option value="pm">PM</option>
                                            <option value="gm">GM</option>
                                        </select>
                                    </div>
                                    <?php if(!empty($quantite_unite_err)) : ?>
                                    <p id="erreur-quantite_unite" class="erreur-p"><?= $icon_type ." ". $quantite_unite_err?></p>
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
                                    <input class="form-control" type="number" name="quantite" placeholder="quantite" value="0" oninput="controlInputQUANTITE();" id="quantite">
                                    <?php if(!empty($quantite_err)) : ?>
                                    <p id="erreur-quantite" class="erreur-p"><?= $icon_type ." ". $quantite_err?></p>
                                    <?php endif; ?>                                    
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_achat">DATE D'ACHAT</label>
                                    <input class="form-control" type="date" name="date_achat" oninput="controlInputDATE_ACHAT();" id="date_achat">
                                    <?php if(!empty($date_achat_err)) : ?>
                                    <p id="erreur-date_achat" class="erreur-p"><?= $icon_type ." ". $date_achat_err?></p>
                                    <?php endif; ?>                                    
                                </div>

                                <div class="form-group">
                                  <label for="fournisseur">FOURNISSEUR TEST</label>                                
                                  <select class="custom-select mr-sm-2"  id="input_fournisseur" onchange="getSelectValue();" oninput="controlInputFOURNISSEUR();" name="fournisseur">
                                  <!-- Si la session ajouter stock est définie -->  
                                    <?php if (isset($_SESSION ['fournisseur_faire_stock_nom']) && isset($_SESSION ['fournisseur_faire_stock_id'])  ) :?>
                                        <option class="pre_selection" value="<?=$_SESSION ['fournisseur_faire_stock_id']?>"><?=ucwords($_SESSION ['fournisseur_faire_stock_nom'])?></option>
                                        <?php foreach ($fournisseurs as $fournisseur): ?> 
                                            <option value="<?=$fournisseur['ID_FOURNISSEUR']?>"><?=$fournisseur['NOM']?></option>
                                        <?php endforeach; ?>   
                                    <?php else : ?>
                                        <option value="vide">---CHOISIR UN FOURNISSEUR--- *</option>
                                        <?php foreach ($fournisseurs as $fournisseur): ?>
                                            <option value="<?=$fournisseur['ID_FOURNISSEUR']?>"><?=$fournisseur['NOM']?></option>
                                        <?php endforeach; ?>   
                                    <?php endif; ?>                                   
                                  </select>
                                    <?php if(!empty($fournisseur_err)) : ?>
                                    <p id="erreur-fournisseur" class="erreur-p"><?= $icon_type ." ". $fournisseur_err?></p>
                                    <?php endif; ?>                                  
                                </div>  

                                <div class="form-group">
                                    <label for="description">DESCRIPTION</label>
                                    <textarea name="description" id="description" oninput="controlInputDESCRIPTION();"></textarea>
                                    <?php if(!empty($description_err)) : ?>
                                    <p id="erreur-description" class="erreur-p"><?= $icon_type ." ". $description_err?></p>
                                    <?php endif; ?>                                    
                                </div>
                                <div class="form-group">
                                    <label for="photo">PHOTO</label>
                                    <input class="form-control" type="file" name="image" placeholder="photo" id="photo">                    
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
  unset($_SESSION ['fournisseur_faire_stock_nom']);
  unset($_SESSION ['fournisseur_faire_stock_id']);
 ?>

<!-- // SWEET ALERT NOTIFICATION // -->
<?=sweet_alert_notification()?>

<?=template_footer()?>
