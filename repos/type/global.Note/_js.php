<script language="javascript" type="text/javascript">
'global.Note'.namespace ();

global.Note.view = function (id, icon)
{
    alert (id);
}

global.Note.earth = function (id)
{
    document.location = 'titan.php?target=tScript&type=Note&file=kml&note=' + id;
}

global.Note.delete = function (id, icon)
{
    alert (id);
}
</script>