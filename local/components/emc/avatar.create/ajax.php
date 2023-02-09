<?
namespace EMC\Avatar;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

\CBitrixComponent::includeComponentClass('emc:avatar.create');

use \Bitrix\Main\Application;
use \Bitrix\Main\Localization\Loc;
use \EMC\Avatar\CreateAvatar;

Loc::LoadMessages(__FILE__);

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$ajaxAction = $request->getPost("action");

if (is_string($ajaxAction) && strlen($ajaxAction)>0) 
{
	$params = $request->getPostList()->toArray();
	$files = $request->getFileList()->toArray();

	$ajaxData = array_merge($params, $files);

	$creator = new CreateAvatar(null, $ajaxData);

	if ($creator->checkModules()) 
	{
		print_r(json_encode($creator->getResult()));
	}
}
else
{
	print_r(json_encode(["error"=>Loc::getMessage("EMPTY_ACTION")]));
}

/*class CreateAvatarAjaxController extends \Bitrix\Main\Engine\Controller
{
    public function getResultAction($formData)
    {
    	if (!empty($formData["action"])) 
    	{
    		$creator = new CreateAvatar(null, $formData);

    		if ($creator->checkModules()) 
    		{
    			return $creator->getResult();
    		}

    		return Loc::getMessage("AJAX_ERROR");
    	}
    	else
    	{
    		return Loc::getMessage("EMPTY_ACTION");
    	}
    }
}*/
?>