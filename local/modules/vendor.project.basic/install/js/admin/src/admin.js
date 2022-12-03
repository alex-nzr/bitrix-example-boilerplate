/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - admin.js
 * 10.07.2022 22:37
 * ==================================================
 */
'use strict';

import "./admin.css";
import "color_picker";

export const OptionPage = {
    bindColorPickerToNode: function (nodeId, inputId, defaultColor = '') {
        const element = BX(nodeId);
        const input = BX(inputId);
        BX.bind(element, 'click', function () {
            new BX.ColorPicker({
                bindElement: element,
                defaultColor: defaultColor ?? '#FFFFFF',
                allowCustomColor: true,
                onColorSelected: function (color) {
                    input.value = color;
                },
                popupOptions: {
                    angle: true,
                    autoHide: true,
                    closeByEsc: true,
                    events: {
                        onPopupClose: function () {}
                    }
                }
            }).open();
        })
    },
};