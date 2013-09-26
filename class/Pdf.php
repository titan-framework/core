<?
class Pdf extends TitanFPDF
{
	protected $foot = '';
	
	function setFooter ($str)
	{
		$this->foot = $str;
	}
	
	function Footer ()
	{
		$this->SetY (-15);
		
		$this->SetFont ('Arial', 'I', 8);
		
		$this->Cell (0, 10, $this->foot . $this->PageNo () .' '. __ ('of') .' {nb}', 0, 0, 'C');
	}
	
	function StartRecord ()
	{
		$this->page_of_record = $this->page;
		$this->record = TRUE;
	}
	
	function EndRecord ()
	{
		$this->record = FALSE;
		$this->num_pages = 0;
	}
	
	function MultiTupla ($tamcol, $texto = array (), $orientacao = 'P', $align = array (), $altura = 6, $coordXbase = 10)
	{
		if($orientacao == 'P')
			$liminf=265;
		else
			$liminf=179;
		
		$max=0;
		$numcol=0;
		while(isset($tamcol[$numcol]))
		{
			if(!isset($align[$numcol]))
				$align[$numcol]='L';
			
			if(!isset($texto[$numcol]))
				$texto[$numcol]="";
			
			$numcol++;
		}
		
		for($i=0;$i<$numcol;$i++)
		{
			$aux = 0;
			if(is_array($texto[$i]))
			{
				$lin[$i] = array();
				foreach($texto[$i] as $linha)
				{
					$aux2 = 0;
					foreach($linha as $j => $value)
					{
						$this->StartRecord();
						$nlin=$this->MultiCell2($tamcol[$i][$j],$altura,$value,0,$align[$i],0,1);
						$this->EndRecord();			
						if($nlin[1] > $aux2)
							$aux2 = $nlin[1];
					}
					$lin[$i][] = $aux2;
					$aux += $aux2 + 1;
				}
				$aux = $aux - 1;
				$lin[$i][] = $aux;
			}
			else
			{
				
				$this->StartRecord();
				$nlin=$this->MultiCell2($tamcol[$i],$altura,$texto[$i],0,$align[$i],0,1);
				$this->EndRecord();
				
				//echo "c".$nlin[1]."d\n";
				
				$aux = $nlin[1];
				$lin[$i] = $aux;
			}
			
			if($max<$aux)
				$max=$aux;
		}
		
		$cont=($max+1)*$altura;
		
		if(($this->GetY() + ($max-1)*6)>$liminf)
		{
			$coordY = $this->GetY() + 1000;  
			$this->SetXY($coordXbase,$coordY);
			$this->Cell(0,0,"",0,0,'C');
		}
		
		$tam=0;
		for($i=0;$i<$numcol;$i++)
		{
			if(is_array($texto[$i]))
			{
				$coordYaux = $this->GetY();
				$tam2 = $lin[$i][sizeof($texto[$i])];
				$tam2 = ($max - $tam2)/sizeof($texto[$i]);
				$tam3 = 0;
				
				foreach($texto[$i] as $i_linha => $linha)
				{
					$tam3 = $tam;
					$alt_linha = ($tam2 + $lin[$i][$i_linha] + 1)*$altura;
					
					foreach($linha as $j => $value)
					{
						$coordX = $coordXbase+$tam3;
						$coordY = $this->GetY() + ($tam2+1)/2;  
						$this->SetXY($coordX,$coordY);
						$this->StartRecord();
						$nlin = $this->MultiCell2($tamcol[$i][$j],$altura, $value,0,$align[$i]);
						$this->EndRecord();
						
						$coordY = $this->GetY() - ($tam2+1)/2;  
						$this->SetXY($coordXbase+$tam3,$coordY);
						$this->Cell($tamcol[$i][$j],$alt_linha, "",1,0,'C');
						
						$tam3 = $tam3 + $tamcol[$i][$j];
						$this->SetXY($coordXbase+$tam3,$coordY);
					}
					$coordY = $this->GetY()+$alt_linha; 
					$this->SetXY($coordXbase+$tam,$coordY);
				}
				$tam = $tam3;
				$this->SetXY($coordXbase,$coordYaux);
				$this->Cell($tam,$cont, "",1,0,'C');			
			}
			else
			{
				$tam = $tam + $tamcol[$i];
				$tam2 = ($max-$lin[$i])*$altura/2;
				$coordY = $this->GetY() + $tam2;  
				$this->SetXY($coordXbase+$tam-$tamcol[$i],$coordY);
				$this->StartRecord();
				$this->MultiCell2($tamcol[$i],$altura, $texto[$i],0,$align[$i]);
				$this->EndRecord();
				$coordY = $this->GetY() - $tam2;  
				$this->SetXY($coordXbase,$coordY);
				$this->Cell($tam,$cont, "",1,0,'C');
			}
		}
	
		$coordY = $this->GetY() + $cont;  
		$this->SetXY($coordXbase,$coordY);
	
		return ($max+1);
	}
	
