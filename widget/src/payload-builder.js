const batteryValues = {
    '90+': 98,
    '85-89': 86,
    '80-84': 81,
    below80: 79,
};

function answer(state, key) {
    return state.answers[key]?.value;
}

function selectedCheckboxes(state, key) {
    return state.answers[key]?.checkboxes ?? [];
}

function valueForPart(part, state) {
    const power = answer(state, 'power');
    const repairHistory = answer(state, 'repair_history');
    const battery = answer(state, 'battery_health');
    const screen = answer(state, 'screen_body');
    const cosmetics = answer(state, 'cosmetics');
    const hardware = answer(state, 'hardware');
    const cameras = answer(state, 'cameras');
    const connectivity = answer(state, 'connectivity');
    const biometrics = answer(state, 'biometrics');
    const screenDamage = selectedCheckboxes(state, 'screen_body');
    const hardwareDefects = selectedCheckboxes(state, 'hardware');
    const cameraDefects = selectedCheckboxes(state, 'cameras');
    const connectivityDefects = selectedCheckboxes(state, 'connectivity');
    const cosmeticsValue = cosmetics === 'none' || !cosmetics ? 3 : cosmetics === 'light' ? 2 : 1;

    switch (Number(part.type)) {
        case 0: return power === 'yes' ? 1 : 0;
        case 1: return repairHistory === 'local' ? 1 : 0;
        case 2: return batteryValues[battery] ?? 98;
        case 3: return screen === 'no' && screenDamage.includes('Front Screen') ? 0 : cosmeticsValue;
        case 4: return screen === 'no' && screenDamage.includes('Back Glass') ? 0 : cosmeticsValue;
        case 5: return hardware === 'no' && hardwareDefects.includes('Charging Port') ? 0 : 1;
        case 6: return hardware === 'no' && hardwareDefects.includes('Volume Buttons') ? 0 : 1;
        case 7: return hardware === 'no' && hardwareDefects.includes('Earpiece') ? 0 : 1;
        case 8: return hardware === 'no' && hardwareDefects.includes('Loudspeaker') ? 0 : 1;
        case 12: return cameras === 'no' && cameraDefects.includes('Back Camera') ? 0 : 1;
        case 13: return cameras === 'no' && cameraDefects.includes('Front Camera') ? 0 : 1;
        case 15: return hardware === 'no' && hardwareDefects.includes('Power Button') ? 0 : 1;
        case 17: return hardware === 'no' && hardwareDefects.includes('Vibration Motor') ? 0 : 1;
        case 20:
        case 21: return biometrics === 'yes' ? 1 : 0;
        case 23: return connectivity === 'no' && connectivityDefects.includes('NFC') ? 0 : 1;
        case 24: return connectivity === 'no' && connectivityDefects.includes('WiFi') ? 0 : 1;
        case 25: return connectivity === 'no' && connectivityDefects.includes('Bluetooth') ? 0 : 1;
        default: return 1;
    }
}

/** Builds the exact Best Repair payload contract required by POST /calculate. */
export function buildCalculatePayload(state) {
    const partsWithValuesMap = {};
    const repairedPartsBefore = new Set(state.repairedPartsBefore.map(Number));

    for (const part of state.parts) {
        const value = repairedPartsBefore.has(Number(part.id)) ? 0 : valueForPart(part, state);
        partsWithValuesMap[String(part.id)] = value;
    }

    return {
        variantId: Number(state.selectedVariantId),
        customer: {
            name: state.customer.name,
            email: state.customer.email,
            phoneNumber: state.customer.phoneNumber,
        },
        repairedPartsBefore: [...repairedPartsBefore],
        partsWithValuesMap,
    };
}
