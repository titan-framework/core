<?
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
	$largura = 10;

if(isset($_GET ["altura"]))
	$altura = $_GET ["altura"];
else
	$altura = 10;
        
if(isset($_GET["cor"]))
	$cor = $_GET["cor"];
else
	$cor = "000000";

header ("Content-type: image/jpeg");

$img = imagecreate($largura,$altura);

$fundo = imagecolorallocate($img, hexdec(substr($cor, 0, 2)),hexdec(substr($cor, 2, 2)),hexdec(substr($cor, 4, 2)));

imagejpeg($img,"",100);
?>