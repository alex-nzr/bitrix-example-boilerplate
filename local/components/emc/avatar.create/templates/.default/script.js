"use strict";

BX.ready(function() {
	BX.namespace("EMC.Avatar.Create");

	const Creator = BX.EMC.Avatar.Create;

	Creator.init = (lastAction = false, useAjax = false, ajaxUrl = false) => {

		Creator.Data = {
			isAjax: useAjax,
			ajaxURL: ajaxUrl,
			maxFileSize: 5*1024*1024,
		};

		switch (lastAction) {
			case false:
				const uploadForm = BX("avatar-upload-form");
				const uploadInput = BX("avatar-preview-upload-file-input");

				if (uploadForm && uploadInput)
				{
					return Creator.uploadFormInit(uploadForm, uploadInput);
				}

				break;
			case "upload":
				if (!Creator.Data.isAjax) 
				{	
					const popup = Creator.createCropPopup();
					popup.show();
					return Creator.InitJcrop();
				}
				break;
			case "crop":
				if (!Creator.Data.isAjax) 
				{	
					const filterForm = BX("avatar-filters-form");
					return Creator.filterFormInit(filterForm);
				}
				break;
			case "apply-filter":
				if (!Creator.Data.isAjax) 
				{	
					const filterForm = BX("avatar-filters-form");
					return Creator.filterFormInit(filterForm);
				}
				break;
			default:
				// statements_def
				break;
		}
	}

	Creator.uploadFormInit = (uploadForm, uploadInput) => {
		
		const maxSizeInput = BX.findChild(uploadForm, {attribute: {"name": "MAX_FILE_SIZE"}}, false);
		//const action = BX.findChild(uploadForm, {attribute: {"name": "action"}}, false);

		if (BX.type.isDomNode(maxSizeInput) && BX.type.isNumber(Number(maxSizeInput.value)))
		{
			Creator.Data.maxFileSize = Number(maxSizeInput.value) * 1024 * 1024;
		}

		Creator.Data.uploadForm = uploadForm;
		Creator.Data.uploadInput = uploadInput;
		BX.bind(uploadInput, "change", ()=>{
			return Creator.uploadFile(uploadInput.files);
		});

		if (Creator.Data.isAjax) 
		{
			return Creator.dropZoneInit(uploadForm, uploadInput);
		}
	}

	Creator.dropZoneInit = (form, fileInput) => {
		const dropZone = BX.findChild(form, {attribute: {"for": fileInput.id}}, false);
		if(dropZone)
		{
			dropZone.ondrag = ()=>false;
			dropZone.ondragstart = ()=>false;
			dropZone.ondragend = ()=>false;
			dropZone.ondragover = ()=>false;
			dropZone.ondragenter = ()=>false;
			dropZone.ondragleave = ()=>false;
			dropZone.ondrop = ()=>false;

			BX.bind(dropZone, "dragenter", ()=>{
				BX.addClass(dropZone, "dragover");
			});
			BX.bind(dropZone, "dragover", ()=>{
				BX.addClass(dropZone, "dragover");
			});
			BX.bind(dropZone, "dragleave", ()=>{
				BX.removeClass(dropZone, "dragover");
			});
			BX.bind(dropZone, "drop", (e)=>{
				BX.removeClass(dropZone, "dragover");
				let files = e.dataTransfer.files;
				return Creator.uploadFile(files);
			});
		}
	}

	Creator.uploadFile = (files) => {
		let size = files[0].size;
		let type = files[0].type;

		if(size <= Creator.Data.maxFileSize)
		{
			if((type === 'image/png') || (type === 'image/jpeg'))
			{
				if (BX.type.isDomNode(BX("bx-avatar-error"))) {
					BX.remove(BX("bx-avatar-error"));
				}
				
				if (Creator.Data.isAjax) 
				{	
					const formData = new FormData(Creator.Data.uploadForm);
					formData.set("avatar-upload-file", files[0]);
					return Creator.ajaxCall(formData);
				}
				else
				{
					return Creator.Data.uploadForm.submit();
				}
			}
			else
			{
				const errorText = BX.messages.INVALID_FILE_FORMAT;

				return Creator.createErrorNode(errorText);
			}
		}
		else
		{
			const errorText = BX.messages.MAX_FILE_SIZE;

			return Creator.createErrorNode(errorText);
		}
	}

	Creator.ajaxCall = (formData) => {
		
		const bxFormData = new BX.ajax.FormData();

		for(let [name, value] of formData) 
		{
			bxFormData.append(name, value);
		}

		BX("loader-screen").classList.add("active");
		document.body.classList.add("hide-popup");		

		bxFormData.send(
			Creator.Data.ajaxURL,
			function(arResult){
				return Creator.arResultProcessing(JSON.parse(arResult));
			},
			null,
			function(error){
				console.log(`error: ${error}`);
			}
		);
	}

	Creator.arResultProcessing = (arResult) => {
		if (arResult.ERRORS.length > 0) 
		{
			let errorText = "";
			arResult.ERRORS.forEach((error) => {
				errorText+=error;
			});

			return Creator.createErrorNode(errorText);
		}
		else
		{
			let previewImg = BX("avatar-preview-image");

			if (!BX.type.isDomNode(previewImg))
			{
				previewImg = BX.create('img', {
				    attrs: {
				        className: 'avatar-preview-image',
				        id: "avatar-preview-image",
				    },
				});

				BX.append(previewImg, BX('avatar-preview-left-block'));
			}
			
			const img = new Image();
			img.onload = function(){
			    previewImg.src = arResult.PREVIEW_PICTURE_SRC;
			    BX("loader-screen").classList.remove("active");
			    document.body.classList.remove("hide-popup");
			};
			img.onerror = function(){
			    //window.location.reload();
			};
			img.src = arResult.PREVIEW_PICTURE_SRC;
			
			const filterForm = BX("avatar-filters-form");
			const cleanSrc = BX("clean_src");
			const filteredSrc = BX("filtered_src");

			switch (arResult.LAST_ACTION) {
				
				case "upload":
					BX.remove(Creator.Data.uploadForm);
					BX.remove(BX("avatar-preview-left-hint"));

					const popup = Creator.createCropPopup();
					popup.show();

					return Creator.createCoordsForm(arResult.PREVIEW_PICTURE_SRC);

				case "crop":
					BX.remove(BX("avatar-jcrop-coords"));

					if (filterForm && cleanSrc && filteredSrc) 
					{
						cleanSrc.value = arResult.CLEAN_PICTURE_SRC;
						filteredSrc.value = arResult.PREVIEW_PICTURE_SRC;

						return Creator.filterFormInit(filterForm);
					}

					break;
				case "apply-filter":
					if (filterForm && cleanSrc && filteredSrc) 
					{
						cleanSrc.value = arResult.CLEAN_PICTURE_SRC;
						filteredSrc.value = arResult.PREVIEW_PICTURE_SRC;

						if (BX.type.isDomNode(BX("avatar-download")))
						{
							BX("avatar-download").href = arResult.PREVIEW_PICTURE_SRC;
						}
						else
						{
							let fileName = ((arResult.PREVIEW_PICTURE_SRC).split("/")).pop();
							BX.append(BX.create('a', {
							    attrs: {
							        id: "avatar-download",
							        class: "avatar-btn avatar-btn-download",
							        href: arResult.PREVIEW_PICTURE_SRC,
							    },
							    props: {
							    	download: fileName,
							    },
							    text: BX.messages.DOWNLOAD_BTN_TEXT,
							}), BX("avatar-preview-right-block"));
						}

						return Creator.filterFormInit(filterForm);
					}
					break;
				default:
					// statements_def
					break;
			}
		}
	}

	Creator.createCropPopup = () => {

		const popupID = "avatar-preview-to-crop";
		const img = BX('avatar-preview-image');

		const popup = BX.PopupWindowManager.create(popupID, null, {
		    offsetTop: 0,
		    content: img,
		    offsetLeft: 0,
		    lightShadow: true,
		    closeIcon: false,
		    closeByEsc: false,
		    titleBar: {
		    	content: BX.create("span", {
		    		text: BX.messages.CROP_WINDOW_TEXT, 
		    		'props': {
		    			'className': 'avatar-preview-to-crop-title-bar'
		    		}
		    	}),
		    },
		    overlay: {
		        backgroundColor: 'rgba(61,94,150,0.8)',
		    },
		    buttons: [
		    	new BX.PopupWindowButton({
		    	    text: BX.messages.CROP_BTN_TEXT,
		    	    className: "avatar-btn avatar-btn-submit",
		    	    id: "avatar-jcrop-btn-submit",
		    	    events: {
		    	    	click: function(){
		    	    		const jcropForm = BX("avatar-jcrop-coords");
		    	    		if (jcropForm) 
		    	    		{
		    	    			Creator.updateCoords(Creator.Data.jcropWidget.pos, img);

		    	    			if (Creator.Data.isAjax) 
		    	    			{
		    	    				popup.close();
		    	    				Creator.Data.jcropStage.destroy();
		    	    				BX.remove(BX("avatar-preview-image"))

		    	    				setTimeout(()=>{
		    	    					BX.remove(BX(popupID));
		    	    				}, 500);

		    	    				const formData = new FormData(jcropForm);
		    	    				return Creator.ajaxCall(formData);
		    	    			}
		    	    			else
		    	    			{
		    	    				return BX.submit(jcropForm);
		    	    			}
		    	    		}
		    	     	}
		    	    }
		    	}),
		    ]
		});

		return popup;
	}

	Creator.createCoordsForm = (imageSrc) => {

		BX.append(BX.create('form', {
		    attrs: {
		        id: "avatar-jcrop-coords"
		    },
		    children: [
		    	BX.create("input", {
		    	    attrs: {
		    	        type: "hidden",
		    	        name: "x",
		    	        id: "x-coords",
		    	        value: "0",
		    	    },
		    	}),
		    	BX.create("input", {
		    	    attrs: {
		    	        type: "hidden",
		    	        name: "y",
		    	        id: "y-coords",
		    	        value: "0",
		    	    },
		    	}),
		    	BX.create("input", {
		    	    attrs: {
		    	        type: "hidden",
		    	        name: "w",
		    	        id: "width",
		    	        value: "200",
		    	    },
		    	}),
		    	BX.create("input", {
		    	    attrs: {
		    	        type: "hidden",
		    	        name: "h",
		    	        id: "height",
		    	        value: "200",
		    	    },
		    	}),
		    	BX.create("input", {
		    	    attrs: {
		    	        type: "hidden",
		    	        name: "src",
		    	        value: imageSrc,
		    	    },
		    	}),
		    	BX.create("input", {
		    	    attrs: {
		    	        type: "hidden",
		    	        name: "action",
		    	        value: "crop",
		    	    },
		    	}),
		    ],
		}), BX("avatar-create-wrapper"));

		return Creator.InitJcrop();
	}

	Creator.InitJcrop = ()=>{
		const img = BX("avatar-preview-image");
		if (img) 
		{
			const coordsForm = BX("avatar-jcrop-coords");
			if (coordsForm) 
			{	
				const Data = Creator.Data;

				Data.Coords = {
					x: BX("x-coords"),
					y: BX("y-coords"),
					w: BX("width"),
					h: BX("height"),
				}
				
				if (!Data.jcropStage) 
				{
					Data.jcropStage = Jcrop.attach('avatar-preview-image', {
		  				aspectRatio: 1/1,
					});

					if (!Data.jcropWidget)
					{
					  	const rect = Jcrop.Rect.create(0,0,200,200);
						const rectOptions = {};
						
						Data.jcropWidget = Data.jcropStage.newWidget(rect,rectOptions);

						Data.jcropStage.listen('crop.change',(widget,e) => {
						  	const pos = widget.pos;
						  	return Creator.updateCoords(pos, img);
						});

					}
				}
			}
		}
	}

	Creator.updateCoords = (pos, img) => {
		let realWidth = img.naturalWidth;
		let realHeight = img.naturalHeight;
		let clientWidth = img.clientWidth;
		let clientHeight = img.clientHeight;
		let coefficientX = 1;
		let coefficientY = 1;

		if ((realWidth!==clientWidth || realHeight !== clientHeight) && clientHeight>0 && clientWidth>0) 
		{
			coefficientX = (Number(realWidth)/Number(clientWidth)).toFixed(2);
			coefficientY = (Number(realHeight)/Number(clientHeight)).toFixed(2);
		}

		const resX = (Number(Math.round(pos.x))*Number(coefficientX)).toFixed(0);
		const resY = (Number(Math.round(pos.y))*Number(coefficientY)).toFixed(0);
		const resW = (Number(Math.round(pos.w))*Number(coefficientX)).toFixed(0);
		const resH = (Number(Math.round(pos.h))*Number(coefficientY)).toFixed(0);
	    Creator.Data.Coords.x.value = resX;
	    Creator.Data.Coords.y.value = resY;
	    Creator.Data.Coords.w.value = resW;
	    Creator.Data.Coords.h.value = resH;
	};

	Creator.filterFormInit = (filterForm) => {
		
		const filters = BX.findChildren(filterForm, {tag : "input", type: "radio", name : "filter"}, true);

		if (BX.type.isDomNode(filterForm) && filters.length)
		{
			
			if (!Creator.Data.filterForm) 
			{
				filters.forEach((filter) => {
					BX.bind(filter, "change", Creator.applyFilter)
				});
			}
			
			Creator.Data.filterForm = filterForm;
		}
	}

	Creator.applyFilter = (e) => {

		if (Creator.Data.isAjax) 
		{	
			const formData = new FormData(Creator.Data.filterForm);
			
			return Creator.ajaxCall(formData);
		}
		else
		{
			return Creator.Data.filterForm.submit();
		}
	}

	Creator.createErrorNode = (errorText) => {
		
		if (!BX.type.isDomNode(BX("avatar-errors"))) 
		{
			BX.prepend(BX.create('div', {
			    attrs: {
			        id: "avatar-errors"
			    },
			}), BX("avatar-create-wrapper"));
		}

		if (BX.type.isDomNode(BX("bx-avatar-error"))) 
		{
			BX("bx-avatar-error").textContent = errorText;
		}
		else
		{
			BX.append(BX.create('p', {
			    attrs: {
			        className: 'avatar-errors-error',
			        id: "bx-avatar-error"
			    },
			    text: errorText,
			}), BX("avatar-errors"));
		}
	}
});