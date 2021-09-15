<?php

include 'functions.php';
session_start();
$pdo = pdo_connect_mysql();
$msg = '';

if(!isset($_SESSION['username'])) {
header('location: login.php'); 
}

// Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page
$stmt = $pdo->prepare("SELECT * FROM fournisseur WHERE ID=".$_SESSION['id']);
$stmt->execute();
// Fetch the records so we can display them in our template.
$fournisseurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
 

   // Define variables and initialize with empty values
    $nom_produit = $ref = $unite = $quantite_unite = $prix_unitaire = $prix_totale = $quantite = $date_achat = $fournisseur = $description = $photo = $etat= "";
    $nom_produit_err = $ref_err = $unite_err = $quantite_unite_err = $prix_unitaire_err = $prix_totale_err = $quantite_err = $date_achat_err = $fournisseur_err = $description_err = $photo_err = "";
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

        if(empty($input_unite)){
            $unite_err = "Veuillez inserer votre unite de mesure";
        } elseif (!preg_match("/^[0-9-a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ' ]*$/",$input_unite)) {
            $unite_err = "Seulement les lettres et les espaces sont acceptés";
        } else{
            $unite = $input_unite;
        }

        // Validate quantite unite
        $input_quantite_unite = data_input($_POST["quantite_unite"]);
        if(empty($input_quantite_unite)){
            $quantite_unite = 0;
            $quantite_unite_err="Quantité vide";
        } elseif (!filter_var($input_quantite_unite, FILTER_VALIDATE_INT) === true) {
           $quantite_unite = "Seulement les chiffres sont acceptés";
        } else{
            $quantite_unite = $input_quantite_unite;
        }

        // Validate PRIX UNITAIRE
        $input_prix_unitaire = data_input($_POST["prix_unitaire"]);

        if(empty($input_prix_unitaire)) {
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
        if(empty($input_quantite)){
            $input_quantite = 0;
            $quantite_err="Quantité vide";
        } elseif (!filter_var($input_quantite, FILTER_VALIDATE_INT) === true) {
           $quantite = "Seulement les chiffres sont acceptés";
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
        $input_date_achat = date("d-m-Y");

        if(!empty($input_date_achat)){
            $date_achat = $input_date_achat;
        }
        // Validate Date d'AJOUT
        $input_date_ajout = date("d-m-Y");

        if(!empty($input_date_ajout)){
            $date_ajout = $input_date_ajout;
        }        
        // Validate FOURNISSEUR
        $input_fournisseur = data_input($_POST["fournisseur"]);

        if(empty($input_fournisseur)){
            $fournisseur_err = "Entrez votre fournisseur";
        } elseif (!preg_match("/^[0-9-a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ' ]*$/",$input_fournisseur)) {
           $fournisseur_err = "Seulement les lettres et les chiffres sont acceptés";  // 1= erreur

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
        // Validate photo
        $input_photo = data_input($_POST["photo"]);
        $photo = $input_photo;
        


// Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page
        
    $sql_nom="SELECT * FROM fournisseur WHERE ID_FOURNISSEUR='$fournisseur'";
    $query_nom = $pdo->prepare($sql_nom);
    $query_nom->execute();
    $row_nom = $query_nom->fetch(PDO::FETCH_BOTH);

        // Check input errors before inserting in database
        if(empty($nom_produit_err) && empty($prix_unitaire_err) && empty($quantite_err) && empty($date_achat_err) && empty($fournisseur_err) && empty($description_err)) {

                $nom_fournisseur=$row_nom['NOM'];
                $ajoute_par = $_SESSION['name'];
                $nom_produit=ucfirst($nom_produit);
                $ref=strtoupper($ref);
                $unite=strtoupper($unite);
                $nom_fournisseur=ucfirst($nom_fournisseur);
                $description=ucfirst($description);

                // prepare sql and bind parameters
                $sql_stock="INSERT INTO stock (ID_FOURNISSEUR, DATE_AJOUT, NOM_PRODUIT, REFERENCE, UNITE, QUANTITE_UNITE, PRIX_UNITAIRE, PRIX_TOTALE, QUANTITE, DATE_ACHAT, FOURNISSEUR, DESCRIPTION, AJOUTE_PAR, PHOTO)
                VALUES (:ID_FOURNISSEUR, :DATE_AJOUT, :NOM_PRODUIT, :REFERENCE, :UNITE, :QUANTITE_UNITE, :PRIX_UNITAIRE, :PRIX_TOTALE, :QUANTITE, :DATE_ACHAT, :FOURNISSEUR, :DESCRIPTION, :AJOUTE_PAR, :PHOTO)";
                
                $stmt_stock = $pdo->prepare($sql_stock);
                
                $stmt_stock->bindParam(':ID_FOURNISSEUR', $fournisseur);
                $stmt_stock->bindParam(':DATE_AJOUT', $date_ajout);
                $stmt_stock->bindParam(':NOM_PRODUIT', $nom_produit);
                $stmt_stock->bindParam(':REFERENCE', $ref);
                $stmt_stock->bindParam(':UNITE', $unite);
                $stmt_stock->bindParam(':QUANTITE_UNITE', $quantite_unite);
                $stmt_stock->bindParam(':PRIX_UNITAIRE', $prix_unitaire);
                $stmt_stock->bindParam(':PRIX_TOTALE', $prix_total);
                $stmt_stock->bindParam(':QUANTITE', $quantite);
                $stmt_stock->bindParam(':DATE_ACHAT', $date_achat);
                $stmt_stock->bindParam(':FOURNISSEUR', $nom_fournisseur);
                $stmt_stock->bindParam(':DESCRIPTION', $description);
                $stmt_stock->bindParam(':AJOUTE_PAR', $ajoute_par);
                $stmt_stock->bindParam(':PHOTO', $photo);

                // insert a row
                $ex_stock= $stmt_stock->execute();
                $last_id_stock = $pdo->lastInsertId();


                // prepare sql and bind parameters GESTION STOCK 

                // GESTION ID
            if($ex_stock) {
                $sql_update_stock="SELECT * FROM gestion_stock WHERE NOM_PRODUIT='$nom_produit' AND REFERENCE='$ref' AND UNITE='$unite' AND QUANTITE_UNITE='$quantite_unite' AND PRIX_UNITAIRE='$prix_unitaire'";

                $query_update_stock= $pdo->prepare($sql_update_stock);
                $query_update_stock->execute();
                $row_update_stock = $query_update_stock->fetch(PDO::FETCH_BOTH);
                $id_gestion = $row_update_stock['ID_GESTION'];

                if($query_update_stock->rowCount()==0) {

                    $sql_gestion_stock="INSERT INTO gestion_stock (ID_STOCK, NOM_PRODUIT, REFERENCE, UNITE, QUANTITE_UNITE, PRIX_UNITAIRE, QUANTITE, PRIX_TOTALE)
                    VALUES (:ID_STOCK, :NOM_PRODUIT, :REFERENCE, :UNITE, :QUANTITE_UNITE, :PRIX_UNITAIRE,:QUANTITE, :PRIX_TOTALE)";
                    
                    $stmt_gestion_stock = $pdo->prepare($sql_gestion_stock);

                    $stmt_gestion_stock->bindParam(':ID_STOCK', $last_id_stock);
                    $stmt_gestion_stock->bindParam(':NOM_PRODUIT', $nom_produit);
                    $stmt_gestion_stock->bindParam(':REFERENCE', $ref);
                    $stmt_gestion_stock->bindParam(':UNITE', $unite);
                    $stmt_gestion_stock->bindParam(':QUANTITE_UNITE', $quantite_unite);
                    $stmt_gestion_stock->bindParam(':PRIX_UNITAIRE', $prix_unitaire);
                    $stmt_gestion_stock->bindParam(':PRIX_TOTALE', $prix_total);
                    $stmt_gestion_stock->bindParam(':QUANTITE', $quantite);

                    // insert a row gestion stock
                    $ex_gestion_stock= $stmt_gestion_stock->execute();
                    $last_id_gestion = $pdo->lastInsertId(); 

                } elseif($query_update_stock->rowCount()==1) {

                    $prix_total_g = ($quantite)*($prix_unitaire);
                    $prix_total_g = $prix_total_g + $row_update_stock['PRIX_TOTALE'];
                    $quantite = $quantite + $row_update_stock['QUANTITE'];


                    $sql = "UPDATE gestion_stock SET QUANTITE='$quantite', PRIX_TOTALE='$prix_total_g' WHERE ID_GESTION='".$row_update_stock['ID_GESTION']."'";

                    // Prepare statement
                    $update1 = $pdo->prepare($sql);

                    // execute the query
                    $ex_gestion_stock= $update1->execute();
                    $last_id_gestion = $pdo->lastInsertId();


                    } else {
                        $msg="Something went wrong";
                        echo $msg;
                    }
                } else {
                    $msg="Something went wrong! TSY METY NY ex stock";
                    echo $msg;
                }                

                // GET GESTION STOCK ID
                $query_update_stock= $pdo->prepare($sql_update_stock);
                $query_update_stock->execute();
                $row_update_stock = $query_update_stock->fetch(PDO::FETCH_BOTH);
                $id_gestion = $row_update_stock['ID_GESTION'];

                // INSERTION GESTION CAISSE      
                if ($ex_stock && $ex_gestion_stock) {

                    $sql_id="SELECT MAX(ID_GESTION_CAISSE) AS max_id FROM gestion_caisse WHERE ID=".$_SESSION['id'];
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
                    $etat="STOCK";
                    $ajoute_par= $_SESSION['name'];
                    $id_resp= $_SESSION['id'];
                    // Validate ETAT
                    $etat ="STOCK";
                    // Validate DATE
                    $date_ajout = date("d-m-Y");                    

                    $sql="INSERT INTO gestion_caisse (ID, DATE_AJOUT, SOLDE_ACTUEL, SOLDE, ETAT, RESPONSABLE)
                    VALUES (:ID, :DATE_AJOUT, :SOLDE_ACTUEL, :SOLDE, :ETAT, :RESPONSABLE)";
                    
                    $stmt = $pdo->prepare($sql);

                    $stmt->bindParam(':ID', $id_resp);
                    $stmt->bindParam(':DATE_AJOUT', $date_ajout);
                    $stmt->bindParam(':SOLDE_ACTUEL', $solde_actuel_total);
                    $stmt->bindParam(':SOLDE', $prix_total);
                    $stmt->bindParam(':ETAT', $etat);
                    $stmt->bindParam(':RESPONSABLE', $responsable);
                    // insert a row gestion stock
                    $ex_gestion_caisse= $stmt->execute();
                    $last_id_caisse = $pdo->lastInsertId(); 

                } else {

                    $msg="Something went wrong! ts mety ny ex gestion stock";
                    echo $msg;
                }

                // INSERTION CAISSE STOCK
                if ($ex_stock && $ex_gestion_stock && $ex_gestion_caisse) {

                     $heure = date("H:i:s");
                     $date = date("d-m-Y");
                     $designation =$nom_produit." ".$ref." ".$quantite_unite." ".$unite;
                     $ajoute_par= $_SESSION['name'];
                    
                    $sql="INSERT INTO caisse_stock (ID_GESTION, ID_GESTION_CAISSE, DATE_AJOUT, HEURE, DESIGNATION, ENTRER, TOTAL_SOMME, AJOUTER_PAR)
                    VALUES ( :ID_GESTION, :ID_GESTION_CAISSE, :DATE_AJOUT, :HEURE, :DESIGNATION, :ENTRER, :TOTAL_SOMME, :AJOUTER_PAR)";
                    
                    $stmt = $pdo->prepare($sql);

                    $stmt->bindParam(':ID_GESTION', $id_gestion);
                    $stmt->bindParam(':ID_GESTION_CAISSE', $last_id_caisse);
                    $stmt->bindParam(':DATE_AJOUT', $date);
                    $stmt->bindParam(':HEURE', $heure);
                    $stmt->bindParam(':DESIGNATION', $designation);
                    $stmt->bindParam(':ENTRER', $prix_total);  
                    $stmt->bindParam(':TOTAL_SOMME', $prix_total); 
                    $stmt->bindParam(':AJOUTER_PAR', $ajoute_par); 

                    // insert a row gestion stock
                    $ex_caisse_stock= $stmt->execute();
                    $last_id_caisse_stock = $pdo->lastInsertId();

                }


                // Attempt to execute the prepared statement
                if($ex_stock && $ex_gestion_stock && $ex_gestion_caisse && $ex_caisse_stock) {
                    // Records created successfully. Redirect to landing page
                    header("location: read_stock.php");
                    exit();
                } else{

                    // sql to delete a record
                    $sql_stock = "DELETE FROM stock WHERE ID_STOCK=".$last_id_stock;
                    $sql_gestion_stock = "DELETE FROM gestion_stock WHERE ID_GESTION=".$last_id_gestion;

                    // use exec() because no results are returned
                    $pdo->exec($sql_gestion_stock);
                    $pdo->exec($sql_stock);

                    $msg="Something went wrong. Please try again later. MISY TSY METY IZY EFATRA";
                    echo $msg;                 

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

<?=template_header('Create')?>

<div class="content update">
	<h2>ADD STOCK</h2>
    <form action="add_stock.php" method="post">
        <label for="nom_produit">NOM DE PRODUIT</label>
        <label for="ref">REFERENCE</label>
        <input type="text" name="nom_produit" placeholder="nom du produit" value="" id="nom_produit">
        <input type="text" name="ref" placeholder="reference" value="" id="ref">
        <label for="quantite_unite">quantite_unite</label>
        <label for="unite">unite</label>
        <input type="text" name="quantite_unite" placeholder="quantite" value="" id="quantite_unite">
        <select class="custom-select mr-sm-2" name="unite" style="width: 400px" id="inlineFormCustomSelect">
            <option selected>Choose...</option>
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
        
        <label for="prix_unitaire">PRIX UNITAIRE</label>
        <label for=""></label>
        <input type="number" name="prix_unitaire" step="0.01" value="0.0" placeholder="Prix unitaire" id="prix_unitaire">
        <label for="quantite">QUANTITE</label>
        <label for="date_achat">DATE D'ACHAT</label>
        <input type="number" name="quantite" placeholder="quantite" id="quantite">
        <input type="date" name="date_achat" placeholder="date d'achat" id="date_achat">
        <label for="fournisseur">Fournisseur</label>
        <label for="description">Description</label>
        <select class="custom-select mr-sm-2" name="fournisseur" style="width: 400px" id="inlineFormCustomSelect">
            <option selected>Choose...</option>
        <?php foreach ($fournisseurs as $fournisseur): ?>                 
            <option value="<?=$fournisseur['ID_FOURNISSEUR']?>"><?=$fournisseur['NOM']?></option>
        <?php endforeach; ?>
        </select>
        <input type="text" name="description" placeholder="description" id="description">
        <input type="file" name="photo" placeholder="photo" id="photo">


        <p><?=$nom_produit_err?></p><br>
        <p><?=$prix_unitaire_err?></p><br>
        <p><?=$ref_err?></p><br>
        <p><?=$quantite_unite_err?></p><br>
        <p><?=$unite_err?></p><br>
        <p><?=$quantite_err?></p><br>
        <p><?=$prix_totale_err?></p><br>
        <p><?=$date_achat_err?></p><br>
        <p><?=$fournisseur_err?></p><br>
        <p><?=$description_err?></p>
        <p><?=$photo_err?></p>
        <input type="submit" name="submit" value="Create">
    </form>
    <?php if ($msg): ?>
    <p><?=$msg?></p>
    <?php endif; ?>
</div>

<?=template_footer()?>
