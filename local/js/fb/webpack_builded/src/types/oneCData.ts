export interface IClinic{
    uid: string,
    name: string
}

export interface ITimeTableItem{
    date: string,
    formattedDate: string,
    formattedTimeBegin: string,
    formattedTimeEnd: string,
    timeBegin: string,
    timeEnd: string,
    typeOfTimeUid?: string,
}

export interface ITimeTable{
    free: ITimeTableItem[],
    busy: ITimeTableItem[],
    freeNotFormatted: ITimeTableItem[],
}

export interface IScheduleItem{
    clinicUid:          string,
    duration:           string,
    durationInSeconds:  number,
    name:               string,
    refUid:             string,
    specialty:          string,
    timetable:          ITimeTable,
}

export interface IOrderParams{
    DATE_TIME: { orderDate: string | boolean, timeBegin: string | boolean, timeEnd: string | boolean },
    DOCTOR: {refUid: string | boolean, doctorName: string | boolean},
    FILIAL: {clinicUid: string | boolean, clinicName: string | boolean},
    SERVICE: {serviceUid: string | boolean, serviceName: string | boolean, serviceDuration: number | boolean},
    SPECIALTY: {specialty: string | boolean, specialtyUid: string | boolean},
    name:       string | boolean,
    surname:    string | boolean,
    middleName: string | boolean,
    phone:      string | boolean,
    email:      string | boolean,
    birthday:   string | boolean,
    address:    string | boolean,
    comment:    string | boolean,
}