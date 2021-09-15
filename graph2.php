<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_bar.php');

include 'functions.php';
# Démarrage de la session
session_start();

# Si la session n'est pas définie, rediriger vers la page de connexion, si l'url est prédéfinie avant la connexion rediriger vers l'url prédéfinie après la connexion
session_url();

// Connect to MySQL database
$pdo = pdo_connect_mysql();

// Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page
/*$query = "SELECT vente.NOM_PRODUIT, vente.DATE_AJOUT, vente.BENEFICE FROM vente, client WHERE (client.ID_CLIENT=vente.ID_CLIENT) AND (client.ID=".$_SESSION['id'].")";*/
$query = "SELECT * FROM vente WHERE (client.ID_CLIENT=vente.ID_CLIENT) AND (client.ID=".$_SESSION['id'].")";
$stmt = $pdo->prepare($query);
$stmt->execute();
// Fetch the records so we can display them in our template.
/*$graphs = $stmt->fetch(PDO::FETCH_ASSOC);*/
$graph = "";
/*foreach ($graphs as $graph) {
	$graphique = array($graph);

}*/


/*while($graphs = $stmt->fetch(PDO::FETCH_ASSOC)){
    	$graph = array($graphs);
    }
  	
  	var_dump($graph);
  	exit();
		*/

$datay=array(62,105,42,42,35,80);


// Create the graph. These two calls are always required
$graph = new Graph(870,400, 'auto');
$graph->SetScale("textlin");

$theme_class=new UniversalTheme;
$graph->SetTheme($theme_class);

// set major and minor tick positions manually
$graph->yaxis->SetTickPositions(array(0,30,60,90,120,150), array(15,45,75,105,135));
$graph->SetBox(false);

//$graph->ygrid->SetColor('gray');
$graph->ygrid->SetFill(false);
$graph->xaxis->SetTickLabels(array('verre', 'table', 'banc', 'clavier', 'souris', 'cable'));
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);

// Create the bar plots
$b1plot = new BarPlot($datay);

// ...and add it to the graPH
$graph->Add($b1plot);


$b1plot->SetColor("#0f3b6f");
$b1plot->SetFillGradient("#243e6b","#5d95f5",GRAD_HOR);
$b1plot->SetWidth(45);
$graph->title->Set("");

// Display the graph
$graph->Stroke();
?>