import {ISelectors} from "../../types/settings";

export default function initAppSelectors(styles: any){
    const selectors: ISelectors = {
        wrapperId:                    'appointment-widget-wrapper',
        widgetBtnWrapId:              styles["appointment-button-wrapper"],
        widgetBtnId:                  styles['appointment-button'],
        formId:                       styles['appointment-form'],
        mobileCloseBtnId:             styles['appointment-form-close'],
        messageNodeId:                styles['appointment-form-message'],
        submitBtnId:                  styles['appointment-form-button'],
        appResultBlockId:             styles['appointment-result-block'],
        inputClass:					  styles['appointment-form_input'],
        textareaClass:				  styles['appointment-form_textarea'],
    }
    return selectors;
}