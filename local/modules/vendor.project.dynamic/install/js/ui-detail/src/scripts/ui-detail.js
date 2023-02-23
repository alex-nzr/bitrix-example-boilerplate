/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * gpnsm - ui-detail.js
 * 13.07.2022 14:46
 * ==================================================
 */

export class UiDetail
{
	constructor(options = {}) {
		if (options.error)
		{
			console.log(`Error in config.php - ${options.error}`)
		}
		else
		{
			this.moduleId 			 	 = options.moduleId;
			this.typeId 			 	 = options.typeId;
			this.entityTypeId 		 	 = options.entityTypeId;
			this.entityId 			 	 = options.entityId;
			this.isNew				 	 = options.isNew;
			this.isAdmin				 = options.isAdmin;

			this.init();
		}
	}

	init() {
		this.entityDetailManager = new BX.Vendor.Project.Dynamic.EntityDetailManager({
			moduleId: 			 	     this.moduleId,
			typeId: 			 	     this.typeId,
			entityTypeId: 		 	     this.entityTypeId,
			entityId: 			 	     this.entityId,
			isNew:				 	     this.isNew,
			pageTitleEditable: 		     false,
			enableCategorySelector:      false,
			cardConfigEditable: 	     false,
			enableCommunicationControls: false,
			enableSectionCreation: 	     this.isAdmin,
			enableSectionEdit: 		     this.isAdmin,
			enableFieldsContextMenu:     this.isAdmin,
			enableSectionEditMode: 	     this.isAdmin,
			showEmptySections: 		     false,
			hideTimelineInCreationPage:  true,
			isStageFlowActive:			 this.isAdmin,
			reloadOnStageChange:		 true,
		});

		this.initEvents();
	}

	initEvents(){

	}
}