import {ISelectors, ISettings, ITextObject} from "../types/settings";

const getProjectSettings = (externalSettings: ISettings, selectors: ISelectors): ISettings => {
    const settings: ISettings =  {...externalSettings, ...selectors}

    if (settings.selectDoctorBeforeService === "Y"){
        settings["selectionBlocks"] = {
            "clinicsBlock": settings.selectionBlocks.clinicsBlock,
            "specialtiesBlock": settings.selectionBlocks.specialtiesBlock,
            "employeesBlock": settings.selectionBlocks.employeesBlock,
            "servicesBlock": settings.selectionBlocks.servicesBlock,
            "scheduleBlock": settings.selectionBlocks.scheduleBlock,
        };
    }

    for (let key in settings.selectionBlocks) {
        if (settings.selectionBlocks.hasOwnProperty(key)){
            settings.selectionNodes[`${settings.selectionBlocks[key].id}`] = {
                "blockId":      `${settings.selectionBlocks[key].id}_block`,
                "listId":       `${settings.selectionBlocks[key].id}_list`,
                "selectedId":   `${settings.selectionBlocks[key].id}_selected`,
                "inputId":      `${settings.selectionBlocks[key].id}_value`,
                "isRequired":   !(key === "servicesBlock" && settings.useServices !== "Y")
            }
            settings.defaultText[settings.selectionBlocks[key].id] = `${settings.selectionBlocks[key].name}`;
        }
    }

    settings.textBlocks.forEach((attrs:ITextObject) => {
        if (attrs["name"] !== undefined){
            settings.textNodes[attrs["name"]] = {
                "inputId": `${attrs["id"]}`,
                "isRequired": (attrs["data-required"] === "true")
            };
        }
    });

    settings.dataKeys = {
        "clinicsKey":       settings.selectionBlocks.clinicsBlock.id,
        "specialtiesKey":   settings.selectionBlocks.specialtiesBlock.id,
        "servicesKey":      settings.selectionBlocks.servicesBlock.id,
        "employeesKey":     settings.selectionBlocks.employeesBlock.id,
        "scheduleKey":      settings.selectionBlocks.scheduleBlock.id,
    }

    return settings;
}

export default getProjectSettings;