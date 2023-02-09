<?php
namespace EMC\Avatar;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Path;
use Bitrix\Main\IO\Directory as Dir;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use CBitrixComponent;
use CFile;
use CJSCore;
use Exception;
use Imagick;

Loc::LoadMessages(__FILE__);
CJSCore::Init(["popup"]);

class CreateAvatar extends CBitrixComponent
{

    private static ?string $root;
    protected static bool $useAjax = false;
    protected static array $ajaxData = [];
    protected static array $errors = [];
    protected static string $action = '';

    //folders
    protected static string $imagesToResizeFolderName = "images_to_resize";
    protected static string $imagesResizedFolderName = "images_resized";
    protected static string $imagesCroppedFolderName = "images_cropped";
    protected static string $componentStorageFolderName = "avatar";
    protected static string $componentImagesStorage;
    protected static string $originalImagesDir;
    protected static string $resizedImagesDir;
    protected static string $croppedImgDir;
    
    protected static string $fileInputName = "avatarUploadFile";
    protected static int $maxFileSize = 5*1024*1024;
    
    public function __construct($component = null, $ajaxData = false)
    {
        parent::__construct($component);
        $this->fillComponentData($ajaxData);
    }

    protected function fillComponentData($ajaxData){
        self::$root = Application::getDocumentRoot();
        $this->request = Application::getInstance()->getContext()->getRequest();

        $mainUploadDir = Option::get(
            'main',
            'upload_dir',
            "upload",
            SITE_ID
        );

        self::$componentImagesStorage = "/".$mainUploadDir."/".self::$componentStorageFolderName."/";
        self::$originalImagesDir = self::$componentStorageFolderName."/".self::$imagesToResizeFolderName;
        self::$resizedImagesDir = "/".$mainUploadDir."/".self::$componentStorageFolderName."/".self::$imagesResizedFolderName."/";
        self::$croppedImgDir = "/".$mainUploadDir."/".self::$componentStorageFolderName."/".self::$imagesCroppedFolderName."/";

        if (is_string($ajaxData["action"]) && strlen($ajaxData["action"])>0)
        {
            self::$action = $ajaxData["action"];
            self::$ajaxData = $ajaxData;
            self::$useAjax = true;
        }
        else
        {
            self::$action = (string)$this->request->getPost("action");
        }
    }

    /**
     * Event called from includeComponent before component execution.
     * Takes component parameters as argument and should return it formatted as needed.
     * @param array[string]mixed $arParams
     * @return array[string]mixed
    */
    public function onPrepareComponentParams($arParams): array
    {
        if (!empty($arParams["USE_AJAX"]) && ($arParams["USE_AJAX"] === "Y")) 
        {
            self::$useAjax = true;
        }

        $max_file_size = is_int((int)$arParams["MAX_FILE_SIZE"]) ? (int)$arParams["MAX_FILE_SIZE"] : 5;

        self::$maxFileSize = $max_file_size*1024*1024;//convert to bytes
        
        $result = array(
            "CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? "A",
            "CACHE_TIME" => $arParams["CACHE_TIME"] ?? 3600,
            "MAX_FILE_SIZE" => self::$maxFileSize,
            "DEFAULT_IMG" => is_file($arParams["DEFAULT_IMG"]) ? $arParams["DEFAULT_IMG"] : $this->GetPath()."/images/default-bg.png",
        );

        $watermarks = [];
        $keyMask = 'WATERMARK_';
        
        foreach($arParams as $key => $value)
        {
            if (
                strpos($key, $keyMask) !== false
                && strpos($key, "~") === false
                && $value > ''
            ){
                $watermarks[] = $value;
            }
        }

        if (count($watermarks) === 0 || $arParams["SAVE_DEFAULT_FILTERS"] === "Y") 
        {
            $defaultWatermarks = [
                $this->GetPath() . "/images/watermarks/01.png",
                $this->GetPath() . "/images/watermarks/02.png",
                $this->GetPath() . "/images/watermarks/03.png",
            ];
            $watermarks = array_merge($watermarks,$defaultWatermarks);
        }

        $result["WATERMARKS"] = $watermarks;

        return $result;
    }

