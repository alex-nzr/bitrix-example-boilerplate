import styles from "../styles/app.scss";

export interface IDefTextObject{
    [key: string]: string
}

export interface ITextObject {
    [key: string]: string | undefined,
}

export interface IDataKeys {
    clinicsKey:     string,
    specialtiesKey: string,
    servicesKey:    string,
    employeesKey:   string,
    scheduleKey:    string,
}

export interface ISelectionObjectParams{
    id: string,
    name: string
}

/*export enum ESelectionBlocks{
    clinicsBlock = "clinicsBlock",
    specialtiesBlock = "specialtiesBlock",
    employeesBlock = "employeesBlock",
    servicesBlock = "servicesBlock",
    scheduleBlock = "scheduleBlock",
}*/

export type ISelectionObject = {
    [key: string]: ISelectionObjectParams;
};

export interface SelectionNode{
    blockId: string,
    listId: string,
    selectedId: string,
    inputId: string,
    isRequired: boolean
}
export interface ITextNode{
    inputId: string,
    isRequired: boolean
}

export interface ISelectionNodes {
    [key: string]: SelectionNode
}
export interface ITextNodes {
    [key: string]: ITextNode
}

export interface ISelectors{
    wrapperId: string,
    widgetBtnWrapId: string,
    widgetBtnId: string,
    formId:  string,
    mobileCloseBtnId: string,
    messageNodeId:   string,
    submitBtnId:   string,
    appResultBlockId: string,
    inputClass:	 string,
    textareaClass:	 string,
}

export interface IConfirmTypes{
    phone: string,
    email: string,
    none: string,
}

export interface ISettings {
    customColors: {};
    useCustomMainBtn: string;
    customMainBtnId: string;
    ajaxUrl: string;
    useServices: string,
    selectDoctorBeforeService: string,
    useTimeSteps: string,
    strictCheckingOfRelations: string,
    showDoctorsWithoutDepartment: string,
    timeStepDurationMinutes: number,
    useConfirmWith: string;
    confirmTypes: IConfirmTypes;
    useEmailNote: string;
    privacyPageLink: string,
    wrapperId: string,
    widgetBtnWrapId: string,
    widgetBtnId: string,
    formId: string,
    messageNodeId: string,
    submitBtnId: string,
    appResultBlockId: string,
    inputClass:	 string,
    textareaClass:	 string,
    dataKeys: IDataKeys,
    selectionBlocks: ISelectionObject,
    selectionNodes: ISelectionNodes,
    textBlocks: Array<ITextObject>,
    textNodes: ITextNodes,
    defaultText: IDefTextObject,
    isUpdate: boolean,
}