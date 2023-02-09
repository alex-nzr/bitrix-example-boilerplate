import {AppointmentPopup} from "./appointment/app"

declare global {
    interface Window {
        BX: any;
    }
}
const App = window.BX.namespace('FirstBit.Appointment');
App.Popup = AppointmentPopup;