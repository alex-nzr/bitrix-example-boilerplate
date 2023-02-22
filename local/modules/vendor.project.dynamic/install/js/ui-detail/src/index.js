import './styles/style.css';
import {Extension} from 'main.core';
import {UiDetail} from './scripts/ui-detail';

const extName = 'vendor.project.dynamic.ui-detail';

BX.ready(() => {
    try
    {
        BX.Vendor.Project.Dynamic.UiDetail = new UiDetail(Extension.getSettings(extName));
    }
    catch (e)
    {
        console.log(`${extName} error`, e)
    }
});