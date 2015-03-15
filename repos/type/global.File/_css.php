<style type="text/css">
.globalFileUpload
{
	position: relative;
	float: left;
	width: 500px;
	height: 47px;
	padding: 2px;
	margin: 0px;
	border: #AAA 1px solid;
	background: none #FFF;
}

.globalFileIframe
{
	float: left;
	overflow: hidden;
	width: 500px;
	height: 47px;
	border-width: 0px;
	margin: 0px;
	padding: 0px;
}

.globalFileError
{
	position: relative;
	margin: 0px 0px 4px 0px;
	border: #900 1px solid;
	padding: 6px;
	background-color: #EBCCCC;
	color: #900;
	font-weight: bold;
	width: 492px;
}

.globalFileUploaded
{
	position: relative;
	float: left;
	border: #AAA 1px solid;
	background: none #FFF;
	padding: 2px;
}

.globalFileArchive
{
	float: right;
	width: 40px;
	height: 47px;
	border-width: 0px;
	margin: 0px;
	background: #EEE url(titan.php?target=tResource&type=File&file=archive.png) no-repeat top right;
	cursor: pointer;
	border: 0px;
	padding: 0px;
}

.globalFileArchive:hover
{
	background-position: bottom;
}

.globalFileSearchBox
{
	border: none;
	color: #888;
	background: url(titan.php?target=tResource&type=File&file=search.png) no-repeat;
	float: left;
	font-family: Arial,Helvetica,sans-serif;
	font-size: 15px;
	height: 36px;
	line-height: 36px;
	margin-right: 12px;
	outline: medium none;
	padding: 0 0 0 35px;
	text-shadow: 1px 1px 0 white;
	width: 420px;
}

.globalFileSearchInfo
{
	font-family: Verdana, Geneva, sans-serif;
	font-size: 12px;
	height: 36px;
	line-height: 36px;
	vertical-align: middle;
	outline: medium none;
	color: #000;
	margin: 5px;
	border-bottom: 1px #CCC solid;
	font-weight: bold; 
}

.globalFileSearchError
{
	margin: 10px auto;
	border: #900 1px solid;
	padding: 6px;
	background-color: #EBCCCC;
	color: #900;
	font-weight: bold;
	width: 500px;
}

.globalFileSearchResult ul,
.globalFileSearchResult li
{
	display: inline-block;
	list-style: none;
	padding: 0px;
	margin: 0px;
}

.globalFileSearchResult li
{
	width: 100px;
	height: 150px;
	margin: 5px;
	padding: 5px;
	background: none #FFF;
	cursor: pointer;
	overflow: hidden;
	text-align: center;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	-moz-box-shadow: 3px 3px 3px #888;
	-webkit-box-shadow: 3px 3px 3px #888;
	box-shadow: 3px 3px 3px #888;
}

.globalFileSearchResult li:hover
{
	background: none #CCEBCC;
}

.globalFileSearchResult li span
{
	font-family: "Courier New", Courier, monospace;
	font-size: 10px;
	word-wrap: break-word;
}
</style>