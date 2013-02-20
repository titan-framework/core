<?
function ifa ($Image, $CenterX, $CenterY, $DiameterX, $DiameterY, $Start, $End, $Color, $color2) 
{
	// To draw the arc
	//imagearc($Image, $CenterX, $CenterY, $DiameterX, $DiameterY, $Start, $End, $Color);
	// To close the arc with 2 lines between the center and the 2 limits of the arc
	$x = $CenterX + (cos(deg2rad($Start))*($DiameterX/2));
	$y = $CenterY + (sin(deg2rad($Start))*($DiameterY/2));
	imageline($Image, $x, $y, $CenterX, $CenterY, $Color);
	$x = $CenterX + (cos(deg2rad($End))*($DiameterX/2));
	$y = $CenterY + (sin(deg2rad($End))*($DiameterY/2));
	imageline($Image, $x, $y, $CenterX, $CenterY, $Color);
	
	// To fill the arc, the starting point is a point in the middle of the closed space
	$x = $CenterX + (cos(deg2rad(($Start+$End)/2))*($DiameterX/4));
	$y = $CenterY + (sin(deg2rad(($Start+$End)/2))*($DiameterY/4));
	imagefilltoborder($Image, $x, $y, $Color,$color2);
}

function infa($Image, $CenterX, $CenterY, $DiameterX,$DiameterY, $Start, $End, $Color)
{
	// To draw the arc
	imagearc($Image, $CenterX, $CenterY, $DiameterX, $DiameterY, $Start,$End, $Color);
	
	// To close the arc with 2 lines between the center and the 2 limits of the arc
	$x = $CenterX + (cos(deg2rad($Start))*($DiameterX/2));
	$y = $CenterY + (sin(deg2rad($Start))*($DiameterY/2));
	imageline($Image, $x, $y, $CenterX, $CenterY, $Color);
	$x = $CenterX + (cos(deg2rad($End))*($DiameterX/2));
	$y = $CenterY + (sin(deg2rad($End))*($DiameterY/2));
	imageline($Image, $x, $y, $CenterX, $CenterY, $Color);
}

function dec2hex ($number, $length) 
{
	$hexval="";
	while ($number > 0) 
	{
		$remainder=$number%16;
		if ($remainder<10)
			$hexval=$remainder.$hexval;
		elseif ($remainder==10)
			$hexval="a".$hexval;
		elseif ($remainder==11)
			$hexval="b".$hexval;
		elseif ($remainder==12)
			$hexval="c".$hexval;
		elseif ($remainder==13)
			$hexval="d".$hexval;
		elseif ($remainder==14)
			$hexval="e".$hexval;
		elseif ($remainder==15)
			$hexval="f".$hexval;
		$number=floor($number/16);
	}
	while (strlen ($hexval) < $length)
		$hexval="0".$hexval;
	
	//this is just to add zero's at the beginning to make hexval a certain length
	return $hexval;
}

if(isset($_GET ["largura"]))
	$largura = $_GET ["largura"];
else
	$largura = 200;

if(isset($_GET ["altura"]))
	$altura = $_GET ["altura"];
else
	$altura = 200;

if(isset($_GET ["partes"]))
	$partes = $_GET ["partes"];
else
	$partes = array(10.3,5.4,28.1,36.2,5.6,14.4);

$largura_pizza = round(0.75 * $largura, 0);
$altura_pizza  = round(0.75 * $altura, 0);

header ("Content-type: image/png");

$img   = imagecreate($largura,$altura);
$fundo = imagecolorallocate($img, 255,255,255);
$cor1  = imagecolorallocate($img, 0xCC,0xCC,0xCC);

imagearc ($img,round($largura/2,0),round($altura/2,0),$largura_pizza,$altura_pizza,0,360,$cor1);

$num = count($partes);

$inicio = 0;

for($i = 1 ; $i <= $num ; $i++)
{
	$final = round ($inicio + 3.6 * $partes [$i]);
	
	if($i==$num)
		$final = 360;
	
	$cor_atual = colors ($i);
	$cor2 = imagecolorallocate($img, hexdec(substr($cor_atual, 0, 2)),hexdec(substr($cor_atual, 2, 2)),hexdec(substr($cor_atual, 4, 2)));
	
	ifa ($img,round($largura/2,0),round($altura/2,0),$largura_pizza,$altura_pizza,$inicio,$final,$cor1,$cor2);
	$inicio = $final;
}

imagepng ($img);
?>