    /**
     * @throws Exception
     */
    public function executeComponent()
    {
        if (self::$action) {
            $this->ClearResultCache(self::$action);
        }

        if ($this->startResultCache($this->arParams['CACHE_TIME'], self::$action)) 
        {
            $this->deleteOldFiles(self::$root.self::$componentImagesStorage);
            $this->checkModules();
            $this->arResult = $this->getResult();
            $this->includeComponentTemplate();
            $this->endResultCache();
        }
    }

    /**
     * preparing $arResult
     * @return array[string]mixed
     * @throws Exception
     */
    public function getResult(): array
    {
        $previewImgParams = $this->startImageProcessing();
        $arResult = [
            "ERRORS" => self::$errors,
            "CROPPED" => false,
            "FILTERED" => false,
            "WATERMARKS" => $this->arParams["WATERMARKS"],
            "MAX_FILE_SIZE" => self::$maxFileSize,
            "USE_AJAX" => self::$useAjax,
            "FILE_INPUT_NAME" => self::$fileInputName,
        ];

        return array_merge($arResult, $previewImgParams);
    }

    /**
     * Check needle modules for this component
     * @return bool
     */
    public function checkModules(): bool
    {
        try 
        {
            if (!Loader::includeModule('main')) 
            {
                throw new Exception(Loc::getMessage("LOADER_ERROR"));
            }

            return true;  
        } 
        catch (Exception $e)
        {
            $this->AbortResultCache();
            self::$errors[] = $e->getMessage();
            return false;
        }  
    }

    /**
     * Start processing of upload/crop/filter images and returns array of result image params
     * @return array[string]mixed
     * @throws Exception
     */
    protected function startImageProcessing(): array
    {
        return $this->checkRequest();
    }

    /**
     * Check request parameters and call relevant method
     * returns array of result image params
     * @return array[string]mixed
     * @throws Exception
     */
    protected function checkRequest(): array
    {
        $defaultResult = [
            "PREVIEW_PICTURE_SRC" => $this->arParams["DEFAULT_IMG"],
            "SHOW_DEFAULT" => true,
        ];

        try 
        {
            switch (self::$action) {
                case 'upload':
                    $file = $this->getRequestParam(self::$fileInputName);
                    $result = $this->uploadImage($file);
                    break;

                case 'crop':
                    if(!Dir::isDirectoryExists(self::$root.self::$croppedImgDir)) 
                    {
                        Dir::createDirectory(self::$root.self::$croppedImgDir);
                    }

                    $x = $this->getRequestParam("x");
                    $y = $this->getRequestParam("y");
                    $w = $this->getRequestParam("w");
                    $h = $this->getRequestParam("h");
                    $src = $this->getRequestParam("src");

                    $result = $this->cropImage((int)$x, (int)$y, (int)$w, (int)$h, $src);
                    break;

                case 'apply-filter':
                    $src = (string)$this->getRequestParam("src");
                    $filtPicSrc = (string)$this->getRequestParam("filt_src");
                    $filter = $this->getRequestParam("filter");

                    $result = $this->addWatermark($src, $filtPicSrc, $filter);
                    break;

                default:
                    $result = $defaultResult;
                    break;
            }

            if (!empty(self::$action)) 
            {
                $result["LAST_ACTION"] = self::$action;
            }
        } 
        catch (SystemException $e) 
        {
            $this->AbortResultCache();
            self::$errors[] = $e->getMessage();
            $result = $defaultResult;
        }

        return $result;
    }

    /**
     * Get request parameters from POST or ajaxData and call relevant method
     * @param string
     * @return array[string]mixed
     */
    protected function getRequestParam(string $param_name)
    {
        if (self::$useAjax && is_array(self::$ajaxData)) 
        {
            return self::$ajaxData[$param_name];
        }
        else
        {
            if ($param_name === self::$fileInputName) 
            {
                return $this->request->getFile($param_name);
            }

            return $this->request->getPost($param_name);
        }
    }

