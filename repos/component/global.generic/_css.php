<style type="text/css">
#sortableList {
	list-style-type: none;
	padding: 3px 5px 3px 5px;
	margin: 0 auto;
	width: 500px;
	border: 2px solid #DDDDDD;
}
#sortableList li {
	padding: 5px 15px 5px 5px;
	margin: 3px 0px 3px 0px;
	border: 1px solid #AAAAAA;
	background-color: #F4F4F4;
	font-weight: bold;
	text-align: justify;
	background: url(titan.php?target=loadFile&file=interface/icon/sort.gif) right no-repeat;
}
#sortableList li:hover
{
	cursor: move;
	background-color: #EBCCCC;
	border-color: #990000;
}


#idListCalendar{margin:12px;}
#idListCalendar .Canvas {width: 97%;border-color: #36817C;border-width: 10pt;border-style: Solid;text-align:Center;background-color: #FFFFFF;margin: 0px;}
#idListCalendar table.calendar {width: 100%;border-color: #36817C;border-width: 0pt;border-style: Solid;text-align:Center;border-collapse: collapse;background-color: #36817C;margin: 0px;}
#idListCalendar .MonthCaption {font-family: Georgia, "Times New Roman", Times, serif, Helvetica;font-size: 27pt;font-weight: Bold;font-style: Normal;color: #36817C;text-decoration: None;border-color: #36817C;border-width: 1pt;border-style: Solid;text-align: Center;background-color: #FFFFFF;padding: 5px;margin: 0px;}
#idListCalendar .Weekdays {font-family: Georgia, "Times New Roman", Times, serif, Helvetica;font-size: 12pt;font-weight: Bold;font-style: Normal;color: #FFFFFF;text-decoration: None;border-color: #000000;border-width: 0pt;border-style: Solid;text-align: Center;background-color: #36817C;}
#idListCalendar .Date {font-family: Georgia, "Times New Roman", Times, serif, Helvetica;font-size: 12pt;font-weight: Bold;font-style: Normal;color: #36817C;text-decoration: None;border-color: #36817C;border-width: 0pt;border-style: Solid;text-align: Left;background-color: #FFFFFF;background: transparent;}
#idListCalendar .Holidays {font-family: Arial;font-size: 7pt;font-weight: Bold;font-style: Normal;color: #000000;t7xt-decoration: None;border-color: #36817C;border-width: 0pt;border-style: Solid;text-align: Left;background-color: #FFFFCC;}
#idListCalendar .events {font-family: Arial;font-size: 7pt;font-weight: Normal;font-style: Normal;color: #000000;text-decoration: None;border-color: #36817C;border-width: 1pt;border-style: Solid;text-align: Left;padding: 2px;background-color: #FFFFFF; height:100px;}
#idListCalendar #header {    float:center;      width:95%;      background:#white repeat-x bottom;      font-size:90%;      line-height:normal;       }
#idListCalendar #header ul {      margin:0;      padding:10px 10px 0;      list-style:none;      text-align:center;       margin-left:auto;      margin-right:auto;}
#idListCalendar #header li {      float:left;      background:url("titan.php?target=resource&toSection=<?=$section->getName();?>&file=left.gif") no-repeat left top;      margin:0;      padding:0 0 0 9px;      }
#idListCalendar #header a {      display:block;      background:url("titan.php?target=resource&toSection=<?=$section->getName();?>&file=right.gif") no-repeat right top;      padding:5px 15px 5px 6px;      text-decoration:none;      font-weight:bold;      color:white;       }
#idListCalendar #header a:hover {      color:#333;  }
#idListCalendar    #header #current {      background-image:url("titan.php?target=resource&toSection=<?=$section->getName();?>&file=left_on.gif"); }
#idListCalendar    #header #current a {      background-image:url("titan.php?target=resource&toSection=<?=$section->getName();?>&file=right_on.gif"); color:#333; padding-bottom:5px;}
#idListCalendar .Calendar { width: 97%; margin-right  : auto; margin-left  : auto; margin-top: 5px; } 
#idListCalendar .NavMenu a:link    {  color: #F88017; font-size:7pt;}
#idListCalendar .NavMenu a:visited {  color: #F88017; font-size:7pt;}
#idListCalendar .NavMenu a:hover   {  color: #F88017; font-size:7pt;}
#idListCalendar .NavMenu a:active  {  color: #F88017; font-size:7pt;}

</style>