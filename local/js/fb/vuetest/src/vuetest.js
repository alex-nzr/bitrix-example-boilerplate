import {Type} from 'main.core';
import {BitrixVue} from 'ui.vue3';

export class Vuetest
{
	constructor(options = {name: 'Vuetest'})
	{
		this.name = options.name;
	}

	createApp(){
		BitrixVue.createApp({
			data()
			{
				return {
					counter: 0
				}
			},
			mounted()
			{
				setInterval(() => {
					this.counter++
				}, 1000)
			},
			// language=Vue
			template: `
        Counter: {{ counter }}
    `
		}).mount('#application-test');
	}

	setName(name)
	{
		if (Type.isString(name))
		{
			this.name = name;
		}
	}

	getName()
	{
		return this.name;
	}
}