    /**
     * Upload image to filesystem and resize it if needle
     * @param array 
     * @return array[string]mixed
     * @throws SystemException
    */
    protected function uploadImage($file): array
    {
        if (is_array($file)) 
        {
            if ($file["size"] <= self::$maxFileSize) 
            {
                $extensionArr = explode(".", $file["name"]);
                $extension = $extensionArr[count($extensionArr)-1];
                $newName = uniqid().".".$extension;

                $arrFile = array_merge(
                    $file, 
                    array("name"=>$newName, "del" => ${"avatar-upload-file_del"}, "MODULE_ID" => "")
                );

                $fid = CFile::SaveFile($arrFile, self::$originalImagesDir, false, false, false, false);
                
                if (intval($fid)>0)
                {
                    $uploadImgParams = CFile::GetFileArray($fid);

                    $img_src_path = $uploadImgParams['SRC'];
                    $img_root_path = self::$root . $img_src_path;    
                    $thumb_src_path = self::$resizedImagesDir . $arrFile['name'];
                    $thumb_root_path = self::$root . $thumb_src_path;
                    
                    if(is_file($img_root_path) && !is_file($thumb_root_path))
                    {
                        $width = $uploadImgParams['WIDTH'];
                        $height = $uploadImgParams['HEIGHT'];

                        if($uploadImgParams['HEIGHT']>1600 || $uploadImgParams['WIDTH']>1600)
                        {
                            $width = $height = 1600;
                        }

                        $resizedImage = CFile::ResizeImageFile(
                            $img_root_path, 
                            $thumb_root_path, 
                            array('width'=>$width,'height'=>$height), 
                            BX_RESIZE_IMAGE_PROPORTIONAL
                        );

                        CFile::Delete($fid);

                        if ($resizedImage) 
                        {
                            $img_src_path = $thumb_src_path;
                        }
                        else
                        {
                            throw new SystemException(Loc::getMessage("PROCESS_ERROR"));
                        }
                    }

                    return array(
                        "PREVIEW_PICTURE_SRC" => $img_src_path,
                        "SHOW_DEFAULT" => false,
                    );
                }
                else
                {
                    throw new SystemException(Loc::getMessage("SAVEFILE_ERROR"));
                }
            }
            else
            {
                throw new SystemException(Loc::getMessage("MAXFILESIZE_ERROR"));
            }
        }
        else
        {
            throw new SystemException(Loc::getMessage("READFILE_ERROR"));
        }
    }

    /**
     * Crop image and save to filesystem
     * @param int, string
     * @return array[string]mixed
     * @throws Exception
     */
    protected function cropImage($x, $y, $w, $h, $src): array
    {
        if (!is_file(self::$root.$src)) 
        {
            throw new SystemException(Loc::getMessage("IMAGE_NOT_FOUND"));
        }
        else
        {
            if ( 
                (isset($x, $y) && !empty($w) && !empty($h))
                && (is_int($x) && is_int($y) && is_int($w) && is_int($h))
            ) 
            {
                $min_width = $min_height = 500;
                
                $src = self::$root.$src;
                $imageExt = $this->getFileExtension($src);

                $image = new Imagick($src);
                
                $image_w = $image->getImageWidth();
                $image_h = $image->getImageHeight();

                if ( ($image_w < ($x+$w)) || ($image_h < ($y+$h)) ) {
                    if ($image_w < ($x+$w)) {
                       $x = $image_w - $w;
                    }
                    if ($image_h < ($y+$h)) {
                        $y = $image_h - $h;
                    }
                    //throw new SystemException(Loc::getMessage("INVALID_CROP_PARAMS"));
                }
                
                $image->cropImage($w, $h, $x, $y);
                
                if ($w<$min_width || $h<$min_height)
                {
                    $image->resizeImage($min_width, $min_height, Imagick::FILTER_UNDEFINED, 1, true);
                }

                $croppedImgName = uniqid().".".$imageExt;
                $image->writeImage(self::$root . self::$croppedImgDir . $croppedImgName);

                unlink($src);
                $image->destroy();

                return array(
                    "PREVIEW_PICTURE_SRC" => self::$croppedImgDir.$croppedImgName,
                    "CLEAN_PICTURE_SRC" => self::$croppedImgDir.$croppedImgName,
                    "SHOW_DEFAULT" => false,
                    "CROPPED" => true,
                );
            }
            else
            {
                throw new Exception(Loc::getMessage("NOT_ALL_PARAMS"));
            }
        }
    }

