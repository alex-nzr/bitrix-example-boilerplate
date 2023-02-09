<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
 
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
 
class CCustomAjax extends CBitrixComponent implements Controllerable
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'test' => [
                'prefilters' => [
                    /*new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),*/
                ],
                'postfilters' => []
            ]
        ];
    }
 
    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }
 
 
    /**
     * @param string $param2
     * @param string $param1
     * @return array
     */
    public function testAction($param2 = 'qwe', $param1 = '')
    {
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $params = $request->getPostList()->toArray();
        $files = $request->getFileList()->toArray();

        $ajaxData = array_merge($params, $files);
        $ajaxData['asd'] = $param1;
        return $ajaxData;
    }
 
}