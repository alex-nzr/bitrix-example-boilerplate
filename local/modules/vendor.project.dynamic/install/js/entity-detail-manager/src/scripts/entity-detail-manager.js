/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * gpnsm - ui-detail.js
 * 20.02.2023 21:46
 * ==================================================
 */
import { Type, Dom, Tag, Loc } from "main.core";
import { StageFlow } from 'ui.stageflow';

export default class EntityDetailManager
{
	constructor(options = {}) {
		this.moduleId 			 	 	 = options.moduleId;
		this.typeId 			 	 	 = options.typeId;
		this.entityTypeId 		 	 	 = options.entityTypeId;
		this.entityId 			 	 	 = options.entityId;
		this.isNew				 	 	 = options.isNew;
		this.pageTitleEditable 		 	 = options.pageTitleEditable;
		this.enableCategorySelector   	 = options.enableCategorySelector;
		this.cardConfigEditable 	 	 = options.cardConfigEditable;
		this.enableSectionCreation 	 	 = options.enableSectionCreation;
		this.enableSectionEdit 		 	 = options.enableSectionEdit;
		this.enableFieldsContextMenu 	 = options.enableFieldsContextMenu;
		this.enableSectionEditMode 	 	 = options.enableSectionEditMode;
		this.showEmptySections 		 	 = options.showEmptySections;
		this.enableCommunicationControls = options.enableCommunicationControls;
		this.hideTimelineInCreationPage  = options.hideTimelineInCreationPage;
		this.isStageFlowActive			 = options.isStageFlowActive;

		this.init();
	}

	init() {
		this.customizeStageFlow();
		this.customizePageTitleBlock();
		this.addCssClasses();
		this.initEvents();
	}

	initEvents(){
		BX.ready(() => {

			BX.addCustomEvent('BX.Crm.EntityEditor:onBeforeLayout', (editor) => {
				if (!this.enableCommunicationControls)
				{
					editor._enableCommunicationControls = false;
				}

				if (!this.cardConfigEditable)
				{
					if (editor._config)
					{
						editor._enableConfigControl = false;
						editor._config._canUpdateCommonConfiguration = false;
						editor._config._canUpdatePersonalConfiguration = false;
						editor._config._enableScopeToggle = false;
					}
				}

				if (!this.enableSectionCreation)
				{
					editor._enableSectionCreation = false;
				}

				if (!this.cardConfigEditable && !this.enableSectionCreation)
				{
					editor._enableBottomPanel = false;
				}

				if (!this.enableSectionEdit)
				{
					editor._enableSectionEdit = false;
				}

				if (!this.enableFieldsContextMenu)
				{
					editor._enableFieldsContextMenu = false;
				}

				if (!this.pageTitleEditable)
				{
					editor._enablePageTitleControls = false;
					editor._model && (editor._model.isCaptionEditable = () => false);
				}

				if (editor.getMode() === BX.UI.EntityEditorMode.edit)
				{
					//setTimeout(() => editor.cancel(), 5000);
				}

				editor.saveScheme();
			});

			BX.addCustomEvent('BX.Crm.EntityEditorSection:onLayout', (section) => {
				if (section.getMode() === BX.UI.EntityEditorMode.edit)
				{
					//section.toggleMode();
				}

				if (!this.enableSectionEditMode)
				{
					BX.type.isDomNode(section._titleActions) ? section._titleActions.remove() : void(0);

					section._schemeElement._isEditable = false;
					section.saveScheme();
				}

				if (!this.showEmptySections)
				{
					const fields = section.getChildren();
					if (fields.length <= 0)
					{
						if(BX.type.isDomNode(section._wrapper))
						{
							section._wrapper.classList.add('ui-element-hidden');
						}
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

			//BX.Crm.EntityEditorColumn
			//BX.Crm.EntityEditorSection
			//BX.Crm.EntityEditor.getDefault()._enableModeToggle = false;
			//BX.UI.EntityEditorMode = {
			//    "intermediate": 0,
			//    "edit": 1,
			//    "view": 2,
			//    "names": {
			//        "view": "view",
			//        "edit": "edit"
			//    }
			//};
		});
	}

	addCssClasses() {
		if (this.isNew)
		{
			document.body.classList.add('editor-mode-new-item-creation');
			if (this.hideTimelineInCreationPage)
			{
				document.body.classList.add('editor-mode-hide-timeline-column');
			}
		}
	}

	customizePageTitleBlock(){
		if (!BX.Crm?.ItemDetailsComponent)
		{
			console.log('BX.Crm.ItemDetailsComponent is undefined')
			return;
		}

		const entityDetailManager = this;

		BX.Crm.ItemDetailsComponent.prototype.initPageTitleButtons = function()
		{
			const pageTitleButtons = Tag.render`
				<span id="pagetitle_btn_wrapper" class="pagetitile-button-container">
					<span id="page_url_copy_btn" class="crm-page-link-btn"></span>
				</span>
			`;

			if (entityDetailManager.pageTitleEditable)
			{
				const editButton = Tag.render`
					<span id="pagetitle_edit" class="pagetitle-edit-button"></span>
				`;
				Dom.prepend(editButton, pageTitleButtons);
			}

			const pageTitle = document.getElementById('pagetitle');
			Dom.insertAfter(pageTitleButtons, pageTitle);

			if (entityDetailManager.enableCategorySelector)
			{
				if(Type.isArray(this.categories) && this.categories.length > 0)
				{
					const currentCategory = this.getCurrentCategory();
					if(currentCategory)
					{
						const categoriesSelector = Tag.render`
							<div id="pagetitle_sub" class="pagetitle-sub">
								<a href="#" onclick="${this.onCategorySelectorClick.bind(this)}">${currentCategory.text}</a>
							</div>
						`;

						Dom.insertAfter(categoriesSelector, pageTitleButtons);
					}
				}
			}
		}
	}

	customizeStageFlow() {
		if (!BX.Crm?.ItemDetailsComponent)
		{
			console.log('BX.Crm.ItemDetailsComponent is undefined')
			return;
		}

		const entityDetailManager = this;

		BX.Crm.ItemDetailsComponent.prototype.initStageFlow = function()
		{
			const BACKGROUND_COLOR = 'd3d7dc';
			if(this.stages)
			{
				const flowStagesData = this.prepareStageFlowStagesData();
				const stageFlowContainer = document.querySelector('[data-role="stageflow-wrap"]');
				if(stageFlowContainer)
				{
					if (!entityDetailManager.isStageFlowActive)
					{
						stageFlowContainer.classList.add('ui-element-disabled');
					}

					this.stageflowChart = new StageFlow.Chart({
						backgroundColor: BACKGROUND_COLOR,
						currentStage: this.currentStageId,
						isActive: entityDetailManager.isStageFlowActive,
						onStageChange: this.onStageChange.bind(this),
						labels: {
							finalStageName: Loc.getMessage('CRM_ITEM_DETAIL_STAGEFLOW_FINAL_STAGE_NAME'),
							finalStagePopupTitle: Loc.getMessage('CRM_ITEM_DETAIL_STAGEFLOW_FINAL_STAGE_POPUP'),
							finalStagePopupFail: Loc.getMessage('CRM_ITEM_DETAIL_STAGEFLOW_FINAL_STAGE_POPUP_FAIL'),
							finalStageSelectorTitle: Loc.getMessage('CRM_ITEM_DETAIL_STAGEFLOW_FINAL_STAGE_SELECTOR'),
						},
					}, flowStagesData);
					stageFlowContainer.appendChild(this.stageflowChart.render());
				}
			}
		}
	}
}