    /**
     * Add watermark on image and save it to filesystem
     * @param string $originalImgPath - path to clean image, 
     * @param string $waterMarkedImgPath - path to img with watermark, 
     * @param $watermarkPath - path to watermark img
     * @return array[string]mixed
     * @throws Exception
    */
    protected function addWatermark(string $originalImgPath, string $waterMarkedImgPath, $watermarkPath): array
    {
        if (!file_exists(self::$root.$watermarkPath))
        {
            throw new SystemException(Loc::getMessage("FILTER_FILE_NOT_FOUND"));
        }
        if (!file_exists(self::$root.$originalImgPath)) 
        {
            throw new SystemException(Loc::getMessage("IMAGE_NOT_FOUND"));
        }

        if ($originalImgPath !== $waterMarkedImgPath)
        {
            $waterMarkedImgRootPath = self::$root.$waterMarkedImgPath;
            if (is_file($waterMarkedImgRootPath))
            {
                unlink($waterMarkedImgRootPath);
            }
        }

        $originalImgRootPath = self::$root . $originalImgPath;
        $watermarkRootPath = self::$root . $watermarkPath;

        $originalImageExt = $this->getFileExtension($originalImgRootPath);

        $originalImage = new Imagick();
        $originalImage->readImage($originalImgRootPath);

        $watermark = new Imagick();
        $watermark->readImage($watermarkRootPath);

        $originalImageWidth = $originalImage->getImageWidth();
        $originalImageHeight = $originalImage->getImageHeight();

        $watermark->resizeImage($originalImageWidth, $originalImageHeight, Imagick::FILTER_UNDEFINED, 1, true);

        $newImgDir = self::$croppedImgDir;
        $newImgName = uniqid()."_w.".$originalImageExt;
        $newImgPath = $newImgDir.$newImgName;
        $newImgRootPath = self::$root.$newImgPath;

        $originalImage->compositeImage($watermark, Imagick::COMPOSITE_OVER, 0, 0);

        $originalImage->writeImage($newImgRootPath);
        $originalImage->destroy();
        $watermark->destroy();

        return array(
            "PREVIEW_PICTURE_SRC" => $newImgPath,
            "CLEAN_PICTURE_SRC" => $originalImgPath,
            "SHOW_DEFAULT" => false,
            "CROPPED" => true,
            "FILTERED" => true,
        );
    }

    /**
     * get File Extension
     * @param string path to file
     * @return string
    */
    protected function getFileExtension($filename): string
    {
        $fileExt = Path::getExtension($filename);

        if (!empty($fileExt)) 
        {
            return $fileExt;
        }
        else
        {
            return "png";
        }
    }

    /**
     * clean image files and dirs in component storage folder if they older then 1 hour
    */
    protected function deleteOldFiles($dir): void
    {
        if (is_dir($dir)) 
        {

            if ($dh = opendir($dir)) 
            {
                $componentFolders = [
                    self::$imagesToResizeFolderName,
                    self::$imagesResizedFolderName,
                    self::$imagesCroppedFolderName,
                ];
                while (($file = readdir($dh)) !== false) 
                {
                    if ($file != "." && $file != "..") 
                    {
                        if (filetype($dir . $file) === "dir" && in_array($file, $componentFolders)) 
                        {
                            $this->deleteOldFiles($dir . $file . "/");
                        }
                        else
                        {
                            if ((time() - filemtime($dir . $file)) > 3600) 
                            {
                                if (filetype($dir . $file) === "dir") 
                                {
                                    Dir::deleteDirectory($dir . $file);
                                }
                                else
                                {
                                    File::deleteFile($dir . $file);
                                }
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }
    }
}