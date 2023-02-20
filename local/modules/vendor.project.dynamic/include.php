<?php

use Vendor\Project\Dynamic\Config\Configuration;
use Vendor\Project\Dynamic\Internals\Control\ServiceManager;
use Vendor\Project\Dynamic\Internals\Debug\Logger;

try
{
    ServiceManager::getInstance()->includeModule();
}
catch (Throwable $e)
{
    Logger::writeToFile(
        $e->getMessage(),
        date("d.m.Y H:i:s") . ' - error on including module - ' . ServiceManager::getModuleId(),
        Configuration::getInstance()->getLogFilePath()
    );
}
if(\Bitrix\Main\Context::getCurrent()->getRequest()->get('init_mode') === 'edit')
{
    $_REQUEST['init_mode'] = '';
    \Bitrix\Main\Context::getCurrent()->getRequest()->set('init_mode', '');
}

\Bitrix\Main\Page\Asset::getInstance()->addString("
<script>
    //readonlyFields = [];
    
    BX.ready(() => {
        //return;
        if (BX.Crm?.ItemDetailsComponent)
        {
            BX.Crm.ItemDetailsComponent.prototype.initPageTitleButtons = () => false;        
        }
        
        BX.addCustomEvent('BX.Crm.EntityEditor:onBeforeLayout', (editor) => {
            editor._enableSectionCreation = false;
            editor._enableSectionEdit = false;
            editor._enableFieldsContextMenu = false;
            editor._enableBottomPanel = false;
            editor._enableConfigControl = false;
            editor._enablePageTitleControls = false;
            editor._enableCommunicationControls = false;
            
            if (editor._config)
            {
                editor._config._canUpdateCommonConfiguration = false;
                editor._config._canUpdatePersonalConfiguration = false;
                editor._config._enableScopeToggle = false;        
            }
            
            if (editor.getMode() === BX.UI.EntityEditorMode.edit)
			{
                //setTimeout(() => editor.cancel(), 5000);
            }
            
            editor._model && (editor._model.isCaptionEditable = () => false);
            BX.type.isDomNode(editor._editPageTitleButton) ? editor._editPageTitleButton.remove() : void(0);
            
            editor.saveScheme();
        });
        
        BX.addCustomEvent('BX.Crm.EntityEditorSection:onLayout', (section) => {
                if (section.getMode() === BX.UI.EntityEditorMode.edit)
                {
                      //section.toggleMode();
                }
            
                BX.type.isDomNode(section._titleActions) ? section._titleActions.remove() : void(0);
               
               section._schemeElement._isEditable = false;
               section.saveScheme();
               
               const fields = section.getChildren();
               if (fields.length <= 0)
               {
                    if(BX.type.isDomNode(section._wrapper))
                    {
                        section._wrapper.classList.add('ui-section-hidden');
                    }    
               }
               
               /*fields.forEach(field => {
                   prepareFieldParams(field);
               });*/
        }) 
        
        /*BX.addCustomEvent('BX.UI.EntityEditorField:onLayout', (field) => {
            prepareFieldParams(field);
        });
        
        function prepareFieldParams(field)
        {
            if(readonlyFields.includes(field._id))
            {
                field._schemeElement._isEditable=false;
                field.saveScheme();        
                
                const inputs = field._wrapper.querySelectorAll(`[name^='`+field._id+`'], [name^='`+field._id+`[]'], i.date.icon`);
                inputs.length && inputs.forEach(input => {
                    input.setAttribute('disabled', true);
                    input.onclick = () => false;
                });
            }
        }*/
    });
</script>
<style>.ui-section-hidden{display: none!important; opacity: 0;position: absolute;z-index: -9999;pointer-events: none;}</style>
");
//BX.Crm.EntityEditorColumn
////BX.Crm.EntityEditorSection
////BX.Crm.EntityEditor.getDefault()._enableModeToggle = false;
///*BX.UI.EntityEditorMode = {
//    "intermediate": 0,
//    "edit": 1,
//    "view": 2,
//    "names": {
//        "view": "view",
//        "edit": "edit"
//    }
//}; */