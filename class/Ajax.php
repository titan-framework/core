<?
class Ajax
{
	public function xoadGetMeta()
	{
		XOAD_Client::mapMethods ($this, array ());

		XOAD_Client::publicMethods ($this, array ());
		
		XOAD_Client::privateMethods ($this, array ());
	}
}
?>