	function MultiCell2($w,$h,$txt,$border=0,$align='J',$fill=0,$test=0)
	{
		if($this->record)
			$this->page_aux = $this->page_of_record;
		//modificacoes...	
		$aux_y = $this->y;
		$cont_lines = 0;		
			
		//Output text with automatic or explicit line breaks
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
			$w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		if($nb>0 and $s[$nb-1]=="\n")
			$nb--;
		$b=0;
		if($border)
		{
			if($border==1)
			{
				$border='LTRB';
				$b='LRT';
				$b2='LR';
			}
			else
			{
				$b2='';
				if(is_int(strpos($border,'L')))
					$b2.='L';
				if(is_int(strpos($border,'R')))
					$b2.='R';
				$b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
			}
		}
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$ns=0;
		$nl=1;
		while($i<$nb)
		{
			//Get next character
			$c=$s[$i];
			if($c=="\n")
			{
				//modificacoes...
				$cont_lines++;
				
				//Explicit line break
				if($this->ws>0)
				{
					$this->ws=0;
					$this->_out('0 Tw');							
				}
				if($test==0)
					$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill, '', true);
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$ns=0;
				$nl++;
				if($border and $nl==2)
					$b=$b2;
				continue;
			}
			if($c==' ')
			{
				$sep=$i;
				$ls=$l;
				$ns++;
			}
			$l+=$cw[$c];
			if($l>$wmax)
			{
				//modificacoes...
				$cont_lines++;
						
				//Automatic line break
				if($sep==-1)
				{
					if($i==$j)
						$i++;
					if($this->ws>0)
					{
						$this->ws=0;
						$this->_out('0 Tw');										
					}
					if($test==0)
						$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill, '',true);
				}
				else
				{
					if($align=='J')
					{
						$this->ws=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
						$this->_out(sprintf('%.3f Tw',$this->ws*$this->k));
					}
					if($test==0)
						$this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill, '', true);
					$i=$sep+1;
				}
				$sep=-1;
				$j=$i;
				$l=0;
				$ns=0;
				$nl++;
				if($border and $nl==2)
					$b=$b2;
			}
			else
				$i++;
		}
		//Last chunk
		if($this->ws>0)
		{
			$this->ws=0;
			$this->_out('0 Tw');
		}
		if($border and is_int(strpos($border,'B')))
			$b.='B';
		if($test==0)
			$this->Cell($w,$h,substr($s,$j,$i),$b,2,$align,$fill, '', true);
		$this->x=$this->lMargin;
		
		//modificacoes
		//ret[0]: coordenada y
		//ret[1]: numero de linhas	
		$ret[0] = $this->y;
		$ret[1] = $cont_lines;
		$this->SetY($aux_y);
		return $ret;
	}
	
	function PrintLine ($width, $text, $x, $y, $border = 1, $tipo = 'f', $ind_J = -1, $B = 1)
	{
		// $vet = vetor contendo os elementos de cada coluna e o seu tamanho ex: $vet = array("text"=> 'teste', "width" => '30');
		// tipo 'f' é formulario, tipo 't' é tabela
		
		$coordY=$this->GetY();
		//echo $coordY." ".$text[0]."<br>";
		if ($this->PageEnd($coordY))
		{	
			$this->SetXY(15,$coordY);
			return $this->PrintLine($width, $text,$x,$coordY,$border,$tipo,$ind_J,$B);		
		}
		
		$n = sizeof ($width);
		$max = array();
			
		if ($tipo == 'f' )
			$align = "J";
		else
			$align = "C";
	
		for ($i=0;$i<$n;$i++)
		{
		
			if ($tipo == 'f' )
				if ( $i%2 == 0 )
					$this->SetFont('Arial','B');
				else
					$this->SetFont('Arial','');
			else if ($tipo=='t' )
				if ($i==0 && $B)
					$this->SetFont('Arial','B');
				else
					$this->SetFont('Arial','');
	
			$var = $this->MultiCell($width[$i],5,$text[$i],1,$align,0,1);
			$max[$i]=($var[1]+1)*5;	
		}
		$altura = max($max);
		$ind = array_search($altura ,$max);
		
		//Inicio da Impressao
		for ($i=0;$i<$n;$i++)
		{
			if ($ind_J != -1)
				if ( $i == $ind_J)
					$align = "J";
				else 
					$align = "C";
	
			if ($tipo == 'f' )
				if ( $i%2 == 0 )
					$this->SetFont('Arial','B');
				else
					$this->SetFont('Arial','');
			else if ($tipo=='t')
				if ($i==0 && $B)
					$this->SetFont('Arial','B');
				else
					$this->SetFont('Arial','');
				
			$this->SetXY($x,$y);
			$temp=5*($altura/$max[$i])	;	
			//echo "$i ->  $temp<br>";
			//echo $align." ".$text[$i]."<br>";
			$this->MultiCell($width[$i],$temp,$text[$i],$border,$align,0,0);
			$x += $width[$i];	
		}
		//echo "altura = $altura <br>";
		return $altura;
	}
	
	function PageEnd ($coordY)
	{
		return $coordY >= $this->h;
	}
}
?>