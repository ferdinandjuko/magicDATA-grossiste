
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
    $nom_produit = $quantite = $date_ajout = $description = "";
    $nom_produit_err = $quantite_err = $date_ajout_err = $description_err = "";
    $icon_type= "<i class='fas fa-exclamation-triangle'></i>";

// Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page
$stmt = $pdo->prepare("SELECT * FROM stock WHERE ID=".$_SESSION['id']);
$stmt->execute();
// Fetch the records so we can display them in our template.
$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Check if the contact id exists, for example update.php?id=1 will get the contact with the id of 1
if (isset($_GET['id'])) {

    if (!empty($_POST)) {

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
        } elseif (!preg_match("/^[0-1-2-3-4-5-6-7-8-9-a-zA-Z-' ]*$/",$input_nom_produit)) {
           $nom_produit_err = "Seulement les lettres et les espaces sont acceptés";
        } else{
           $nom_produit = $input_nom_produit;
        }
          
        // Validate QUANTITE
        $input_quantite = data_input($_POST["quantite"]);
        if(empty($input_quantite)){
            $quantite = 0;
            $quantite_err="Quantité vide";
        } elseif (!filter_var($input_quantite, FILTER_VALIDATE_INT) === true) {
           $quantite = "Seulement les chiffres sont acceptés";
        } else{
            $quantite = $input_quantite;
        }
        // Validate Date d'ajout
        $input_date_ajout = date("m/d/Y");

        if(!empty($input_date_ajout)){
            $date_ajout = $input_date_ajout;
        }
               
        // Validate description
        $input_description = data_input($_POST["description"]);

        if(empty($input_description)){
            $description_err = "Inserer votre votre description";
        } elseif (!preg_match("/^[0-1-2-3-4-5-6-7-8-9-a-zA-Z-' ]*$/",$input_description)) {
           $description_err = "Seulement les lettres et les chiffres sont acceptés";  // 1= erreur

        }else {
            $description = $input_description;
        }  

        // Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page


        $sql_nom="SELECT * FROM stock WHERE ID_STOCK='$nom_produit'";
        $query_nom = $pdo->prepare($sql_nom);
        $query_nom->execute();
        $row_nom = $query_nom->fetch(PDO::FETCH_BOTH);
        $row_noun = $row_nom['NOM_PRODUIT'];



        // Check input errors before inserting in database
        if(empty($nom_produit_err) && empty($quantite_err) && empty($date_ajout_err) && empty($description_err)){
                // prepare sql and bind parameters
                $sql="UPDATE deffectueux SET NOM_PRODUIT = '$row_noun', QUANTITE = '$quantite', DATE_AJOUT = '$date_ajout', DESCRIPTION = '$description' WHERE ID_DEFF ="."'".$_GET['id']."'";
                $stmt = $pdo->prepare($sql);

            // Update the record
            //var_dump($sql);
            $ex = $stmt->execute();
            // Attempt to execute the prepared statement
                if($ex){
                    // Records created successfully. Redirect to landing page
                    $msg = 'Updated Successfully!';
                    header("location: read_deffectueux.php");
                    exit();
                } else{
                    $msg="Something went wrong. Please try again later.";
                    echo $msg;
                }        
         }
        
    }
        // Get the contact from the contacts table
        $sql= "SELECT * FROM deffectueux WHERE ID_DEFF = "."'".$_GET['id']."'";
        $stmt = $pdo->prepare($sql);
        //var_dump($sql);
        $stmt->execute();
        $deffectueux = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$deffectueux) {
            exit('Stock doesn\'t exist with that ID!');
        }
 } else {
    exit('No ID specified!');
 }

?>

<?=template_header('Modification deffectueux')?>
<?=template_content('deffectueux')?>
     
        <!-- CONTENU -->
        <div class="container">
            <div id="search-cadre" class="row">
                <div class="col-md-6">
                    <h3>Bienvenue dans Ilo grossiste</h3>
                </div>
                <div class="col-md-6">
                  <form action="/action_page.php">
                    <div id="input-group" class="input-group">
                      <input id="search" type="text" class="form-control" placeholder="Rechercher"><i class="fas fa-search"></i>
                    </div>
                  </form>   
                </div>          
            </div>
        <!-- FIN FONCTION -->   

                <!-- navigation --> 
            <div id="navigation" class="row">
                <h5><i class='fas fa-home'></i> / Tableau de bord</h5>
            </div>
                <!-- Fin navigation --> 
        <div class="content update">
        	<h2>Update Defective #<?=$deffectueux['ID_DEFF']?></h2>
            <form action="update_deffectueux.php?id=<?=$_GET['id']?>" method="post">

                <label for="nom_produit">NOM DE PRODUIT</label>
                <label for="quantite">QUANTITE</label>
                <select class="custom-select mr-sm-2" name="nom_produit" style="width: 400px" id="inlinFormCustomSelect">
                    <option selected>Choose...</option>
                    <?php foreach ($stocks as $stock): ?>
                        <option value="<?=$stock['ID_STOCK']?>"><?=$stock['NOM_PRODUIT']." (".$stock['REFERENCE']." ".$stock['QUANTITE_UNITE'].$stock['UNITE']?>)</option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="quantite" placeholder="quantite" value="<?=$deffectueux['QUANTITE']?>" id="quantite">
                <label for="date_ajout">DATE D'AJOUT</label>
                <label for="description">DESCRIPTION</label>
                <input type="date" name="date_ajout" placeholder="date d'ajout" value="<?=$deffectueux['DATE_AJOUT']?>" id="date_ajout">
                <input type="text" name="description" placeholder="description" value="<?=$deffectueux['DESCRIPTION']?>" id="description">

               
                <p><?=$nom_produit_err?></p><br>
                <p><?=$quantite_err?></p><br>
                <p><?=$date_ajout_err?></p><br>
                <p><?=$description_err?></p>
                <input type="submit" value="Update">
            </form>
            <?php if ($msg): ?>
            <p><?=$msg?></p>
            <?php endif; ?>
        </div>

<?=template_footer()?>