<?
function formatCnpj ($cnpj)
{
	return Cnpj::format ($cnpj);
}

function formatCpf ($cpf)
{
	return Cpf::format ($cpf);
}

function formatRga ($rga)
{
	return Rga::format ($rga);
}

function formatCep ($cep)
{
	return Cep::format ($cep);
}

function integerValidate ($str)
{
	return Integer::validate ($str);
}

function floatValidate ($str)
{
	return Float::validate ($str);
}

function textValidate ($str)
{
	return String::validate ($str);
}

function limitText ($str, $size)
{
    return String::limit ($str, $size);
}

function editValidate ($str)
{
	return Edit::validate ($str);
}

function coordinate ($coord)
{
	return Coordinate::toKml ($coord);
}

function dircopy ($srcdir, $dstdir, $verbose = FALSE)
{
	copyDir ($srcdir, $dstdir, $verbose);
}
?>