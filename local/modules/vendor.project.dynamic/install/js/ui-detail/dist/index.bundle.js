this.BX=this.BX||{},this.BX.Vendor=this.BX.Vendor||{},this.BX.Vendor.Project=this.BX.Vendor.Project||{},function(e,t){"use strict";var i=function(){function e(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};babelHelpers.classCallCheck(this,e),t.error?console.log("Error in config.php - ".concat(t.error)):(this.moduleId=t.moduleId,this.typeId=t.typeId,this.entityTypeId=t.entityTypeId,this.entityId=t.entityId,this.isNew=t.isNew,this.isAdmin=t.isAdmin,this.init())}return babelHelpers.createClass(e,[{key:"init",value:function(){this.entityDetailManager=new BX.Vendor.Project.Dynamic.EntityDetailManager({moduleId:this.moduleId,typeId:this.typeId,entityTypeId:this.entityTypeId,entityId:this.entityId,isNew:this.isNew,pageTitleEditable:!1,enableCategorySelector:!1,cardConfigEditable:!1,enableCommunicationControls:!1,enableSectionCreation:this.isAdmin,enableSectionEdit:this.isAdmin,enableFieldsContextMenu:this.isAdmin,enableSectionEditMode:this.isAdmin,showEmptySections:!1,hideTimelineInCreationPage:!0,isStageFlowActive:this.isAdmin}),this.initEvents()}},{key:"initEvents",value:function(){}}]),e}(),n="vendor.project.dynamic.ui-detail";BX.ready((function(){try{BX.Vendor.Project.Dynamic.UiDetail=new i(t.Extension.getSettings(n))}catch(e){console.log("".concat(n," error"),e)}}))}(this.BX.Vendor.Project.Dynamic=this.BX.Vendor.Project.Dynamic||{},BX);
//# sourceMappingURL=index.bundle.js.map