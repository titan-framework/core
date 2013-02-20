
Titan Framework
===============
By Camilo Carromeu (http://www.carromeu.com/)

Aplicação gerada pelo Titan Architect ([URL]) em [DATE].

Para instalar esta aplicação em outro servidor Web primeiramente certifique-se que ele tenha os pré-requisitos necessários (http://wiki.ledes.net/index.php/Titan_Framework:_Pr%C3%A9-Requisitos).

Em seguida, siga os passos:

1. Descompacte o arquivo no diretório de WebApps do servidor (p.e., o diretório 'htdocs' do Apache)

2. Utilizando um cliente SVN (Subversion), faça checkout de https://svn.ledes.net/titan/core/ na pasta core/

3. Faça checkout de https://svn.ledes.net/titan/repository/ na pasta repos/

4. Dê direitos de escrita nas pastas file/, instance/ e cache/ para o servidor Web (no caso do Apache rodando no Debian ou Ubuntu, o usuário www-data )

5. Restaure o arquivo db.sql no SGBD PostgreSQL

6. Edite o arquivo configure/titan.xml de forma apropriada

Enjoy it ;)
