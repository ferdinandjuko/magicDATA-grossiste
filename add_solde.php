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
    $solde = $solde_actuel = $etat = $responsable= $date_ajout= $id_resp= "";
    $solde_actuel_err = $responsable_err = "";
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
        
           
        // Validate SOLDE ACTUEL
        $input_solde_actuel = data_input($_POST["solde_actuel"]);

        if(empty($input_solde_actuel) || $input_solde_actuel==0.0) {
            $solde_actuel_err = "Solde actuel vide";
        }elseif (!preg_match("/^[0-9,. ]*$/",$input_solde_actuel)) {
           $solde_actuel_err = "Seulement les chiffres sont acceptés pu";
        } else{
            $solde_actuel = $input_solde_actuel;
        } 

        // Validate SOLDE
            $solde=0;

        // Validate ETAT
           $etat ="DEPOT (+ Crédit)";

        // Validate DATE
           $date_ajout = date("Y-m-d");

        // Validate RESPONSABLE
           $responsable = $_SESSION['name']; 
           if (empty($responsable)) {
                 $responsable_err= "Session nom de l'utilisateur non definie! veuillez reconnecter";
                }      
        // ID SESSION
        $id_resp = $_SESSION['id'];


        // Check input errors before inserting in database
        if(empty($solde_actuel_err) && empty($responsable_err)) {

            $num_gestion_caisse = $pdo->query("SELECT COUNT(*) FROM gestion_caisse WHERE (gestion_caisse.SESSION_ID=".$_SESSION['id'].")")->fetchColumn();
            

            if ($num_gestion_caisse > 0) {
                # DEPOT SOLDE 
                $sql_id="SELECT MAX(ID_GESTION_CAISSE) AS max_id FROM gestion_caisse WHERE (SESSION_ID=".$_SESSION['id'].")";
                $query_id = $pdo->prepare($sql_id);
                $query_id->execute();
                $row_id = $query_id->fetch(PDO::FETCH_BOTH);
                $last_id = $row_id['max_id'];
                // selectionner solde actuel
                $sql_select="SELECT * FROM gestion_caisse WHERE ID_GESTION_CAISSE="."'".$last_id."'";
                $query_select = $pdo->prepare($sql_select);
                $query_select->execute();
                $row_gestion_caisse = $query_select->fetch(PDO::FETCH_BOTH);
                $solde_actuel_base = $row_gestion_caisse['SOLDE_ACTUEL'];
                // definition des variables
                $solde_actuel_ajout = doubleval($solde_actuel_base)+doubleval($solde_actuel);
                // MOTIF
                $motif= "Dépôt caisse";                   

            } else {

                // MOTIF
                $motif= "Dépôt Solde de départ";
                $solde_actuel_ajout = $solde_actuel;

             }

                $sql="INSERT INTO gestion_caisse (SESSION_ID, DATE_AJOUT, SOLDE_ACTUEL, SOLDE, ETAT, MOTIF, RESPONSABLE)
                VALUES ('$id_resp', '$date_ajout', '$solde_actuel_ajout', '$solde_actuel', '$etat', '$motif', '$responsable')";
                
                $stmt = $pdo->prepare($sql);

                $stmt->bindParam(':SESSION_ID', $id_resp);
                $stmt->bindParam(':DATE_AJOUT', $date_ajout);
                $stmt->bindParam(':SOLDE_ACTUEL', $solde_actuel_ajout);
                $stmt->bindParam(':SOLDE', $solde_actuel);
                $stmt->bindParam(':ETAT', $etat);
                $stmt->bindParam(':MOTIF', $motif);
                $stmt->bindParam(':RESPONSABLE', $responsable);

                // insert a row gestion stock
                $ex = $stmt->execute(); 

                // Attempt to execute the prepared statement
                if($ex <> false){
                        // Records created successfully. Redirect to landing page
                        $_SESSION['alert_icon'] = "success";
                        $_SESSION['alert_title'] = "SUCCES";
                        $_SESSION['alert_text'] = "Votre solde à été ajouté avec succès!";
                        header("location: read_caisse.php");
                        exit();
                    } else{
                        $_SESSION['alert_icon'] = "error";
                        $_SESSION['alert_title'] = "ERREUR";
                        $_SESSION['alert_text'] = "Something went wrong. Please try again later."; 
                        header("location: add_solde.php");
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

<?=template_header('Creation de solde')?>

<script>

/* -------- ON INPUT DELETE ERROR ------------ */

// CONTROL INPUT NOM PRODUIT
function controlInputSOLDE_ACTUEL() {
  $("#erreur-solde_actuel").css("display", "none");
     if($('#solde_actuel').val() == ''){
        $("#erreur-solde_actuel").css("display", "block");
   }
}

</script>

<?=template_content('caisse')?>
        
        <!-- CONTENU -->
        <div id="container-page" class="container">
    <!-- FIN FONCTION -->  
            <!-- navigation --> 
            <div id="navigation" class="row">
                <h5><a href="index.php"><i class='fas fa-home'></i></a> / Caisses / Création de nouvelle solde</h5>
                <div class="navigation-retour-active">
                  <a href="read_caisse.php"><i class="fas fa-chevron-circle-left"></i></a>
                </div>
                <div class="navigation-retour-non-active">
                  <a href=""><i class="fas fa-chevron-circle-right"></i></a>
                </div>                 
            </div>
                <!-- Fin navigation --> 
            <div id="cadre-contenu">
                <div class="cadre-title">
                    <h2><i class="fas fa-plus-circle"></i> AJOUTER UNE SOLDE</h2>
                </div>
                <div class="container mt-5">
                    <form name="myForm" id="form-add2" action="add_solde.php" method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="solde_actuel">SOLDE</label>
                                    <input class="form-control" type="number" step="0.01" name="solde_actuel" placeholder="0.0" oninput="controlInputSOLDE_ACTUEL();" value="0.0" id="sa">
                                    <?php if(!empty($solde_actuel_err)) : ?>
                                    <p id="erreur-solde_actuel" class="erreur-p"><?= $icon_type ." ". $solde_actuel_err?></p>
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

 <!-- // SWEET ALERT NOTIFICATION // -->
<?=sweet_alert_notification()?>

<?=template_footer()?>

