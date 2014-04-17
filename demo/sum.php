<?php

require('../autoload.php');

use Gregwar\Tex2png\Tex2png;

session_start();

if($_SERVER['REQUEST_METHOD'] === 'POST'){

	//echo "Generating sum.png...\n";

	Tex2png::create($_REQUEST['tex'])
    ->saveTo('sum.png')
    ->generate();

    $_SESSION['tex'] = $_REQUEST['tex'];

    header('Location: ' . $_SERVER["REQUEST_URI"]);
    die();
}

?>
<img src="sum.png?r=<?php echo rand()  ?>" />
<hr />
<form action="" method="post">
	<? if(isset($_SESSION['tex'])): ?>
	<textarea name="tex" rows="3" style="width:600px;font-size:16px"><?php echo $_SESSION['tex'] ?></textarea>
	<? else: ?>
	<textarea name="tex" rows="3" style="width:600px;font-size:16px">\sum_{i = 0}^{i = n} \frac{i}{2}</textarea>
	<? endif; ?>
	<br>
	<input type="submit" value="Submit" />
</form>