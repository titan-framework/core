<?
ob_start ();
?>
/**
 * Copyright © 2013 Titan Framework. All Rights Reserved.
 *
 * Developed by Laboratory for Precision Livestock, Environment and Software Engineering (PLEASE Lab)
 * of Embrapa Beef Cattle at Campo Grande - MS - Brazil.
 * 
 * @see http://please.cnpgc.embrapa.br
 * 
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @author Jairo Ricardes Rodrigues Filho <jairocgr@gmail.com>
 * 
 * @version <?= date ('y.m') ?>-1-alpha
 */

package <?= $app ?>.ws;

import org.apache.http.HttpResponse;

import <?= $app ?>.exception.TechnicalException;
import <?= $app ?>.util.HttpHelper;
import <?= $app ?>.util.WebServiceHelper;

public class <?= $model ?>WebService
{
	private HttpResponse response;
	
	public <?= $model ?>WebService ()
	{}
	
	public String list (long time)
	{
		try
		{
			response = WebServiceHelper.singleton ().get ("/<?= $section->getName () ?>/list/" + time);
			
			return HttpHelper.getResponseContentString (response);
		}
		catch (TechnicalException e)
		{
			throw e;
		}
		catch (Exception e)
		{
			throw new TechnicalException ("Problemas técnicos na autenticação!", e);
		}
	}
	
	public static String active ()
	{
		try
		{
			return HttpHelper.getResponseContentString (WebServiceHelper.singleton ().get ("/<?= $section->getName () ?>/active"));
		}
		catch (TechnicalException e)
		{
			throw e;
		}
		catch (Exception e)
		{
			throw new TechnicalException ("Problemas técnicos na autenticação!", e);
		}
	}
	
	public long getServerTime ()
	{
		return WebServiceHelper.singleton ().getServerTime (response);
	}
}
<?
return ob_get_clean ();
?>