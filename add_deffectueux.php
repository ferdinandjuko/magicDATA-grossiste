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
$stmt = $pdo->prepare("SELECT * FROM gestion_stock WHERE QUANTITE<>0 AND SESSION_ID=".$_SESSION['id']);

$stmt->execute();
// Fetch the records so we can display them in our template.
$gstocks = $stmt->fetchAll(PDO::FETCH_ASSOC);


   // Define variables and initialize with empty values
    $nom_produit = $quantite = $date_ajout = $description = "";
    $nom_produit_err = $quantite_err = $date_ajout_err = $description_err = "";
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

        if ($input_nom_produit == "vide"){
            $nom_produit_err = "Veuillez selectionner un nom de produit";
        }else{
            $nom_produit = $input_nom_produit;
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
        // Validate Date d'AJOUT
        $input_date_ajout = data_input($_POST["date_ajout"]);

        if(!empty($input_date_ajout)){
            $date_ajout = $input_date_ajout;
        } elseif (empty($input_date_ajout)) {
            $date_ajout_err = "Date vide";
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

// Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page

        
    $sql_nom="SELECT * FROM gestion_stock WHERE ID_GESTION='$nom_produit'";
    $query_nom = $pdo->prepare($sql_nom);
    $query_nom->execute();
    $row_gestion = $query_nom->fetch(PDO::FETCH_BOTH);

    /*$sql_nom="SELECT * FROM fournisseur WHERE ID_FOURNISSEUR='$nom_produit'";
    $query_nom = $pdo->prepare($sql_nom);
    $query_nom->execute();
    $row_gestion = $query_nom->fetch(PDO::FETCH_BOTH);*/

        // Check input errors before inserting in database
        if(empty($nom_produit_err) && empty($quantite_err) && empty($date_ajout_err) && empty ($description_err)){

                $nom_stock=$row_gestion['NOM_PRODUIT'];
                $unite= $row_gestion['UNITE'];
                $quantite_unite= $row_gestion['QUANTITE_UNITE'];
                $prix_unitaire= $row_gestion['PRIX_UNITAIRE'];
                $ref= $row_gestion['REFERENCE'];
                $designation = $row_gestion['NOM_PRODUIT']." (".$quantite_unite."".$unite.") ".$prix_unitaire." Ar";
                $ajouter_par = $_SESSION['name'];
                $fournisseur = $row_gestion['FOURNISSEUR'];
                $id_fournisseur = $row_gestion['ID_FOURNISSEUR'];

               // prepare sql and bind parameters GESTION STOCK 

                $sql_update_stock="SELECT * FROM gestion_stock WHERE NOM_PRODUIT='$nom_stock' AND REFERENCE='$ref' AND UNITE='$unite' AND QUANTITE_UNITE='$quantite_unite' AND PRIX_UNITAIRE='$prix_unitaire'";

                $query_update_stock= $pdo->prepare($sql_update_stock);
                $query_update_stock->execute();
                $row_update_stock = $query_update_stock->fetch(PDO::FETCH_BOTH);

                if($query_update_stock->rowCount()==1) {


                $sql="INSERT INTO deffectueux (ID_GESTION, ID_FOURNISSEUR, DESIGNATION, QUANTITE, DATE_AJOUT, DESCRIPTION, PRIX_UNITAIRE, FOURNISSEUR, AJOUTER_PAR)
                VALUES (:ID_GESTION, :ID_FOURNISSEUR, :DESIGNATION, :QUANTITE, :DATE_AJOUT, :DESCRIPTION, :PRIX_UNITAIRE, :FOURNISSEUR, :AJOUTER_PAR)";
                
                $stmt_deff = $pdo->prepare($sql);

                $stmt_deff->bindParam(':ID_GESTION', $nom_produit);
                $stmt_deff->bindParam(':ID_FOURNISSEUR', $id_fournisseur);
                $stmt_deff->bindParam(':DESIGNATION', $designation);
                $stmt_deff->bindParam(':QUANTITE', $quantite);
                $stmt_deff->bindParam(':DATE_AJOUT', $date_ajout);
                $stmt_deff->bindParam(':DESCRIPTION', $description);
                $stmt_deff->bindParam(':PRIX_UNITAIRE', $prix_unitaire);
                $stmt_deff->bindParam(':FOURNISSEUR', $fournisseur);
                $stmt_deff->bindParam(':AJOUTER_PAR', $ajouter_par);

                    // insert a row gestion stock
                $ex_deff= $stmt_deff->execute();                    

                    if ($ex_deff <> false) {

                        $quantite =($row_update_stock['QUANTITE']) - ($quantite);

                        $sql = "UPDATE gestion_stock SET QUANTITE='$quantite' WHERE ID_GESTION='".$row_update_stock['ID_GESTION']."'";

                        // Prepare statement
                        $update_gestion = $pdo->prepare($sql);

                        // execute the query
                        $ex_gestion_stock= $update_gestion->execute();

                    }

                } else {
                    $msg="Something went wrong";
                }

                // Attempt to execute the prepared statement
                if($ex_gestion_stock && $ex_deff){
                    // Records created successfully. Redirect to landing page
                    header("location: read_deffectueux.php");
                    exit();
                } else{
                    $msg="Something went wrong. Please try again later.";
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


<?=template_header('Creation deffectueux')?>
<script>

/* -------- ON INPUT DELETE ERROR ------------ */

// CONTROL INPUT NOM PRODUIT
function controlInputNOM_PRODUIT() {
  $("#erreur-nom_produit").css("display", "none");
     if($('#nom_produit').val() == ''){
        $("#erreur-nom_produit").css("display", "block");
   }
}
// CONTROL INPUT QUANTITE
function controlInputQUANTITE() {
  $("#erreur-quantite").css("display", "none");
     if($('#quantite').val() == ''){
        $("#erreur-quantite").css("display", "block");
   }
}
// CONTROL INPUT DATE ACHAT
function controlInputDATE_AJOUT() {
  $("#erreur-date_ajout").css("display", "none");
     if($('#date_ajout').val() == ''){
        $("#erreur-date_ajout").css("display", "block");
   }
}
// CONTROL INPUT DESCRIPTION
function controlInputDESCRIPTION() {
  $("#erreur-description").css("display", "none");
     if($('#description').val() == 'vide'){
        $("#erreur-description").css("display", "block");
   }
}

</script>

<?=template_content('deffectueux')?>
        
        <!-- CONTENU -->
        <div id="container-page" class="container">
    <!-- FIN FONCTION -->  
            <!-- navigation --> 
            <div id="navigation" class="row">
                <h5><a href="index.php"><i class='fas fa-home'></i></a> / Inventaire / Stocks déffectueux / Création nouveau stock déffectueux</h5>
                <div class="navigation-retour-active">
                  <a href="read_deffectueux.php"><i class="fas fa-chevron-circle-left"></i></a>
                </div>
                <div class="navigation-retour-non-active">
                  <a href=""><i class="fas fa-chevron-circle-right"></i></a>
                </div>                 
            </div>
                <!-- Fin navigation --> 
            <div id="cadre-contenu">
                <div class="cadre-title">
                    <h2><i class="fas fa-plus-circle"></i> AJOUTER UN STOCK DEFFECTUEUX</h2>
                </div>
                <div class="container mt-4 mb-5">
                    <form name="myForm" id="form-add3" action="add_deffectueux.php" method="post">
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
                                    <label for="quantite">QUANTITE</label>
                                    <input class="form-control" type="text" name="quantite" placeholder="quantite" value="0.0" oninput="controlInputQUANTITE();" id="quantite">
                                    <?php if(!empty($quantite_err)) : ?>
                                    <p id="erreur-quantite" class="erreur-p"><?= $icon_type ." ". $quantite_err?></p>
                                    <?php endif; ?>                                    
                                </div>
                                <div class="form-group">
                                    <label for="date_achat">DATE D'AJOUT</label>
                                    <input class="form-control" type="date" name="date_ajout" oninput="controlInputDATE_AJOUT();" id="date_ajout">
                                    <?php if(!empty($date_ajout_err)) : ?>
                                    <p id="erreur-date_ajout" class="erreur-p"><?= $icon_type ." ". $date_ajout_err?></p>
                                    <?php endif; ?>                                    
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description">DESCRIPTION</label>
                                    <textarea name="description" id="description" placeholder="Placez ici votre commentaire..." oninput="controlInputDESCRIPTION();"></textarea>
                                    <?php if(!empty($description_err)) : ?>
                                    <p id="erreur-description" class="erreur-p"><?= $icon_type ." ". $description_err?></p>
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

         <!--JAVASCRIPT ET SWEET ALERT -->
         <?php if (isset($_SESSION['alert_text']) && $_SESSION['alert_text']!='') : ?>
            <script>
              Swal.fire({
                  icon: '<?=$_SESSION['alert_icon']?>',
                  title: '<?=$_SESSION['alert_title']?>',
                  text: '<?=$_SESSION['alert_text']?>',
                  timer: 9000,
                })


            </script>
            <?php 
              unset($_SESSION['alert_icon']);
              unset($_SESSION['alert_title']);
              unset($_SESSION['alert_text']);
             ?>
         <?php endif; ?> 

        <!-- ///////////////////////// FIN MODAL DETAIL //////////////////////////// -->
  <script>
   $(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $('#myModal').modal('show');
    $('#modal-detail').modal('show');
  }); 
  </script>

<?=template_footer()?>

