<?
$search = new Search ('calendar.xml','search.xml', 'list.xml');

$search->recovery ();

$view = new View ('calendar.xml', 'list.xml');

$defaultAction="view";
if($view->getDefaultIcon())
	if($view->getDefaultIcon()->getAction())
		$defaultAction = $view->getDefaultIcon()->getAction();

if(!isset($_GET['dateShow']) || trim($_GET['dateShow'])=='')
	$dateShow = date("d-m-Y");
else
	$dateShow =$_GET['dateShow'];

$dateShowArray=explode("-",$dateShow);

if(!is_array($dateShowArray) || count($dateShowArray)!=3)
	throw new Exception("Data inválida");

$dayShow=(int)$dateShowArray[0];
$monthShow=(int)$dateShowArray[1];
$yearShow = (int)$dateShowArray[2];

if(isset($_POST['search__DATE_']) )
{
	$_START_ = implode("-",$_POST['search__DATE_']);
	$_SESSION['search__DATE_']=&$_POST['search__DATE_'];
}
else if(isset($_SESSION['search__DATE_']))
{
	$_START_ = implode("-",$_SESSION['search__DATE_']);
}
else
{
	$_START_ = "01-01-".$yearShow ;
}

if(isset($_POST['search__DATE_END_']) )
{
	$_END_ = implode("-",$_POST['search__DATE_END_']);
	$_SESSION['search__DATE_END_']=&$_POST['search__DATE_END_'];
}
else if(isset($_SESSION['search__DATE_END_']) )
{
	$_END_ = implode("-",$_SESSION['search__DATE_END_']);
}
else
	$_END_ = "31-12-".$yearShow;


if(isset($_POST['search']) && $_POST['search']==Search::TCLEAR)
{
	$_END_ = "31-12-".$yearShow;
	$_END_AUX=explode("-",$_END_);
	$_SESSION['search__DATE_END_']=&$_END_AUX;
	$_START_ = "01-01-".$yearShow ;
	$_START_AUX=explode("-",$_START_);
	$_SESSION['search__DATE_']=	&$_START_AUX;
}

$dateShow=$dayShow."-".$monthShow."-".$yearShow;

$timestampFirstDay = mktime(1,1,1,$monthShow,1,$yearShow);
$dayOfWeekFirstDay = date("w",$timestampFirstDay);

$maxDays = array(1=>31,	2=>28,	3=>31,	4=>30,	5=>31,	6=>30,	7=>31,	8=>31,	9=>30,	10=>31,	11=>30,	12=>31	);

if(($yearShow % 400 == 0) || ($yearShow % 4 == 0 && ($yearShow % 100) != 0))
	$maxDays[2]=29;

$monthArrayExt = array (
	 1 => __ ('January'),
	 2 => __ ('February'),
	 3 => __ ('March'),
	 4 => __ ('April'),
	 5 => __ ('May'),
	 6 => __ ('June'),
	 7 => __ ('July'),
	 8 => __ ('August'),
	 9 => __ ('September'),
	10 => __ ('October'),
	11 => __ ('November'),
	12 => __ ('December')
);

$monthArray = array (
	 1 => __ ('Jan'),
	 2 => __ ('Feb'),
	 3 => __ ('Mar'),
	 4 => __ ('Apr'),
	 5 => __ ('May'),
	 6 => __ ('Jun'),
	 7 => __ ('Jul'),
	 8 => __ ('Aug'),
	 9 => __ ('Sep'),
	10 => __ ('Oct'),
	11 => __ ('Nov'),
	12 => __ ('Dec')
);

$fieldDate= new Date($view->getTable(), array("id"=>"_DATE_", "type"=>"Date", "value"=>$_START_, "column"=>$view->getField("_DATE_")->getColumn()));
$fieldDate->setValue($_START_);
$fieldDateEnd= new Date($view->getTable(), array("id"=>"_DATE_END_", "type"=>"Date", "value"=>$_END_, "column"=>$fieldDate->getColumn()));

$stringWhere=$search->makeWhere ();

$whereDate='';
if(isset($_START_) && trim($_START_)!='' && isset($_END_) && trim ($_END_)!='' )
{
$stringWhere = trim($stringWhere)!=''? ($stringWhere.' AND '): ($stringWhere);

$valueStart=explode("-",$_START_);
$dayStart = $valueStart [0]; $monthStart = $valueStart [1]; $yearStart = $valueStart [2];
if(!isset($dayStart)) $dayStart=1;
if(!isset($monthStart)) $monthStart=1;
if(!isset($yearStart)) $yearStart=date("Y");

$valueEnd=explode("-",$_END_);
$dayEnd = $valueEnd [0]; $monthEnd = $valueEnd [1]; $yearEnd = $valueEnd [2];
if(!isset($dayEnd)) $dayEnd=1;
if(!isset($monthEnd)) $monthEnd=1;
if(!isset($yearEnd)) $yearEnd=date("Y");

$whereDate = '('.$fieldDate->getTable () .'.'. $fieldDate->getColumn () .' >= \''. $dayStart .'/'. $monthStart .'/'. $yearStart .'\'';
$whereDate .= ' AND '.$fieldDate->getTable () .'.'. $fieldDate->getColumn () .' <= \''. $dayEnd .'/'. $monthEnd .'/'. $yearEnd .'\')';

$stringWhere.=$whereDate;
}

if (!$view->load ($stringWhere))
	throw new Exception (__('Unable to load the data!'));

$itemsCalendar=array();
while($field = $view->getItem())
{
	$date = $view->getField('_DATE_')->getValue();
	$dateKey = $date[0]."-".$date[1]."-".$date[2];

	if(!array_key_exists($dateKey,$itemsCalendar))
		$itemsCalendar[$dateKey]=array();

	$iCount = count($itemsCalendar[$dateKey]);

	$itemsCalendar[$dateKey][$iCount]['_FIELD_'] = $field;
	$itemsCalendar[$dateKey][$iCount]['_ID_'] = $view->getId();
	$itemsCalendar[$dateKey][$iCount]['_TITLE_'] = Form::toHtml( $view->getField("_TITLE_") );
}
?>