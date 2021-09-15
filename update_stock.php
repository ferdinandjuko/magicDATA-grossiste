
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
    $nom_produit = $ref = $unite = $quantite_unite = $prix_unitaire = $prix_total = $quantite = $date_achat = $fournisseur = $description = $photo = "";
    $date_achat_err = $fournisseur_err = $description_err = $photo_err = "";

    $icon_type= "<i class='fas fa-exclamation-triangle'></i>";


// Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page
$stmt = $pdo->prepare("SELECT * FROM fournisseur WHERE ID=".$_SESSION['id']);
$stmt->execute();
// Fetch the records so we can display them in our template.
$fournisseurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
if ($_SESSION['ID_STOCK']) {
    // code...
    $_GET['id']=$_SESSION['ID_STOCK'];
    unset($_SESSION['ID_STOCK']);
}


// Si le ID stock est definie
if (isset($_GET['id'])) {
    // Get the stock from the stock table
    $sql= "SELECT * FROM stock WHERE ID_STOCK = "."'".$_GET['id']."'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $stock = $stmt->fetch(PDO::FETCH_ASSOC);    

    if (!empty($_POST)) {

        function data_input($data) {
              $data = trim($data);
              $data = stripslashes($data);
              $data = htmlspecialchars($data);
              return $data;
            }
        
        // Validate Nom produit
        $nom_produit = $stock['NOM_PRODUIT'];

        // Validate reference
        $ref = $stock['REFERENCE'];

        // Validate unite
        $unite = $stock['UNITE'];

        // Validate quantite unite
        $quantite_unite = $stock['QUANTITE_UNITE'];

         // Validate prix unitaire
        $prix_unitaire = $stock['PRIX_UNITAIRE'];

        // Validate QUANTITE
        $quantite = $stock['QUANTITE'];

        // Validate Date d'achat
        $input_date_achat = date("m-d-Y");

        if(!empty($input_date_achat)){
            $date_achat = $input_date_achat;
        }
        // Validate FOURNISSEUR
        $input_fournisseur = data_input($_POST["fournisseur"]);

        if(empty($input_fournisseur)){
            $fournisseur_err = "Entrez votre fournisseur";
        } elseif (!preg_match("/^[0-1-2-3-4-5-6-7-8-9-a-zA-Z-' ]*$/",$input_fournisseur)) {
           $fournisseur_err = "Seulement les lettres et les chiffres sont acceptés";  // 1= erreur

        }else{
            $fournisseur = $input_fournisseur;
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

    // SELECT FOURNISSEUR
    $sql_nom="SELECT * FROM fournisseur WHERE ID_FOURNISSEUR='$fournisseur'";
    $query_nom = $pdo->prepare($sql_nom);
    $query_nom->execute();
    $row_nom = $query_nom->fetch(PDO::FETCH_BOTH);
    $nom_fournisseur=$row_nom['NOM'];


        // Check input errors before inserting in database
        if(empty($date_achat_err) && empty($fournisseur_err) && empty($description_err)){

            // prepare sql and bind parameters
            $sql="UPDATE stock SET DATE_ACHAT = '$date_achat', FOURNISSEUR = '$nom_fournisseur', DESCRIPTION = '$description', PHOTO = '$photo' WHERE ID_STOCK ="."'".$_GET['id']."'";
            $stmt = $pdo->prepare($sql);
            // Update the record
            $ex_update_stock = $stmt->execute();

                } 
                  
            // Attempt to execute the prepared statement
            if($ex_update_stock ){
                    // Records created successfully. Redirect to landing page
                    $msg = 'Updated Successfully!';
                    header("location: read_stock.php");
                    exit();
                } else{
                    $msg="Something went wrong. Please try again later.";
                    echo $msg;
                }        
        
    }

        if (!$stock) {
            exit('Stock doesn\'t exist with that ID!');
        }
 } else {
    exit('No ID specified!');
 }

?>

<?=template_header('Modification stocks')?>
<?=template_content('stocks')?>
   
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
        	<h2>Update Contact #<?=$stock['ID_STOCK']?></h2>
            <form action="update_stock.php?id=<?=$_GET['id']?>" method="post">

                <label for="nom_produit">DESIGNATION DU PRODUIT</label>
                <label for="prix_unitaire">PRIX UNITAIRE</label>
                <input readonly="true" type="text" name="nom_produit" placeholder="nom du produit" value="<?=$stock['NOM_PRODUIT']." ".$stock['REFERENCE']." ".$stock['QUANTITE_UNITE']." ".$stock['UNITE']?>" id="nom_produit">
                <input readonly="true" type="number" step="0.01" name="prix_unitaire" placeholder="Prix unitaire" value="<?=$stock['PRIX_UNITAIRE']?>" id="prix_unitaire"> 
                <label for="quantite">QUANTITE</label>     
                <label for="date_achat">DATE D'ACHAT</label>
                <input type="number" name="quantite" placeholder="QUANTITE" value="<?=$stock['QUANTITE']?>" id="quantite">        
                <input type="date" name="date_achat" placeholder="date d'achat" value="<?=$stock['DATE_ACHAT']?>" id="date_achat">        
                <label for="fournisseur">FOUNISSEUR</label>
                <label for="description">DESCRIPTION</label>
                <select class="custom-select mr-sm-2" name="fournisseur" style="width: 400px" id="inlineFormCustomSelect">
                    <option selected>Choose...</option>
                <?php foreach ($fournisseurs as $fournisseur): ?>                 
                    <option value="<?=$fournisseur['ID_FOURNISSEUR']?>"><?=$fournisseur['NOM']?></option>
                <?php endforeach; ?>
                </select>
                <input type="text" name="description" placeholder="description" value="<?=$stock['DESCRIPTION']?>" id="description">
                <input type="file" name="photo" placeholder="photo" value="<?=$stock['PHOTO']?>" id="photo">
               
                <p><?=$date_achat_err?></p><br>
                <p><?=$fournisseur_err?></p><br>
                <p><?=$description_err?></p>
                <p><?=$photo_err?></p>
                <input type="submit" value="Update">
            </form>
            <?php if ($msg): ?>
            <p><?=$msg?></p>
            <?php endif; ?>
        </div>

<?=template_footer()?>