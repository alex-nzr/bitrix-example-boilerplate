import {ISelectionNodes, ISettings, ITextNodes} from "../../types/settings";
import styles from "../../styles/app.scss";
import {IClinic, IOrderParams, IScheduleItem, ITimeTableItem} from "../../types/oneCData";
import initAppSelectors from "../utils/app-selectors";
import getProjectSettings from "../../store/settings";
import buildAppointmentSkeleton from "../utils/app-html-skeleton";
import {convertHexToHsl} from "../utils/functions";

export const AppointmentPopup: any = {
    init: async function(params: ISettings)
    {
        const selectors     = initAppSelectors(styles);
        const settings      = getProjectSettings(params, selectors);
        const appSkeleton   = buildAppointmentSkeleton(styles, settings);

        const root = document.getElementById("appointment-popup-root");
        root ? (root.innerHTML = appSkeleton) : console.log("Appointment root selector not found")

        this.initParams = params;

        this.useServices 					= (params.useServices === "Y");
        this.selectDoctorBeforeService 		= (params.selectDoctorBeforeService === "Y");
        this.useTimeSteps 					= (params.useTimeSteps === "Y");
        this.timeStepDurationMinutes		= Number(params.timeStepDurationMinutes);
        this.strictCheckingOfRelations		= (params.strictCheckingOfRelations === "Y");
        this.showDoctorsWithoutDepartment	= (params.showDoctorsWithoutDepartment === "Y");
        this.confirmTypes                   = params.confirmTypes;
        this.useConfirmWith                 = (params.useConfirmWith);
        this.useEmailNote                   = (params.useEmailNote === "Y");

        this.isUpdate = params.isUpdate;

        this.ajaxUrl  = params.ajaxUrl;

        const formData = new FormData();
        formData.set('sessid', window.BX.bitrix_sessid());
        this.requestParams = {
            method: 'POST',
            body: formData,
        }
        this.dataKeys = {
            clinicsKey: params.dataKeys.clinicsKey,
            specialtiesKey: params.dataKeys.specialtiesKey,
            servicesKey: params.dataKeys.servicesKey,
            employeesKey: params.dataKeys.employeesKey,
            scheduleKey: params.dataKeys.scheduleKey,
        };
        this.data = {
            clinics: [],
            employees: {},
            services: {},
            schedule: []
        }
        this.eventHandlersAdded = {}

        this.requiredInputs = [];

        this.filledInputs = {
            [this.dataKeys.clinicsKey]: {
                clinicUid: false,
                clinicName: false,
            },
            [this.dataKeys.specialtiesKey]: {
                specialty: false,
                specialtyUid: false,
            },
            [this.dataKeys.servicesKey]: {
                serviceUid: false,
                serviceName: false,
                serviceDuration: false,
            },
            [this.dataKeys.employeesKey]: {
                refUid: false,
                doctorName: false,
            },
            [this.dataKeys.scheduleKey]: {
                orderDate: false,
                timeBegin: false,
                timeEnd: false,
            },
            textValues: {
                name: 		this.filledInputs?.textValues?.name       ?? false,
                surname: 	this.filledInputs?.textValues?.surname    ?? false,
                middleName: this.filledInputs?.textValues?.middleName ?? false,
                phone: 		this.filledInputs?.textValues?.phone      ?? false,
                address: 	this.filledInputs?.textValues?.address    ?? false,
                email: 	    this.filledInputs?.textValues?.email      ?? false,
                birthday:   this.filledInputs?.textValues?.birthday   ?? false,
                comment: 	this.filledInputs?.textValues?.comment    ?? false,
            },
        }
        this.defaultText = params.defaultText;
        this.step = '';

        this.useCustomMainBtn = (params.useCustomMainBtn === "Y");
        const widgetBtnId = this.useCustomMainBtn ? params.customMainBtnId : selectors.widgetBtnId;

        this.wrapperId = selectors.wrapperId;
        this.wrapper = document.getElementById(this.wrapperId);
        this.widgetBtnWrap = document.getElementById(selectors.widgetBtnWrapId);
        this.widgetBtn = document.getElementById(widgetBtnId);
        this.mobileCloseBtn = document.getElementById(selectors.mobileCloseBtnId);
        this.messageNode = document.getElementById(selectors.messageNodeId);
        this.submitBtn = document.getElementById(selectors.submitBtnId);
        this.resultBlock = document.getElementById(selectors.appResultBlockId);

        this.phoneMask = '+7(000)000-00-00';

        this.customColors = params.customColors ?? {};

        this.loaded = false;

        this.initWrapperAction();
        this.initForm(selectors.formId);
        this.initSelectionNodes(params.selectionNodes);
        this.initTextNodes(params.textNodes);
        this.addPhoneMasks();
        this.addCalendarSelection();
        this.activateWidgetButton();
        this.addCustomColors();
    },

    initWrapperAction: function(){
        this.wrapper.addEventListener('click', (e: any) => {
            if (e.target?.getAttribute('id') === this.wrapperId){
                this.showWidget();
            }
        })
    },

    initForm: function(id: string){
        this.form = this.wrapper.querySelector(`#${id}`);
        this.form.addEventListener('submit', this.submit.bind(this));
        this.mobileCloseBtn.addEventListener('click', this.showWidget.bind(this))
    },

    initSelectionNodes: function(nodesData: ISelectionNodes){
        this.selectionNodes = {}
        for (const nodesDataKey in nodesData)
        {
            if (nodesData.hasOwnProperty(nodesDataKey))
            {
                this.eventHandlersAdded[nodesDataKey] = false;

                this.selectionNodes[nodesDataKey] = {
                    blockNode: 		this.wrapper.querySelector(`#${nodesData[nodesDataKey].blockId}`),
                    listNode: 		this.wrapper.querySelector(`#${nodesData[nodesDataKey].listId}`),
                    selectedNode: 	this.wrapper.querySelector(`#${nodesData[nodesDataKey].selectedId}`),
                    inputNode: 		this.wrapper.querySelector(`#${nodesData[nodesDataKey].inputId}`),
                }

                if (nodesData[nodesDataKey].isRequired)
                {
                    this.requiredInputs.push(this.selectionNodes[nodesDataKey].inputNode);
                }
            }
        }
    },

    initTextNodes: function(nodesData: ITextNodes){
        this.textNodes = {}
        for (const nodesDataKey in nodesData)
        {
            if (nodesData.hasOwnProperty(nodesDataKey))
            {
                const input = this.wrapper.querySelector(`#${nodesData[nodesDataKey].inputId}`);

                const currentValue = this.filledInputs.textValues[nodesDataKey];
                input && (input.value = currentValue ? currentValue : '');
                if (input && currentValue && (nodesDataKey === 'birthday')){
                    const date = new Date(currentValue);
                    input.value = this.convertDateToDisplay(date.getTime(), false, true);
                }

                input && input.addEventListener('input', (e: any)=> {
                    let val = e.target.value;
                    if (e.target.name === 'phone' && val.length > this.phoneMask.length){
                        val = val.substring(0, this.phoneMask.length)
                    }
                    this.filledInputs.textValues[nodesDataKey] = val;
                })
                this.textNodes[nodesDataKey] = {
                    inputNode: input,
                }

                if (nodesData[nodesDataKey].isRequired)
                {
                    this.requiredInputs.push(this.textNodes[nodesDataKey].inputNode);
                }
                else
                {
                    if ((this.useConfirmWith === this.confirmTypes.email) && (nodesDataKey === this.confirmTypes.email))
                    {
                        this.requiredInputs.push(this.textNodes[nodesDataKey].inputNode);
                    }
                }
            }
        }
    },

    start: async function(){
        this.toggleLoader(true);
        this.loaded = await this.loadData();
        if (this.loaded){
            this.startRender();
        }else{
            !this.useCustomMainBtn && this.widgetBtnWrap.classList.add(styles['hidden']);
            this.errorMessage("Loading data error")
        }
    },

    loadData:  async function(){
        let loaded = false;
        try{
            const clinicsResponse = await this.getListClinic();
            const clinics = await clinicsResponse.json();

            if (clinics.data?.error || clinics.errors?.length > 0){
                this.errorMessage(clinics.data?.error ?? JSON.stringify(clinics.errors));
            }else{
                if (clinics.data?.length > 0){
                    this.data.clinics = clinics.data;

                    const employeesResponse = await this.getListEmployees();
                    const employees = await employeesResponse.json();

                    if (employees.data?.error || employees.errors?.length > 0){
                        this.errorMessage(employees.data?.error ?? JSON.stringify(employees.errors));
                    }else{
                        if (Object.keys(employees.data).length > 0){
                            this.data.employees = employees.data;
                            const scheduleResponse = await this.getSchedule();
                            const schedule = await scheduleResponse.json();

                            if (schedule.data?.error || schedule.errors?.length > 0){
                                this.errorMessage(schedule.data?.error ?? JSON.stringify(schedule.errors));
                            }else{
                                if (schedule.data?.hasOwnProperty("schedule")){
                                    this.data.schedule = schedule.data.schedule;
                                    loaded = true;
                                }
                            }
                        }else{
                            this.errorMessage("Employees not found")
                        }
                    }
                }else{
                    this.errorMessage("Clinics not found")
                }
            }
        }catch (e) {
            this.errorMessage(e);
            return loaded;
        }
        return loaded;
    },

    getListClinic: async function(){
        const action = 'firstbit:appointment.oneCController.getClinics';
        try {
            const response = await fetch(`${this.ajaxUrl}?action=${action}`, this.requestParams);
            if (response.ok) {
                return response;
            }else{
                this.errorMessage(`Get clinics error. Status code ${response.status}`);
            }
        } catch(e) {
            this.errorMessage(e);
        }
    },

    getListEmployees: async function(){
        const action = 'firstbit:appointment.oneCController.getEmployees';
        try {
            const response = await fetch(`${this.ajaxUrl}?action=${action}`, this.requestParams);
            if (response.ok) {
                return response;
            }else{
                this.errorMessage(`Get employees error. Status code ${response.status}`);
            }
        } catch(e) {
            this.errorMessage(e);
        }
    },

    getListNomenclature: async function(clinicGuid: string){
        const action = 'firstbit:appointment.oneCController.getNomenclature';
        this.requestParams.body.set('clinicGuid', clinicGuid);
        try {
            const response = await fetch(`${this.ajaxUrl}?action=${action}`, this.requestParams);
            this.requestParams.body.delete('clinicGuid');
            if (response.ok) {
                return response;
            }else{
                this.errorMessage(`Get nomenclature error. Status code ${response.status}`);
            }
        } catch(e) {
            this.errorMessage(e);
        }
    },

    getSchedule: async function(){
        const action = 'firstbit:appointment.oneCController.getSchedule';
        try {
            const response = await fetch(`${this.ajaxUrl}?action=${action}`, this.requestParams);
            if (response.ok) {
                return response;
            }else{
                this.errorMessage(`Can not get schedule. Error status - ${response.status}`);
            }
        } catch(e) {
            this.errorMessage(e)
        }
    },

    bindServicesToSpecialties: function() {
        const services  = this.data.services;
        const employees = this.data.employees;
        if(Object.keys(employees).length > 0)
        {
            for (const employeeUid in employees)
            {
                if (!employees.hasOwnProperty(employeeUid)) { return; }
                const empServices = employees[employeeUid].services;
                if(empServices && Object.keys(empServices).length > 0){
                    for (const empServiceUid in empServices)
                    {
                        if (!empServices.hasOwnProperty(empServiceUid)) { return; }

                        if (services.hasOwnProperty(empServiceUid)){
                            const specialty = employees[employeeUid]['specialty'];
                            if (specialty){
                                services[empServiceUid].specialtyUid = this.createIdFromName(specialty);
                            }
                        }
                    }
                }
            }
        }

    },

    startRender: function(){
        try
        {
            const clinicsRendered = this.renderClinicList();
            if (clinicsRendered)
            {
                if (this.isUpdate === "Y")
                {
                    for (const dataKey in this.filledInputs) {
                        if (this.filledInputs.hasOwnProperty(dataKey)
                            && this.selectionNodes.hasOwnProperty(dataKey))
                        {
                            this.filledInputs[dataKey] = JSON.parse(this.selectionNodes[dataKey].inputNode.value);
                        }
                    }
                    this.renderSpecialtiesList();
                    this.renderEmployeesList();
                    this.renderScheduleList();
                }
                setTimeout(()=>{
                    this.toggleLoader(false);
                }, 300)
            }
            else
            {
                this.errorMessage("error on clinics rendering")
            }
        }
        catch (e) {
            this.errorMessage(e);
        }
    },

    renderClinicList: function(){
        let rendered = false;
        if(this.data.clinics.length)
        {
            if (this.selectionNodes.hasOwnProperty(this.dataKeys.clinicsKey))
            {
                const clinicsList = this.selectionNodes[this.dataKeys.clinicsKey].listNode;
                clinicsList.innerHTML = '';
                this.data.clinics.forEach((clinic: IClinic) => {
                    const li = document.createElement('li');
                    if (clinic.uid) {
                        li.dataset.uid = clinic.uid;
                        li.dataset.name = clinic.name;
                        li.textContent = clinic.name;
                        clinicsList.append(li);
                    }else{
                        this.errorMessage(`${clinic.name} was excluded from render, because it hasn't uid`);
                    }
                });
                this.addListActions(this.dataKeys.clinicsKey);
                rendered = true;
            }
            else
            {
                this.errorMessage("clinics nodes not found");
            }
        }else{
            this.errorMessage("no clinics to render");
        }
        return rendered;
    },

    renderSpecialtiesList: function(){
        if (this.selectionNodes.hasOwnProperty(this.dataKeys.specialtiesKey))
        {
            const specialtiesList = this.selectionNodes[this.dataKeys.specialtiesKey].listNode;
            specialtiesList.innerHTML = '';
            this.eventHandlersAdded[this.dataKeys.specialtiesKey] = false;
            if(Object.keys(this.data.employees).length > 0)
            {
                for (let uid in this.data.employees)
                {
                    if (!this.data.employees.hasOwnProperty(uid)){
                        throw new Error("Employee uid not found on specialties render");
                    }
                    const clinicCondition = (this.filledInputs[this.dataKeys.clinicsKey].clinicUid === this.data.employees[uid].clinicUid);
                    let canRender = true;
                    if(this.strictCheckingOfRelations){
                        canRender = clinicCondition;
                        if (this.showDoctorsWithoutDepartment){
                            canRender = clinicCondition || !this.data.employees[uid].clinicUid;
                        }
                    }

                    if (canRender && this.data.employees[uid]['specialty'])
                    {
                        const specialty = this.data.employees[uid]['specialty'];
                        const specialtyUid = this.createIdFromName(specialty);

                        const alreadyRendered = specialtiesList.querySelector(`[data-uid="${specialtyUid}"]`);
                        if (!alreadyRendered){
                            const li = document.createElement('li');
                            li.textContent = specialty;
                            li.dataset.uid = specialtyUid;
                            specialtiesList.append(li);
                        }
                    }
                }
                if (specialtiesList.children.length === 0){
                    const span = document.createElement('span');
                    span.classList.add(styles["empty-selection-message"]);
                    span.textContent = `В данной клинике не найдено направлений деятельности`;
                    specialtiesList.append(span);
                }
                this.addListActions(this.dataKeys.specialtiesKey);
            }
        }
        else
        {
            this.errorMessage("specialties block not found")
        }
    },

    renderServicesList: function(){
        if (this.selectionNodes.hasOwnProperty(this.dataKeys.servicesKey))
        {
            const servicesList = this.selectionNodes[this.dataKeys.servicesKey].listNode;
            servicesList.innerHTML = '';
            this.eventHandlersAdded[this.dataKeys.servicesKey] = false;
            if(Object.keys(this.data.services).length > 0)
            {
                for (let uid in this.data.services)
                {
                    if (!this.data.services.hasOwnProperty(uid)){
                        throw new Error("Employee uid not found on specialties render");
                    }

                    let renderCondition = (this.filledInputs[this.dataKeys.specialtiesKey].specialtyUid
                        === this.data.services[uid].specialtyUid);
                    if (this.selectDoctorBeforeService){
                        const selectedEmployeeUid = this.filledInputs[this.dataKeys.employeesKey].refUid;
                        renderCondition = renderCondition && this.data.employees[selectedEmployeeUid].services.hasOwnProperty(uid);
                    }

                    if (renderCondition)
                    {
                        const li = document.createElement('li');

                        let price = Number((this.data.services[uid]['price']).replace(/\s+/g, ''));

                        if (this.data.services.hasOwnProperty(uid)){
                            li.innerHTML = `<p>
												${this.data.services[uid].name}<br>
												${price>0 ? "<b>"+price+"</b>₽" : ""}
											</p>`;
                            li.dataset.uid = uid;
                            li.dataset.duration = this.data.services[uid].duration;
                            servicesList.append(li);
                        }
                    }
                }
                if (servicesList.children.length === 0){
                    const span = document.createElement('span');
                    span.classList.add(styles["empty-selection-message"]);
                    span.textContent = `К сожалению, по выбранным параметрам нет подходящих услуг`;
                    servicesList.append(span);
                }
                this.addListActions(this.dataKeys.servicesKey);
            }
        }
        else
        {
            this.errorMessage("services block not found")
        }
    },

    renderEmployeesList: function() {
        if (this.selectionNodes.hasOwnProperty(this.dataKeys.employeesKey))
        {
            const empList = this.selectionNodes[this.dataKeys.employeesKey].listNode;
            empList.innerHTML = '';
            this.eventHandlersAdded[this.dataKeys.employeesKey] = false;
            if(Object.keys(this.data.employees).length > 0) {
                for (let uid in this.data.employees)
                {
                    if (this.data.employees.hasOwnProperty(uid))
                    {
                        const selectedSpecialty = this.filledInputs[this.dataKeys.specialtiesKey].specialty;
                        const selectedClinic = this.filledInputs[this.dataKeys.clinicsKey].clinicUid;
                        const specialtyCondition = this.data.employees[uid]['specialty'] === selectedSpecialty;
                        const clinicCondition = selectedClinic === this.data.employees[uid].clinicUid;

                        let canRender = specialtyCondition;

                        if(this.strictCheckingOfRelations){
                            if (this.showDoctorsWithoutDepartment){
                                canRender = (specialtyCondition && !this.data.employees[uid].clinicUid)
                                    ||
                                    (specialtyCondition && clinicCondition);
                            }
                            else
                            {
                                canRender = specialtyCondition && clinicCondition;
                            }
                        }

                        if (canRender)
                        {
                            if (this.useServices && !this.selectDoctorBeforeService)
                            {
                                const selectedServiceUid = this.filledInputs[this.dataKeys.servicesKey].serviceUid;
                                if (!this.data.employees[uid].services.hasOwnProperty(selectedServiceUid)){
                                    continue;
                                }
                            }
                            const li = document.createElement('li');
                            li.dataset.uid = uid;
                            li.textContent = `${this.data.employees[uid].surname} 
												${this.data.employees[uid].name} 
												${this.data.employees[uid].middleName}`;
                            empList.append(li);
                        }
                    }
                }
                if (empList.children.length === 0){
                    const span = document.createElement('span');
                    span.classList.add(styles["empty-selection-message"]);
                    span.textContent = `К сожалению, по выбранным параметрам на ближайшее время нет свободных специалистов`;
                    empList.append(span);
                }
                this.addListActions(this.dataKeys.employeesKey);
            }
        }
    },

    renderScheduleList: function() {
        if (this.data.schedule.length)
        {
            const scheduleList = this.selectionNodes[this.dataKeys.scheduleKey].listNode;
            scheduleList.classList.add(styles["column-mode"]);
            scheduleList.innerHTML = '';
            this.eventHandlersAdded[this.dataKeys.scheduleKey] = false;

            this.data.schedule.forEach((employeeSchedule: IScheduleItem) => {
                if (
                    employeeSchedule.clinicUid === this.filledInputs[this.dataKeys.clinicsKey].clinicUid
                    && employeeSchedule.refUid === this.filledInputs[this.dataKeys.employeesKey].refUid
                )
                {
                    const selectedEmployee = this.data.employees[employeeSchedule.refUid];
                    const selectedService = this.filledInputs[this.dataKeys.servicesKey];
                    let serviceDuration = Number(selectedService.serviceDuration);
                    if(selectedEmployee.services.hasOwnProperty(selectedService.serviceUid))
                    {
                        if (selectedEmployee.services[selectedService.serviceUid].hasOwnProperty("personalDuration")){
                            const personalDuration = selectedEmployee.services[selectedService.serviceUid]["personalDuration"];
                            serviceDuration = Number(personalDuration) > 0 ? Number(personalDuration) : serviceDuration;
                        }
                    }
                    const renderCustomIntervals = this.useServices && (serviceDuration > 0);
                    const timeKey = renderCustomIntervals ? "freeNotFormatted" : "free";

                    if (employeeSchedule.timetable[timeKey].length)
                    {
                        let intervals = employeeSchedule.timetable[timeKey];

                        if (renderCustomIntervals)
                        {
                            const customIntervals = this.getIntervalsForServiceDuration(intervals, serviceDuration*1000);

                            if (customIntervals.length === 0)
                            {
                                const span = document.createElement('span');
                                span.classList.add(styles["empty-selection-message"]);
                                span.textContent = `К сожалению, запись на данную услугу к выбранному специалисту невозможна на ближайшее время`;
                                scheduleList.append(span);
                                return;
                            }
                            else
                            {
                                intervals = customIntervals;
                            }
                        }

                        let renderDate: string;
                        let renderColumn: undefined | HTMLElement = undefined;
                        intervals.forEach((day, index) => {
                            const isLast = (index === (intervals.length - 1));
                            if ((day.date !== renderDate) || isLast)
                            {
                                renderColumn ? scheduleList.append(renderColumn) : void(0);
                                !isLast || (intervals.length === 1) ? renderColumn = this.createDayColumn(day) : void(0);
                                renderDate = day.date;
                            }
                            const time = document.createElement('span');
                            time.dataset.displayDate = `${day['formattedDate']} `;
                            time.dataset.date = day.date;
                            time.dataset.start = day.timeBegin;
                            time.dataset.end = day.timeEnd;
                            time.textContent = `${day['formattedTimeBegin']}`;
                            if (renderColumn){
                                renderColumn.append(time);
                            }
                        });
                    }else{
                        const span = document.createElement('span');
                        span.classList.add(styles["empty-selection-message"]);
                        span.textContent = `К сожалению, у данного специалиста нет записи в выбранном филиале на ближайшее время`;
                        scheduleList.append(span);
                    }
                }
            });
            if (scheduleList.children.length === 0){
                const span = document.createElement('span');
                span.classList.add(styles["empty-selection-message"]);
                span.textContent = `К сожалению, у данного специалиста нет записи в выбранном филиале на ближайшее время`;
                scheduleList.append(span);
            }
            this.addListActions(this.dataKeys.scheduleKey);
        }
        else
        {
            this.errorMessage("Schedule is empty");
        }
    },

    getIntervalsForServiceDuration: function(intervals: Array<ITimeTableItem>, serviceDurationMs: number) {
        const newIntervals: Array<ITimeTableItem> = [];
        intervals.length && intervals.forEach((day: ITimeTableItem) => {
            const timestampTimeBegin = Number(new Date(day.timeBegin));
            const timestampTimeEnd = Number(new Date(day.timeEnd));
            const timeDifference = timestampTimeEnd - timestampTimeBegin;
            const appointmentsCount = Math.floor(timeDifference / serviceDurationMs);
            if (appointmentsCount > 0)
            {
                if (this.useTimeSteps && (serviceDurationMs >= 30*60*1000)) //use timeSteps only for services with duration>=30 minutes
                {
                    let start   = new Date(timestampTimeBegin);
                    let end     = new Date(timestampTimeBegin + serviceDurationMs);
                    while(end.getTime() <= timestampTimeEnd){
                        newIntervals.push({
                            "date": 				day.date,
                            "timeBegin": 			this.convertDateToISO(Number(start)),
                            "timeEnd": 				this.convertDateToISO(Number(end)),
                            "formattedDate": 		this.convertDateToDisplay(Number(start), false),
                            "formattedTimeBegin": 	this.convertDateToDisplay(Number(start), true),
                            "formattedTimeEnd": 	this.convertDateToDisplay(Number(end), true),
                        });
                        start.setMinutes(start.getMinutes() + this.timeStepDurationMinutes);
                        end.setMinutes(end.getMinutes() + this.timeStepDurationMinutes);
                    }
                }
                else
                {
                    for (let i = 0; i < appointmentsCount; i++)
                    {
                        let start = Number(new Date(timestampTimeBegin + (serviceDurationMs * i)));
                        let end = Number(new Date(timestampTimeBegin + (serviceDurationMs * (i+1))));
                        newIntervals.push({
                            "date": 				day.date,
                            "timeBegin": 			this.convertDateToISO(start),
                            "timeEnd": 				this.convertDateToISO(end),
                            "formattedDate": 		this.convertDateToDisplay(start, false),
                            "formattedTimeBegin": 	this.convertDateToDisplay(start, true),
                            "formattedTimeEnd": 	this.convertDateToDisplay(end, true),
                        });
                    }
                }

            }
        });
        return newIntervals;
    },

    createDayColumn: function(day: ITimeTableItem){
        const date = this.readDateInfo(day.timeBegin);

        const title = document.createElement('p');
        title.innerHTML = `${date.weekDay}<br>${day['formattedDate']}`;

        const column = document.createElement("li");
        column.append(title);
        return column;
    },

    addHorizontalScrollButtons: function(){
        const scroller = this.selectionNodes[this.dataKeys.scheduleKey].listNode;
        const item = scroller.querySelector('li');

        if (item){
            const itemWidth = scroller.querySelector('li').clientWidth;

            const btnBlock = document.createElement("div");
            btnBlock.classList.add(styles["horizontal-scroll-buttons"])
            const nextBtn = document.createElement("button");
            const prevBtn = document.createElement("button");
            nextBtn.type = "button";
            prevBtn.type = "button";
            nextBtn.textContent = ">";
            prevBtn.textContent = "<";
            btnBlock.append(prevBtn);btnBlock.append(nextBtn);
            scroller.append(btnBlock);

            nextBtn.onclick = () => {
                if (scroller.scrollLeft < (scroller.scrollWidth - itemWidth*3 - 10)) {
                    scroller.scrollBy({ left: itemWidth*3, top: 0, behavior: 'smooth' });
                } else {
                    scroller.scrollTo({ left: 0, top: 0, behavior: 'smooth' });
                }
            }
            prevBtn.onclick = () => {
                if (scroller.scrollLeft !== 0) {
                    scroller.scrollBy({ left: -itemWidth*3, top: 0, behavior: 'smooth' });
                } else {
                    scroller.scrollTo({ left: scroller.scrollWidth, top: 0, behavior: 'smooth' });
                }
            }
        }
    },

    addListActions: function(dataKey: string) {
        if (this.eventHandlersAdded[dataKey]) {
            return false;
        }

        const selected = this.selectionNodes[dataKey].selectedNode;
        const list 	 = this.selectionNodes[dataKey].listNode;

        if (selected && list)
        {
            if (!selected.classList.contains(styles['activated'])) {
                selected.addEventListener('click', ()=>{
                    list.classList.toggle(styles['active']);
                    for (const nodesKey in this.selectionNodes) {
                        if (
                            this.selectionNodes.hasOwnProperty(nodesKey)
                            && nodesKey !== dataKey
                        ){
                            this.selectionNodes[nodesKey].listNode.classList.remove(styles['active']);
                        }
                    }
                })
                selected.classList.add(styles['activated']);
            }
            this.eventHandlersAdded[dataKey] = true;
            (dataKey === this.dataKeys.scheduleKey) ? this.addHorizontalScrollButtons() : void(0);
            this.addItemActions(dataKey);
        }
        else{
            this.errorMessage('selected node or list node not found');
        }
    },

    addItemActions: function(dataKey: string){
        const items = this.selectionNodes[dataKey].listNode.children;
        if (!items.length){
            return;
        }
        for (let item of items) {
            if (dataKey === this.dataKeys.scheduleKey)
            {
                const times = item.querySelectorAll('span');
                times.length && times.forEach((time: HTMLElement) => {
                    time.addEventListener('click', (e: MouseEvent)=>{
                        e.stopPropagation();
                        this.selectionNodes[dataKey].listNode.classList.remove(styles['active']);
                        this.selectionNodes[dataKey].selectedNode.innerHTML = `
                            <span>
                                ${(e.currentTarget as HTMLElement).dataset.displayDate} - 
                                ${(e.currentTarget as HTMLElement).textContent}
                            </span>
                        `;

                        this.changeStep(dataKey, (e.currentTarget as HTMLElement));
                        this.activateBlocks();
                    })
                });
            }
            else{
                item.addEventListener('click', (e: MouseEvent)=>{
                    e.stopPropagation();
                    this.selectionNodes[dataKey].listNode.classList.remove(styles['active']);
                    this.selectionNodes[dataKey].selectedNode.innerHTML = `<span>${(e.currentTarget as HTMLElement).textContent}</span>`;
                    this.changeStep(dataKey, (e.currentTarget as HTMLElement));
                    this.activateBlocks();
                })
            }
        }
    },

    changeStep: function(dataKey: string, target: HTMLElement){
        this.selectionNodes[dataKey].inputNode.value = target.dataset.uid;
        switch (dataKey) {
            case this.dataKeys.clinicsKey:
                this.filledInputs[dataKey].clinicUid = target.dataset.uid;
                this.filledInputs[dataKey].clinicName = target.dataset.name;
                if (this.useServices){
                    this.getListNomenclature(`${target.dataset.uid}`)
                        .then((res: Response) => res.json())
                        .then((nomenclature: any) => {
                            if (nomenclature.data?.error || nomenclature.errors?.length > 0){
                                this.errorMessage(nomenclature.data?.error ?? JSON.stringify(nomenclature.errors));
                            }else{
                                if (Object.keys(nomenclature.data).length > 0){
                                    this.data.services = nomenclature.data;
                                    this.bindServicesToSpecialties();
                                }
                            }
                        })
                }
                this.renderSpecialtiesList();
                break;
            case this.dataKeys.specialtiesKey:
                this.filledInputs[dataKey].specialty = target.textContent;
                this.filledInputs[dataKey].specialtyUid = target.dataset.uid;
                if(this.useServices){
                    if (this.selectDoctorBeforeService){
                        this.renderEmployeesList();
                    }else{
                        this.renderServicesList();
                    }
                }else{
                    this.renderEmployeesList();
                }
                break;
            case this.dataKeys.servicesKey:
                this.filledInputs[dataKey].serviceName = target.textContent;
                this.filledInputs[dataKey].serviceUid = target.dataset.uid;
                this.filledInputs[dataKey].serviceDuration = target.dataset.duration;
                this.selectDoctorBeforeService ? this.renderScheduleList(): this.renderEmployeesList();
                break;
            case this.dataKeys.employeesKey:
                this.filledInputs[dataKey].doctorName = target.textContent;
                this.filledInputs[dataKey].refUid = target.dataset.uid;
                if(this.useServices){
                    if (this.selectDoctorBeforeService){
                        this.renderServicesList();
                    }else{
                        this.renderScheduleList();
                    }
                }else{
                    this.renderScheduleList();
                }
                break;
            case this.dataKeys.scheduleKey:
                this.filledInputs[dataKey].orderDate = target.dataset.date;
                this.filledInputs[dataKey].timeBegin = target.dataset.start;
                this.filledInputs[dataKey].timeEnd = target.dataset.end;
                this.selectionNodes[dataKey].inputNode.value = target.dataset.date;
                break;
            default:
                this.errorMessage('stepCode is invalid or empty')
                break;
        }
        this.step = dataKey;
    },

    submit: async function(event: Event){
        event.preventDefault();

        if (this.checkRequiredFields())
        {
            this.messageNode ? this.messageNode.textContent = "" : void(0);
            this.form.style.pointerEvents = 'none';
            this.submitBtn.classList.add(styles['loading']);
            let orderData: IOrderParams = {...this.filledInputs.textValues};

            for (let key in this.selectionNodes)
            {
                if (this.selectionNodes.hasOwnProperty(key) && this.filledInputs.hasOwnProperty(key))
                {
                    this.selectionNodes[key].inputNode.value = JSON.stringify(this.filledInputs[key]);
                    orderData = {...orderData, ...this.filledInputs[key]};
                }
            }

            if (this.useConfirmWith !== this.confirmTypes.none){
                this.sendConfirmCode(orderData);
            }
            else
            {
                await this.sendOrder(orderData);
            }
        }
        else
        {
            if (this.messageNode){
                this.messageNode.textContent = "Не заполнены все обязательные параметры записи";
            }
            else {
                this.errorMessage('Have not all required params to creating an order');
            }
        }
    },

    sendOrder: async function (params: IOrderParams) {
        const action = 'firstbit:appointment.oneCController.addOrder';
        this.requestParams.body.set('params', JSON.stringify(params));
        try {
            const response = await fetch(`${this.ajaxUrl}?action=${action}`, this.requestParams);
            this.requestParams.body.delete('params');

            if (response.ok)
            {
                const result = await response.json();

                if (result.status === 'success'){
                    if (result.data?.error)
                    {
                        this.errorMessage(result.data.error);
                        this.finalizingWidget(false);
                    }
                    else
                    {
                        if (this.useEmailNote && params["email"])
                        {
                            await this.sendEmailNote(params)
                        }
                        this.finalizingWidget(true);
                    }
                }
                else
                {
                    this.errorMessage(JSON.stringify(result.errors));
                    this.finalizingWidget(false);
                }
            }
            else
            {
                this.errorMessage('Can not connect to 1c. Status code - ' + response.status);
                this.finalizingWidget(false);
            }
        } catch(e) {
            this.errorMessage(e);
            this.finalizingWidget(false);
        }
    },

    sendConfirmCode: function (params: IOrderParams) {
        const action = 'firstbit:appointment.messageController.sendConfirmCode';

        if (this.useConfirmWith === this.confirmTypes.phone){
            this.requestParams.body.set('phone', params.phone);
        }
        else if (this.useConfirmWith === this.confirmTypes.email){
            this.requestParams.body.set('email', params.email);
        }

        this.messageNode.textContent = "";

        fetch(`${this.ajaxUrl}?action=${action}`, this.requestParams)
            .then(response => response.json())
            .then(result => {
                if(result.status === 'success')
                {
                    const timeExpires = result.data?.timeExpires ?? ((new Date()).getTime() / 1000).toFixed(0);
                    this.createConfirmationForm(params, timeExpires);
                }
                else{
                    this.messageNode.textContent = result.errors?.[0]?.message + ". Обратитесь к администратору сайта";
                }
            })
            .finally(() => {
                this.requestParams.body.delete('phone');
                this.requestParams.body.delete('email')
            });
    },

    createConfirmationForm: function (params: IOrderParams, timeExpires: number){
        const confirmWrapperId = styles['appointment-form-confirmation-wrapper'];

        const existsConfirmWrapper = document.getElementById(confirmWrapperId);
        existsConfirmWrapper && existsConfirmWrapper.remove();

        const confirmWrapper = document.createElement('div');
        confirmWrapper.setAttribute('id', confirmWrapperId);
        confirmWrapper.style.width = '100%';

        const label = document.createElement('label');
        label.classList.add(styles['appointment-form_input-wrapper']);

        const input = document.createElement('input');
        input.classList.add(styles["appointment-form_input"]);
        input.setAttribute('type', 'number');
        input.setAttribute('placeholder', 'Введите код подтверждения');
        input.setAttribute('required', 'true');
        input.setAttribute('autocomplete', 'new-password');
        input.addEventListener('input', () => {
            if (input.value && input.value.length > 4){
                input.value = input.value.substring(0, 4);
            }
        });

        const p = document.createElement('p');
        p.style.cssText = 'color:orangered;text-align:center';

        const btnWrap = document.createElement('div');
        btnWrap.classList.add(styles['appointment-form-button-wrapper']);
        const btn = document.createElement('button');
        btn.textContent = "Отправить";
        btn.classList.add(styles['appointment-form_button']);
        btn.setAttribute('type', 'button');
        btn.addEventListener('click', () => {
            p.textContent = '';
            if (input.value && input.value.length === 4){
                this.form.style.pointerEvents = 'none';
                this.verifyConfirmCode(input.value, params, p, btn);
            }
            else
            {
                if (!input.value || (input.value.length < 4 || input.value.length > 4)){
                    p.textContent = 'Код должен содержать четыре цифры';
                }
            }
        });

        const curTimeSeconds: number = Number(((new Date()).getTime() / 1000).toFixed(0));
        let remainingTime = timeExpires - curTimeSeconds;

        const repeatBtn = document.createElement('a');
        repeatBtn.classList.add(styles['appointment-form_button-link']);
        repeatBtn.setAttribute('href', '#');

        const interval = setInterval(() => {
            if (remainingTime <= 0){
                repeatBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.sendConfirmCode(params);
                });
                clearInterval(interval);
            }
            else
            {
                remainingTime--;
                repeatBtn.textContent = `Отправить повторно ${remainingTime > 0 ? remainingTime : ''}`;
            }
        }, 1000);

        for (const key in this.selectionNodes) {
            if (this.selectionNodes.hasOwnProperty(key)){
                this.selectionNodes[key].blockNode.style.display = 'none';
            }
        }
        for (const key in this.textNodes) {
            if (this.textNodes.hasOwnProperty(key)){
                this.textNodes[key].inputNode.closest('label').style.display = 'none';
            }
        }
        this.form.style.transform = 'translateY(30vh)';
        confirmWrapper.append(label, p, btnWrap, repeatBtn);
        this.submitBtn.closest('div').before(confirmWrapper);
        this.submitBtn.closest('div').style.display = 'none';
        label.append(input);
        btnWrap.append(btn);
    },

    verifyConfirmCode: function (code: string, params: IOrderParams, textNode: HTMLElement, btnNode: HTMLButtonElement) {
        const action = 'firstbit:appointment.messageController.verifyConfirmCode';

        this.requestParams.body.set('code', code);
        btnNode.classList.add(styles['loading']);
        fetch(`${this.ajaxUrl}?action=${action}`, this.requestParams)
            .then(res => res.json())
            .then(json => {
                if (json.data?.success)
                {
                    this.sendOrder(params).finally(() => {
                        btnNode.classList.remove(styles['loading']);
                        const existsConfirmWrapper = document.getElementById(styles['appointment-form-confirmation-wrapper']);
                        existsConfirmWrapper && existsConfirmWrapper.remove();
                        this.form.style.transform = '';
                        for (const key in this.selectionNodes) {
                            if (this.selectionNodes.hasOwnProperty(key)){
                                this.selectionNodes[key].blockNode.style.display = '';
                            }
                        }
                        for (const key in this.textNodes) {
                            if (this.textNodes.hasOwnProperty(key)){
                                this.textNodes[key].inputNode.closest('label').style.display = '';
                            }
                        }
                    });
                }
                else{
                    btnNode.classList.remove(styles['loading']);
                    //console.log(json);
                    if (json.errors?.length > 0){
                        json.errors.forEach((error: any) => {
                            textNode.innerHTML = ((Number(error.code) === 400) || (Number(error.code) === 406) || (Number(error.code) === 425))
                                                    ? `${textNode.innerHTML}${error.message}<br>`
                                                    : "Неизвестная ошибка";
                        })
                    }
                }
            })
            .finally(() => {
                this.requestParams.body.delete('phone');
                this.requestParams.body.delete('email');
            });
    },

    sendEmailNote: async function (params: IOrderParams) {
        const action = 'firstbit:appointment.messageController.sendEmailNote';
        this.requestParams.body.set('params', JSON.stringify(params));
        fetch(`${this.ajaxUrl}?action=${action}`, this.requestParams)
            .then(res => res.json())
            .then(json => (json.status === 'error' ? console.log(json) : void(0)))
            .finally(() => this.requestParams.body.delete('params'));
    },

    checkRequiredFields: function(){
        let allNotEmpty = true;

        if (this.requiredInputs.length > 0){
            this.requiredInputs.some((input: HTMLInputElement) => {
                if (!this.isNotEmptyVal(input)){
                    allNotEmpty = false;
                    return true;
                }
            });
        }

        return allNotEmpty && this.phoneIsValid(this.textNodes.phone.inputNode);

    },

    isNotEmptyVal: function(input: HTMLInputElement){
        let  isNotEmpty = (input.value.length > 0);
        if (input.parentElement !== null)
        {
            !isNotEmpty
                ? input.parentElement.classList.add(styles["error"])
                : input.parentElement.classList.remove(styles["error"]);
        }
        return isNotEmpty;
    },

    activateBlocks: function(){
        let current = false;
        let next = false;
        for (const nodesKey in this.selectionNodes)
        {
            if (!this.useServices && nodesKey === this.dataKeys.servicesKey){
                continue;
            }

            if (this.selectionNodes.hasOwnProperty(nodesKey))
            {
                const block = this.selectionNodes[nodesKey].blockNode;
                if (!current && !next){
                    block.classList.remove(styles["hidden"])
                }
                else if (current && !next){
                    block.classList.remove(styles["hidden"])
                    this.resetValue(nodesKey);
                }
                else{
                    block.classList.add(styles["hidden"]);
                    this.resetValue(nodesKey);
                }
                next = current;
                if(nodesKey === this.step) {
                    current = true;
                }
            }
        }
    },

    resetValue: function(nodesKey: string) {
        this.selectionNodes[nodesKey].selectedNode.textContent = this.defaultText[nodesKey];
        this.selectionNodes[nodesKey].inputNode.value = "";
        if (this.filledInputs.hasOwnProperty(nodesKey)){
            for (const propKey in this.filledInputs[nodesKey]) {
                if (this.filledInputs[nodesKey].hasOwnProperty(propKey)){
                    this.filledInputs[nodesKey][propKey] = false;
                }
            }
        }
    },

    toggleLoader: function(on = true){
        on  ? this.form.classList.add(styles['loading'])
            : this.form.classList.remove(styles['loading'])
    },

    activateWidgetButton: function ()
    {
        if (this.widgetBtn){
            this.widgetBtn.addEventListener('click', this.showWidget.bind(this));
        }
        else{
            console.log(`Not found node with id "${this.initParams.customMainBtnId}"`)
        }
    },

    showWidget: function () {
        this.wrapper.classList.toggle(styles['active']);
        this.useCustomMainBtn ? this.widgetBtn.classList.toggle('appointment-form-visible')
                              : this.widgetBtn.classList.toggle(styles['active']);
        if (!this.loaded){
            this.start()
                .then(() => {})
                .finally(() => {})
        }
    },

    errorMessage: function(message: string){
        //console.error("App error:\n" + message);
    },

    addPhoneMasks: function (){
        const maskedInputs = this.wrapper.querySelectorAll('input[type="tel"]');
        const that = this;
        maskedInputs.length && maskedInputs.forEach((input: HTMLInputElement) => {
            input.addEventListener('input', (e: Event) => {
                that.maskInput((e.currentTarget as HTMLInputElement), this.phoneMask);
            });
        });
    },

    addCalendarSelection: function (){
        const that = this;
        const birthdayInput: HTMLInputElement = this.wrapper.querySelector('input[name="birthday"]');
        birthdayInput.addEventListener('keydown', (e: Event) => {
            e.preventDefault();
            return false;
        });
        birthdayInput.addEventListener('click', () => {
            // @ts-ignore
            window.BX.calendar({
                node: birthdayInput,
                field: birthdayInput,
                bTime: false,
                callback_after: function(date: string){
                    const timestamp = (new Date(date)).getTime();
                    that.filledInputs.textValues.birthday = that.convertDateToISO(timestamp);
                }
            });
        });
    },

    maskInput: function(input: HTMLInputElement, mask: string){
        const value = input.value;
        const literalPattern = /[0]/;
        const numberPattern = /[0-9]/;

        let newValue = "";

        let valueIndex = 0;

        for (let i = 0; i < mask.length; i++) {
            if (i >= value.length) break;
            if (mask[i] === "0" && !numberPattern.test(value[valueIndex])) break;
            while (!literalPattern.test(mask[i])) {
                if (value[valueIndex] === mask[i]) break;
                newValue += mask[i++];
            }
            newValue += value[valueIndex++];
        }

        input.value = newValue;
    },

    convertDateToISO: function (timestamp: number) {
        const date = this.readDateInfo(timestamp);

        return `${date.year}-${date.month}-${date.day}T${date.hours}:${date.minutes}:00`;
    },

    convertDateToDisplay: function (timestamp: number, onlyTime: boolean = false, onlyDate = false) {
        const date = this.readDateInfo(timestamp);

        if (onlyTime){
            return `${date.hours}:${date.minutes}`;
        }
        if (onlyDate){
            return `${date.day}.${date.month}.${date.year}`;
        }
        return `${date.day}-${date.month}-${date.year}`;
    },

    readDateInfo: function(timestampOrISO: string | number){
        const weekDays:{[key: number]: string} = {
            0: "Вс",
            1: "Пн",
            2: "Вт",
            3: "Ср",
            4: "Чт",
            5: "Пт",
            6: "Сб",
        }

        const date = new Date(timestampOrISO);

        let day: string = `${date.getDate()}`;
        if (Number(day)<10) {
            day = `0${day}`;
        }

        let month: string = `${date.getMonth()+1}`;
        if (Number(month)<10) {
            month = `0${month}`;
        }

        let hours: string = `${date.getHours()}`;
        if (Number(hours)<10) {
            hours = `0${hours}`;
        }

        let minutes: string = `${date.getMinutes()}`;
        if (Number(minutes)<10) {
            minutes = `0${minutes}`;
        }

        let seconds: string = `${date.getSeconds()}`;
        if (Number(seconds)<10) {
            seconds = `0${seconds}`;
        }

        return {
            "day": day,
            "month": month,
            "year": date.getFullYear(),
            "hours": hours,
            "minutes": minutes,
            "seconds": seconds,
            "weekDay": weekDays[date.getDay()]
        }
    },

    phoneIsValid: function(phoneInput: HTMLInputElement){
        const phone = phoneInput.value;
        let isValid;
        if (!phone)
        {
            isValid = false;
        }
        else
        {
            const validCodes = [904,900,901,902,903,905,906,908,909,910,911,912,913,914,915,916,917,918,
                919,920,921,922,923,924,925,926,927,928,929,930,931,932,933,934,936,937,938,939,950,951,
                952,953,958,960,961,962,963,964,965,966,967,968,969,978,980,981,982,983,984,985,986,987,
                988,989,992,994,995,996,997,999];
            const code = Number(`${phone[3]}${phone[4]}${phone[5]}`);
            isValid = validCodes.includes(code) && phone.length === 16;
        }

        if (phoneInput.parentElement !== null){
            !isValid
                ? phoneInput.parentElement.classList.add(styles["error"])
                : phoneInput.parentElement.classList.remove(styles["error"]);
        }
        return isValid;
    },

    finalizingWidget: function(success: boolean) {
        this.submitBtn.classList.remove(styles['loading']);

        let errorDesc = this.createFinalError();

        this.resultBlock.classList.add(styles['active']);

        const resTextNode = this.resultBlock.querySelector('p');
        this.form.classList.add(styles['off']);
        if (resTextNode)
        {
            if (success)
            {
                const date = this.convertDateToDisplay(this.filledInputs[this.dataKeys.scheduleKey].timeBegin, false);
                const time = this.convertDateToDisplay(this.filledInputs[this.dataKeys.scheduleKey].timeBegin, true);
                const doctor = this.filledInputs[this.dataKeys.employeesKey].doctorName;
                resTextNode.innerHTML = `Вы записаны на приём ${date} в ${time}.<br>Врач - ${doctor}` ;

                resTextNode.classList.add(styles['success']);
                this.finalAnimations();
            }
            else
            {
                resTextNode.append(errorDesc);
                resTextNode.classList.add(styles['error']);
                setTimeout(()=>{
                    this.reload();
                }, 5000);
            }
        }
    },

    finalAnimations: function(){
        this.widgetBtn.classList.remove(styles['active']);
        this.widgetBtn.classList.add(styles['success']);
        setTimeout(()=>{
            this.reload();
        }, 4000);
    },

    reload: function(){
        this.wrapper.classList.remove(styles['active']);
        this.resultBlock.classList.remove(styles['active']);
        this.widgetBtn.classList.remove(styles['success']);
        this.widgetBtn.classList.remove(styles['active']);
        this.selectionNodes[this.dataKeys.scheduleKey].listNode.scrollTo({ left: 0, top: 0});
        this.form.style.pointerEvents = '';
        this.form.classList.remove(styles['off']);

        this.init(this.initParams).then(() => {
            //const clickEvent = new Event('click', {bubbles:false});
            //this.selectionNodes[this.dataKeys.clinicsKey].listNode.firstChild.dispatchEvent(clickEvent);
        });
    },

    createIdFromName: function(str: string) {
        return window.btoa(window.unescape(encodeURIComponent(str)));
    },

    createFinalError: function () {
        const p       = document.createElement('p');
        const start   = document.createElement('span');
        const link    = document.createElement('a');
        const end     = document.createElement('span');

        start.innerHTML = `К сожалению, создание заявки не удалось.\n 
						Возможно, выбранное вами время приёма уже было занято кем-то другим. 
						Пожалуйста, `;
        link.href = "#reload";
        link.textContent = "обновите расписание";
        link.addEventListener('click', this.reload.bind(this));

        end.textContent = " и попробуйте ещё раз.";
        p.append(start, link, end);
        return p;
    },

    addCustomColors: function (){
        if (Object.keys(this.customColors).length > 0)
        {
            const style = document.createElement('style');
            style.textContent = `.${styles['widget-wrapper']}, .${styles['appointment-button-wrapper']}{`
            for (let key in this.customColors){
                if (this.customColors.hasOwnProperty(key))
                {
                    switch (key) {
                        case "--appointment-main-color":
                            const hslM = convertHexToHsl(this.customColors[key]);
                            if (hslM){
                                style.textContent += `--main-h: ${hslM.h};--main-s: ${hslM.s};--main-l: ${hslM.l};`;
                            }
                            break;
                        case "--appointment-field-color":
                            const hslF = convertHexToHsl(this.customColors[key]);
                            if (hslF){
                                style.textContent += `-field-h: ${hslF.h};--field-s: ${hslF.s};--field-l: ${hslF.l};`;
                            }
                            break;
                        default:
                            style.textContent += `${key}: ${this.customColors[key]};`;
                            break;
                    }
                }
            }
            style.textContent = style.textContent + `}`;
            this.wrapper.after(style);
        }
    }
}

