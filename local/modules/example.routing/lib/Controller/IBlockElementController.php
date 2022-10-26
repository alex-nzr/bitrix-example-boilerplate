<?php
namespace Example\Routing\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Example\Routing\Service\IBlock\IBlockElementService;

/**
 * Class IBlockElementController
 * @package Vendor\Project\Basic\Controller
 */
class IBlockElementController extends Controller
{
    public function getByIdAction($id): ?array
    {
        $res = IBlockElementService::iBlockElementGetById($id);
        if ($res['error'])
        {
            $this->addError(new Error($res['error_description'], $res['error']));
            return null;
        }
        return $res;
    }

    public function addAction(): ?array
    {
        $res = IBlockElementService::iBlockElementAdd($this->request);
        if (array_key_exists('error', $res))
        {
            $this->addError(new Error($res['error_description'], $res['error']));
            return null;
        }
        return $res;
    }

    public function updateAction(): ?array
    {
        $res = IBlockElementService::iBlockElementUpdate($this->request);
        if (array_key_exists('error', $res))
        {
            $this->addError(new Error($res['error_description'], $res['error']));
            return null;
        }
        return $res;
    }

    public function deleteAction($id): ?array
    {
        $res = IBlockElementService::iBlockElementDelete($id);
        if (array_key_exists('error', $res))
        {
            $this->addError(new Error($res['error_description'], $res['error']));
            return null;
        }
        return $res;
    }

    public function testAction($sid, $id): ?array
    {
        return [
            'section_id' => $sid,
            'element_id' => $id,
        ];
    }

    public function configureActions(): array
    {
        return [
            'getById'   => [ 'prefilters' => [], 'postfilters' => [] ],
            'add'       => [ 'prefilters' => [], 'postfilters' => [] ],
            'update'    => [ 'prefilters' => [], 'postfilters' => [] ],
            'delete'    => [ 'prefilters' => [], 'postfilters' => [] ],
            'test'    => [ 'prefilters' => [], 'postfilters' => [] ]
        ];
    }
}