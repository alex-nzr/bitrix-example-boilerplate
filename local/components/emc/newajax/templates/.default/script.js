"use strict";

BX.ready(function() {
	const form = BX('testForm');
	form.addEventListener('submit', (e) => {
		e.preventDefault();
		const formData = new FormData(form);
		formData.append('param1', 'testParam1')
		var request = BX.ajax.runComponentAction('emc:newajax', 'test', {
		    mode:'class',
		    data: formData
		});
		 
		request.then(function(response){
		    console.log(response);
		}) 
	})

});