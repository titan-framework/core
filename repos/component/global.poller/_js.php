<script language="JavaScript">
function ModificarResposta()
{
    j=document.enquete.elements["resposta[]"].selectedIndex;
    if(j>0)
    {
            document.enquete.nova_resposta.value = document.enquete.elements["resposta[]"].options[j].text;
        document.enquete.elements["resposta[]"].options[j]=null;
        tam=document.enquete.elements["resposta[]"].length;
        for(i=0;i<tam;i++)
            document.enquete.elements["resposta[]"].options[i].selected=false;
        document.enquete.elements["resposta[]"].selectedIndex=j-1;
    }
    else
        alert("Selecione uma resposta a ser modificada!");
}

function InserirResposta()
{
    var i;
    tam=document.enquete.elements["resposta[]"].length;
    j=document.enquete.elements["resposta[]"].selectedIndex;
    if(j==-1)
        j=tam;
    j++;
    for(i=tam;i>j;i--)
    {
        op = document.enquete.elements["resposta[]"].options[i-1];
        document.enquete.elements["resposta[]"].options[i] = new Option(op.text, op.value, false, false);
    }
    document.enquete.elements["resposta[]"].options[i] = new Option(document.enquete.nova_resposta.value, document.enquete.nova_resposta.value, false, false);
    tam++;
    j=i;
    for(i=0;i<tam;i++)
        document.enquete.elements["resposta[]"].options[i].selected=false;
    document.enquete.elements["resposta[]"].options[j].selected=true;
    document.enquete.nova_resposta.value = '';
}

function RemoverResposta()
{
    var i=0;
    document.enquete.elements["resposta[]"].options[0].selected=false;
    if(document.enquete.elements["resposta[]"].selectedIndex!=-1)
    {
        i=document.enquete.elements["resposta[]"].selectedIndex;
        while(document.enquete.elements["resposta[]"].selectedIndex>0)
        {
            i=document.enquete.elements["resposta[]"].selectedIndex;
            document.enquete.elements["resposta[]"].options[document.enquete.elements["resposta[]"].selectedIndex]=null;
        }
    }
    else
        alert("Selecione uma resposta a ser apagada!");
    if(i>=document.enquete.elements["resposta[]"].length)
        i=document.enquete.elements["resposta[]"].length-1;
    document.enquete.elements["resposta[]"].options[i].selected=true;
}

function Limpar()
{
    tam=document.enquete.elements["resposta[]"].length;
    if(tam>1)
    {
        for(i=0;i<tam;i++)
            document.enquete.elements["resposta[]"].options[i].selected=true;
        RemoverResposta();
    }
    document.enquete.reset();
}

function SubirResposta()
{
    i = document.enquete.elements["resposta[]"].selectedIndex;
    tam = document.enquete.elements["resposta[]"].length;
    if(i>1)
    {
        for(j=0;j<tam;j++)
            document.enquete.elements["resposta[]"].options[j].selected=false;
        op1 = document.enquete.elements["resposta[]"].options[i-1];
        op2 = document.enquete.elements["resposta[]"].options[i];
        document.enquete.elements["resposta[]"].options[i-1] = new Option(op2.text, op2.value, false, false);
        document.enquete.elements["resposta[]"].options[i] = new Option(op1.text, op1.value, false, false);
        document.enquete.elements["resposta[]"].options[i-1].selected = true;
    }
}

function DescerResposta()
{
    i = document.enquete.elements["resposta[]"].selectedIndex;
    tam = document.enquete.elements["resposta[]"].length;
    if(i>0 && i<tam-1)
    {
        for(j=0;j<tam;j++)
            document.enquete.elements["resposta[]"].options[j].selected=false;
        op1 = document.enquete.elements["resposta[]"].options[i+1];
        op2 = document.enquete.elements["resposta[]"].options[i];
        document.enquete.elements["resposta[]"].options[i+1] = new Option(op2.text, op2.value, false, false);
        document.enquete.elements["resposta[]"].options[i] = new Option(op1.text, op1.value, false, false);
        document.enquete.elements["resposta[]"].options[i+1].selected = true;
    }
}

function Enviar()
{
    tam=document.enquete.elements["resposta[]"].length;
    document.enquete.elements["resposta[]"].options[0].selected=false;
    for(i=1;i<tam;i++)
        document.enquete.elements["resposta[]"].options[i].selected=true;
    document.enquete.submit();
}
</script>